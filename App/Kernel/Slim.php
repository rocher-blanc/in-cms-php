<?php

namespace App\Kernel;

use Slim\Factory\AppFactory;
use Slim\Flash\Messages as FlashMessages;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Loader\FilesystemLoader;

/**
 * Bootstrapper Slim 4.
 *
 * Construit l'instance \Slim\App (Slim 4), configure Twig, Flash,
 * middleware, modes, et expose l'interface attendue par App\Kernel.
 * Toute l'API Slim 2 est exposée via SlimBridge, qui est le singleton
 * retourné par getApp().
 */
class Slim
{
    private ?SlimBridge $bridge = null;

    /** Dossiers de templates additionnels (enregistrés par le projet) */
    private array $templateFolder = [];

    /* -------------------------------------------------- */
    /* Getters                                            */
    /* -------------------------------------------------- */

    /** Retourne le SlimBridge (point d'entrée unique de l'API Slim 2) */
    public function getApp(): SlimBridge
    {
        return $this->bridge;
    }

    /* -------------------------------------------------- */
    /* Setters (appelés par App\Kernel avant load())      */
    /* -------------------------------------------------- */

    /** Slim 2 : setConfig(['key' => 'value']) */
    public function setConfig($config): void
    {
        if (is_array($config)) {
            $this->bridge->config($config);
        }
    }

    public function setTemplateFolder(string $folder): void
    {
        $this->templateFolder[] = $folder;
    }

    /* -------------------------------------------------- */
    /* Extensions Twig                                    */
    /* -------------------------------------------------- */

    /**
     * Slim 2 : setParserExtensions([$ext1, $ext2])
     * Ajoute des extensions à l'environment Twig après initialisation.
     */
    public function setParserExtensions(array $extensions): void
    {
        if ($this->bridge === null || $this->bridge->view() === null) {
            return;
        }
        $env = $this->bridge->view()->getEnvironment();
        foreach ($extensions as $ext) {
            if (is_object($ext) && !$env->hasExtension(get_class($ext))) {
                $env->addExtension($ext);
            }
        }
    }

    /* -------------------------------------------------- */
    /* Middleware                                         */
    /* -------------------------------------------------- */

    /** Slim 2 : loadPlugin($plugin) */
    public function loadPlugin($plugin): void
    {
        if (is_array($plugin)) {
            foreach ($plugin as $row) {
                if (is_object($row)) {
                    $this->bridge->add($row);
                }
            }
        } elseif (is_object($plugin)) {
            $this->bridge->add($plugin);
        }
    }

    public function initMiddleware(): void
    {
        // PrettyExceptions est maintenant un ErrorMiddleware Slim 4
        // configuré dans load(). On ne l'ajoute plus ici.
    }

    public function addMiddleware($middleware): void
    {
        if (is_array($middleware)) {
            foreach ($middleware as $row) {
                if (is_object($row)) {
                    $this->bridge->add($row);
                }
            }
        } elseif (is_object($middleware)) {
            $this->bridge->add($middleware);
        }
    }

    /* -------------------------------------------------- */
    /* Initialisation                                     */
    /* -------------------------------------------------- */

    /**
     * Crée l'application Slim 4, le SlimBridge, configure Twig + Flash.
     * Appelé par App\Kernel::getSlim().
     */
    public function load(): void
    {
        // 1. Slim 4 App
        $slim4 = AppFactory::create();
        $slim4->addRoutingMiddleware();

        // 2. SlimBridge (singleton Slim 2 compat)
        $this->bridge = SlimBridge::getInstance();
        $this->bridge->setSlimApp($slim4);
        $this->bridge->config([
            'mode'  => defined('SLIM_MODE') ? SLIM_MODE : 'development',
            'cache' => defined('CACHE_PATH') ? CACHE_PATH : false,
        ]);
    }

    /**
     * Configure Twig avec les dossiers de templates.
     * Doit être appelée après que les dossiers soient connus (initSlim).
     */
    public function initView(): void
    {
        $viewArray = [];

        if (defined('VIEW_PROJECT_PATH'))        $viewArray[] = VIEW_PROJECT_PATH;
        if (defined('VIEW_PROJECT_COMMON_PATH')) $viewArray[] = VIEW_PROJECT_COMMON_PATH;

        foreach ($this->templateFolder as $folder) {
            $viewArray[] = $folder;
        }

        // Détection module depuis l'URL courante
        $url = '';
        try {
            $url = \App\Kernel\Factory::getInstance()->Url()->getFullUrl();
        } catch (\Throwable $e) {
            // Ignore si Factory n'est pas encore disponible
        }

        $exp = explode('/', $url);
        if (count($exp) > 2 && $exp[2] === 'module') {
            if (defined('VIEW_PROJECT_PATH') && is_dir(VIEW_PROJECT_PATH . '/module/' . $exp[3])) {
                $viewArray[] = VIEW_PROJECT_PATH . '/module/' . $exp[3];
            }
            if (defined('TEMPLATES_PATH') && is_dir(TEMPLATES_PATH . '/module/' . $exp[3])) {
                $viewArray[] = TEMPLATES_PATH . '/module/' . $exp[3];
            }
        }

        if (defined('TEMPLATES_PATH'))             $viewArray[] = TEMPLATES_PATH;
        if (defined('TEMPLATES_COMMON_TECH_PATH') && defined('THEME') && $this->bridge->config('config') === 'front') {
            $viewArray[] = TEMPLATES_COMMON_TECH_PATH;
        }
        if (defined('TEMPLATES_COMMON_PATH'))      $viewArray[] = TEMPLATES_COMMON_PATH;

        $viewArray = array_unique(array_filter($viewArray, fn($d) => is_dir($d)));

        $cacheEnabled = ($this->bridge->config('cache') !== false && $this->bridge->config('cache') !== null);
        $options = [
            'cache'      => $cacheEnabled ? $this->bridge->config('cache') : false,
            'debug'      => ($this->bridge->config('mode') !== 'production'),
            'autoescape' => false,
        ];

        $loader = new FilesystemLoader($viewArray);
        $twig   = new Twig($loader, $options);

        // Extensions Twig de base
        if ($options['debug']) {
            $twig->getEnvironment()->addExtension(new \Twig\Extension\DebugExtension());
        }

        // Extension slim/twig-view (url_for, base_url, etc.)
        $slim4App = $this->bridge->getSlimApp();
        $slim4App->add(TwigMiddleware::create($slim4App, $twig));

        $this->bridge->setTwig($twig);
    }

    /** Slim 2 : configureMode() — délégué au bridge */
    public function initMode(): void
    {
        $bridge = $this->bridge;

        $bridge->configureMode('production', function () use ($bridge) {
            $bridge->config([
                'log.enable' => false,
                'cache'      => defined('CACHE_PATH') ? CACHE_PATH : false,
                'debug'      => false,
                'twig.debug' => false,
            ]);
        });

        $bridge->configureMode('development', function () use ($bridge) {
            $bridge->config([
                'log.enable' => false,
                'cache'      => false,
                'debug'      => true,
                'twig.debug' => true,
            ]);
        });
    }

    /* -------------------------------------------------- */
    /* Run                                                */
    /* -------------------------------------------------- */

    public function run(): void
    {
        $this->bridge->run();
    }
}
