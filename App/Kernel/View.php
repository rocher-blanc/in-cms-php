<?php

namespace App\Kernel;

class View
{
    protected array $folder = [];

    private static ?self $instance = null;

    /* -------------------------------------------------- */
    /* Singleton                                          */
    /* -------------------------------------------------- */

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /* -------------------------------------------------- */
    /* Dossiers de templates                              */
    /* -------------------------------------------------- */

    public function setFolder(string $folder): void
    {
        $this->folder[] = $folder;
    }

    /* -------------------------------------------------- */
    /* Données globales Twig                              */
    /* -------------------------------------------------- */

    public function setData(string $key, mixed $value): void
    {
        AppContext::addGlobal($key, $value);
    }

    public function appendData(array $array): void
    {
        AppContext::addGlobals($array);
    }

    /* -------------------------------------------------- */
    /* Rendu                                              */
    /* -------------------------------------------------- */

    public function render(string $template, array $args = []): void
    {
        $twig = AppContext::twig();
        if ($twig === null) {
            return;
        }

        $env    = $twig->getEnvironment();
        $loader = $env->getLoader();

        $exists = false;
        try {
            $loader->getSourceContext($template);
            $exists = true;
        } catch (\Twig\Error\LoaderError) {
            try {
                $loader->getSourceContext($template . '.html');
                $template .= '.html';
                $exists    = true;
            } catch (\Twig\Error\LoaderError) {
                // template introuvable
            }
        }

        if (!$exists) {
            return;
        }

        if (defined('DEBUG_TWIG') && DEBUG_TWIG) {
            $this->getTwigDebugBar()->merge($args);
            $args['__debug_twig__'] = $this->getTwigDebugBar()->getDebugTwig();
        }

        echo $env->render($template, $args);
    }

    public function fetch(string $template, array $args = []): string
    {
        $twig = AppContext::twig();
        if ($twig === null) {
            return '';
        }
        return $twig->getEnvironment()->render($template, $args);
    }

    public function getTwigDebugBar(): \App\Kernel\Front\TwigDebugBar
    {
        return \App\Kernel\Front\TwigDebugBar::getInstance();
    }
}
