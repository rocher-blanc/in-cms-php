<?php

namespace App\Kernel;

use Slim\App;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages as FlashMessages;
use Slim\Middleware\OutputBufferingMiddleware;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Twig\Loader\FilesystemLoader;

/**
 * Bootstrapper Slim 4.
 *
 * Responsabilités :
 *  - Créer l'instance Slim\App
 *  - Configurer Twig (chemins de templates, options, extensions)
 *  - Initialiser le Flash
 *  - Enregistrer les middlewares Slim (routing, body parsing, error)
 *  - Exposer l'interface attendue par App\Kernel (setConfig, addMiddleware, run…)
 *
 * Config, Twig et Flash sont exposés via AppContext (façade statique),
 * ce qui évite de passer des dépendances en paramètre partout.
 */
class Slim
{
    private ?App $app = null;

    /** Dossiers de templates additionnels (enregistrés par le projet) */
    private array $templateFolder = [];

    /** Extensions Twig à ajouter après initView() */
    private array $pendingExtensions = [];

    /* -------------------------------------------------- */
    /* Getters                                            */
    /* -------------------------------------------------- */

    /** Retourne l'application Slim 4 */
    public function getApp(): App
    {
        return $this->app;
    }

    /* -------------------------------------------------- */
    /* Setters (appelés par App\Kernel avant load())      */
    /* -------------------------------------------------- */

    /**
     * Reçoit la config issue de App\Kernel et la fusionne dans Config.
     * Slim 2 : setConfig(['session' => 'cms', 'login.url' => '/admin/login', ...])
     */
    public function setConfig(array $config): void
    {
        Config::getInstance()->merge($config);
    }

