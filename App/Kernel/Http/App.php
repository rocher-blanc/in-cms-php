<?php

namespace App\Kernel\Http;

use App\Kernel\AppContext;
use App\Kernel\CMS;
use App\Kernel\Config;
use App\Kernel\Factory;
use App\Kernel\View;

/**
 * Façade compatibilité Slim 2 pour les controllers hérités.
 *
 * Les controllers appellent $this->getApp()->... en s'attendant à l'API de
 * l'application Slim 2 :
 *   ->request / ->request() / ->Request()   (requête)
 *   ->view()                                (moteur de rendu)
 *   ->pass()                                (déclenche un 404)
 *   ->lastModified($ts)                     (en-tête HTTP)
 *
 * Slim 4 ne fournit plus cet objet « app » aux controllers. Cette façade
 * (pattern Facade) réexpose l'API attendue en s'appuyant sur les services
 * Slim 4 (AppContext, CMS, Factory, View), ce qui évite de réécrire tout le
 * code métier hérité.
 */
class App
{
    private static ?self $instance = null;

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Accès type propriété : $this->getApp()->request->post(...)
     * (l'API historique mélange ->request et ->request()).
     */
    public function __get(string $name): mixed
    {
        return match ($name) {
            'request' => $this->request(),
            'config'  => Config::getInstance(),
            default   => null,
        };
    }

    /* -------------------------------------------------- */
    /* Requête                                            */
    /* -------------------------------------------------- */

    /**
     * Requête courante. PHP étant insensible à la casse sur les noms de
     * méthode, cette définition répond aussi aux appels historiques
     * ->Request() présents dans certains controllers.
     */
    public function request(): ?Request
    {
        return CMS::getInstance()->request();
    }

    /* -------------------------------------------------- */
    /* Vue                                                */
    /* -------------------------------------------------- */

    public function view(): View
    {
        return View::getInstance();
    }

    /* -------------------------------------------------- */
    /* Contrôle de flux                                   */
    /* -------------------------------------------------- */

    /**
     * Équivalent Slim 2 $app->pass() : la ressource demandée n'existe pas,
     * on déclenche un 404 (converti en réponse par le ErrorMiddleware).
     */
    public function pass(): never
    {
        Factory::getInstance()->Response()->show404();
    }

    /** Positionne l'en-tête Last-Modified si les en-têtes ne sont pas envoyés. */
    public function lastModified(int $timestamp): void
    {
        if (!headers_sent()) {
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $timestamp) . ' GMT');
        }
    }

    /** Lecture d'une clé de config (API historique $app->config('key')). */
    public function config(?string $key = null): mixed
    {
        $config = Config::getInstance();
        return $key === null ? $config : $config->get($key);
    }
}
