<?php

namespace App\Kernel;

use Psr\Http\Message\ServerRequestInterface;
use Slim\App;
use Slim\Flash\Messages as FlashMessages;
use Slim\Views\Twig;

/**
 * Façade statique vers les services partagés de l'application.
 *
 * Remplace les méthodes de SlimBridge (view, flash, request, appendViewData).
 * Chaque service est initialisé une seule fois dans App\Kernel\Slim::load().
 *
 * Concept : Service Locator léger, acceptable ici car PHP est stateless
 * entre les requêtes et nous n'avons pas de conteneur DI complet.
 */
class AppContext
{
    private static ?App $slimApp = null;
    private static ?Twig $twig = null;
    private static ?FlashMessages $flash = null;
    private static ?ServerRequestInterface $request = null;

    /* -------------------------------------------------- */
    /* Initialiseurs (appelés par App\Kernel\Slim)        */
    /* -------------------------------------------------- */

    public static function setSlimApp(App $app): void
    {
        self::$slimApp = $app;
    }

    public static function setTwig(Twig $twig): void
    {
        self::$twig = $twig;
    }

    public static function setFlash(FlashMessages $flash): void
    {
        self::$flash = $flash;
    }

    /** Mise à jour à chaque requête par les middlewares */
    public static function setRequest(ServerRequestInterface $request): void
    {
        self::$request = $request;
    }

    /* -------------------------------------------------- */
    /* Accesseurs                                         */
    /* -------------------------------------------------- */

    public static function slimApp(): ?App
    {
        return self::$slimApp;
    }

    public static function twig(): ?Twig
    {
        return self::$twig;
    }

    public static function flash(): ?FlashMessages
    {
        return self::$flash;
    }

    public static function request(): ?ServerRequestInterface
    {
        return self::$request;
    }

    /* -------------------------------------------------- */
    /* Helpers vue                                        */
    /* -------------------------------------------------- */

    /**
     * Ajoute des variables globales Twig (ex: user, csrf_token, flash...).
     * Remplace SlimBridge::appendViewData().
     *
     * Note : addGlobal() doit être appelé avant le premier rendu.
     */
    public static function addGlobal(string $key, mixed $value): void
    {
        if (self::$twig === null) {
            return;
        }
        try {
            self::$twig->getEnvironment()->addGlobal($key, $value);
        } catch (\LogicException) {
            // Twig 3 : les globals sont figés après le premier rendu
        }
    }

    /** Ajoute plusieurs variables globales en une fois */
    public static function addGlobals(array $data): void
    {
        if (self::$twig === null) {
            return;
        }
        $env = self::$twig->getEnvironment();
        foreach ($data as $key => $value) {
            try {
                $env->addGlobal($key, $value);
            } catch (\LogicException) {
                // Twig 3 : les globals sont figés après le premier rendu
            }
        }
    }

    /**
     * Relit une variable globale Twig précédemment enregistrée.
     * Remplace SlimBridge::getViewData() : les plugins (ex : Meta) posent
     * des globals que les controllers relisent ensuite.
     */
    public static function getGlobal(string $key, mixed $default = null): mixed
    {
        if (self::$twig === null) {
            return $default;
        }
        $globals = self::$twig->getEnvironment()->getGlobals();
        return $globals[$key] ?? $default;
    }

    /** Réinitialise le contexte (tests unitaires) */
    public static function reset(): void
    {
        self::$slimApp = null;
        self::$twig    = null;
        self::$flash   = null;
        self::$request = null;
    }
}