    /** Applique le base path Slim 4 pour le back-office (/admin-site). */
    public function setBasePathFromConfig(): void
    {
        if ($this->app === null) {
            return;
        }

        $config = Config::getInstance();
        if ($config->get('config') !== 'back') {
            return;
        }

        $basePath = $config->get('admin.url', '');
        if (is_string($basePath) && $basePath !== '') {
            $this->app->setBasePath($basePath);
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
     * Peut être appelé avant ou après initView().
     */
    public function setParserExtensions(array $extensions): void
    {
        if ($this->app === null) {
            // Mise en file d'attente si Twig n'est pas encore initialisé
            $this->pendingExtensions = array_merge($this->pendingExtensions, $extensions);
            return;
        }

        $twig = AppContext::twig();
        if ($twig === null) {
            return;
        }

        $env = $twig->getEnvironment();
        foreach ($extensions as $ext) {
            if (is_object($ext) && !$env->hasExtension(get_class($ext))) {
                $env->addExtension($ext);
            }
        }
    }

    /* -------------------------------------------------- */
    /* Middleware                                         */
    /* -------------------------------------------------- */

    /** Slim 2 : loadPlugin($plugin) — en Slim 4, délégué au middleware Plugin (PSR-15). */
    public function loadPlugin(mixed $plugin): void
    {
        if ($this->app === null) {
            return;
        }

        $plugins = is_array($plugin) ? $plugin : [$plugin];
        $this->app->add(new \App\Kernel\Middleware\Plugin($plugins));
    }

    public function initMiddleware(): void {}

    public function addMiddleware(mixed $middleware): void
    {
        if ($this->app === null) {
            return;
        }

        if (is_array($middleware)) {
            foreach ($middleware as $row) {
                if (is_object($row)) {
                    $this->app->add($row);
                }
            }
        } elseif (is_object($middleware)) {
            $this->app->add($middleware);
        }
    }

    /* -------------------------------------------------- */
    /* Initialisation                                     */
    /* -------------------------------------------------- */

    /**
     * Crée l'application Slim 4 et initialise les services de base.
     * Appelé par App\Kernel::getSlim().
     */
    public function load(): void
    {
        // Config par défaut (peut être écrasée par setConfig)
        $config = Config::getInstance();
        if ($config->get('mode') === null) {
            $config->set('mode', defined('SLIM_MODE') ? SLIM_MODE : 'development');
        }
        if ($config->get('cache') === null) {
            $config->set('cache', defined('CACHE_PATH') ? CACHE_PATH : false);
        }

        // Slim 4 App
        $this->app = AppFactory::create();
        $this->app->addRoutingMiddleware();
        $this->app->addBodyParsingMiddleware();

        // Capture les echo/print des route handlers et les injecte dans la réponse
        $this->app->add(new OutputBufferingMiddleware(new StreamFactory()));

        // ErrorMiddleware : centralise en Slim 4 les cas de sortie « exception »
        //  - HttpNotFoundException : aucune route ne matche  → page 404 propre
        //  - NotFoundException (métier, show404)             → page 404 propre
        //  - RedirectException (redirect() dans un handler)  → réponse 3xx
        $this->registerErrorMiddleware();

        AppContext::setSlimApp($this->app);

        // Flash (nécessite les sessions — initiées avant ce point par le projet)
        $flash = new FlashMessages();
        AppContext::setFlash($flash);
    }

    /**
     * Configure Twig avec les dossiers de templates.
     * Doit être appelée après que les constantes de chemin soient définies.
     */
    public function initView(): void
    {
        $config = Config::getInstance();

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
        } catch (\Throwable) {
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
        if (defined('TEMPLATES_COMMON_TECH_PATH') && defined('THEME') && $config->get('config') === 'front') {
            $viewArray[] = TEMPLATES_COMMON_TECH_PATH;
        }
        if (defined('TEMPLATES_COMMON_PATH'))      $viewArray[] = TEMPLATES_COMMON_PATH;

        $viewArray = array_unique(array_filter($viewArray, fn($d) => is_string($d) && is_dir($d)));

        $isDebug      = ($config->get('mode') !== 'production');
        $cacheEnabled = ($config->get('cache') !== false && $config->get('cache') !== null);

        $options = [
            'cache'      => $cacheEnabled ? $config->get('cache') : false,
            'debug'      => $isDebug,
            'autoescape' => false,
        ];

        $loader = new FilesystemLoader($viewArray);
        $twig   = new Twig($loader, $options);

        if ($isDebug) {
            $twig->getEnvironment()->addExtension(new \Twig\Extension\DebugExtension());
        }

        // Flash disponible dans tous les templates
        $twig->getEnvironment()->addGlobal('flash', AppContext::flash());

        // Middleware slim/twig-view (url_for, base_url, etc.)
        $this->app->add(TwigMiddleware::create($this->app, $twig));

        AppContext::setTwig($twig);

        // Extensions en attente (enregistrées avant initView)
        if (!empty($this->pendingExtensions)) {
            $this->setParserExtensions($this->pendingExtensions);
            $this->pendingExtensions = [];
        }
    }

    /**
     * Slim 2 : configureMode() — adapté pour Slim 4.
     * Applique les options de config selon le mode.
     */
    public function initMode(): void
    {
        $config = Config::getInstance();
        $mode   = $config->get('mode', 'development');

        if ($mode === 'production') {
            $config->set('log.enable', false);
            $config->set('debug',      false);
            $config->set('twig.debug', false);
            if (defined('CACHE_PATH')) {
                $config->set('cache', CACHE_PATH);
            }
        } else {
            $config->set('log.enable', false);
            $config->set('cache',      false);
            $config->set('debug',      true);
            $config->set('twig.debug', true);
        }
    }

    /* -------------------------------------------------- */
    /* Run                                                */
    /* -------------------------------------------------- */

    public function run(): void
    {
        $this->app->run();
    }

    /* -------------------------------------------------- */
    /* Gestion centralisée des erreurs (Slim 4)           */
    /* -------------------------------------------------- */

    /**
     * Enregistre le ErrorMiddleware et ses handlers dédiés.
     *
     * Pourquoi : en Slim 4 le routing est dispatché par le RoutingMiddleware,
     * bien après l'enregistrement des routes par les plugins. Les sorties
     * « flow control » par exception (404 métier, redirection) et le 404
     * natif (route introuvable) doivent donc être converties en réponses
     * PSR-7 à un point unique plutôt que via un show404() inconditionnel.
     */
    private function registerErrorMiddleware(): void
    {
        $config              = Config::getInstance();
        $displayErrorDetails = ($config->get('mode', 'development') !== 'production');

        $errorMiddleware = $this->app->addErrorMiddleware($displayErrorDetails, true, true);

        // Route introuvable → 404 propre
        $errorMiddleware->setErrorHandler(
            \Slim\Exception\HttpNotFoundException::class,
            fn(): \Psr\Http\Message\ResponseInterface => $this->buildNotFoundResponse()
        );

        // 404 métier (Response::show404) levé depuis un route handler
        $errorMiddleware->setErrorHandler(
            \App\Kernel\Exception\NotFoundException::class,
            function ($request, \Throwable $exception): \Psr\Http\Message\ResponseInterface {
                $body = $exception instanceof \App\Kernel\Exception\NotFoundException
                    ? $exception->getBody()
                    : '';
                return $this->buildNotFoundResponse($body);
            }
        );

        // Redirection levée depuis un route handler
        $errorMiddleware->setErrorHandler(
            \App\Kernel\Exception\RedirectException::class,
            function ($request, \Throwable $exception): \Psr\Http\Message\ResponseInterface {
                $status = $exception instanceof \App\Kernel\Exception\RedirectException
                    ? $exception->getHttpStatus() : 302;
                $url = $exception instanceof \App\Kernel\Exception\RedirectException
                    ? $exception->getUrl() : '/';
                return (new \Slim\Psr7\Response($status))->withHeader('Location', $url);
            }
        );
    }

    /** Construit une réponse 404 en réutilisant le template d'erreur si présent. */
    private function buildNotFoundResponse(string $body = ''): \Psr\Http\Message\ResponseInterface
    {
        if ($body === '') {
            $body = 'Erreur 404';
            $twig = AppContext::twig();
            if ($twig !== null) {
                $env = $twig->getEnvironment();
                foreach (['errors/404.twig', 'errors/404.twig.html'] as $tpl) {
                    if ($env->getLoader()->exists($tpl)) {
                        $body = $env->render($tpl);
                        break;
                    }
                }
            }
        }

        $response = new \Slim\Psr7\Response(404);
        $response->getBody()->write($body);
        return $response;
    }
}
