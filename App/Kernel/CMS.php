<?php

namespace App\Kernel;

use App\Kernel\Front\Translate;
use Psr\Http\Message\ServerRequestInterface;

class CMS
{
    private static ?self $instance = null;
    private array $config = [];

    public function __construct() {}

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
    /* Config                                             */
    /* -------------------------------------------------- */

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * Lit une clé depuis la config locale (issue de Kernel::$_config).
     * Pour la config applicative complète, utiliser Config::getInstance()->get().
     */
    public function config(string $key): mixed
    {
        return $this->config[$key] ?? null;
    }

    /* -------------------------------------------------- */
    /* Helpers applicatifs                                */
    /* -------------------------------------------------- */

    public function isDev(): bool
    {
        return defined('DEBUG_CMS') && DEBUG_CMS;
    }

    public function getIp(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_X_REAL_IP']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '';
    }

    /* -------------------------------------------------- */
    /* Request courante                                   */
    /* -------------------------------------------------- */

    public function request(): ?ServerRequestInterface
    {
        return AppContext::request();
    }

    /* -------------------------------------------------- */
    /* Traduction                                         */
    /* -------------------------------------------------- */

    public function text(string $key): string
    {
        return Translate::getInstance()->getText($key);
    }

    /* -------------------------------------------------- */
    /* Vue                                                */
    /* -------------------------------------------------- */

    public function view(): View
    {
        return View::getInstance();
    }

    public function fetch(string $tpl, array $arg = []): string
    {
        return $this->view()->fetch($tpl, $arg);
    }

    public function render(string $tpl, array $arg = []): void
    {
        if (isset($_GET['noview']) || isset($_POST['noview'])) {
            return;
        }
        $this->view()->render($tpl, $arg);
    }
}
