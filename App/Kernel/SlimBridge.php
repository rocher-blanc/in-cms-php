<?php

namespace App\Kernel;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use Slim\Flash\Messages as FlashMessages;
use Slim\Routing\RouteCollectorProxy;
use Slim\Views\Twig;

/**
 * Adapter Slim 2 → Slim 4.
 *
 * Expose l'API Slim 2 (getInstance, get/post/map/group, render, redirect,
 * flash, config, response->body, request, view, add, run) en déléguant
 * à une instance interne Slim\App (Slim 4).
 *
 * Les route callbacks hérités de Slim 2 (qui font echo) sont enveloppés
 * dans un output buffer : la sortie capturée est injectée dans la PSR-7
 * response retournée à Slim 4.
 *
 * Concept : Adapter Pattern + Strangler Fig.
 */
class SlimBridge
{
    private static ?self $instance = null;

    private App $app;

    /** @var array<string, mixed> Configuration Slim 2 style */
    private array $config = [];

    /** Twig environment partagé */
    private ?Twig $twig = null;

    /** Flash messages (slim/flash) */
    private ?FlashMessages $flash = null;

    /** Response courante construite durant l'exécution d'une route */
    private SlimResponse $currentResponse;

    /** Content-type demandé dans la route courante */
    private string $contentType = 'text/html';

    private function __construct()
    {
        $this->currentResponse = new SlimResponse();
    }

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

    public static function reset(): void
    {
        self::$instance = null;
    }

    /* -------------------------------------------------- */
    /* App Slim 4 interne                                 */
    /* -------------------------------------------------- */

    public function setSlimApp(App $app): void
    {
        $this->app = $app;
    }

    public function getSlimApp(): App
    {
        return $this->app;
    }

    /* -------------------------------------------------- */
    /* Config (Slim 2 style)                              */
    /* -------------------------------------------------- */

    /**
     * @param string|array $key
     * @param mixed $value
     * @return mixed
     */
    public function config($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->config[$k] = $v;
            }
            return $this;
        }

        if ($value !== null) {
            $this->config[$key] = $value;
            return $this;
        }

        return $this->config[$key] ?? null;
    }

    /** Slim 2 configureMode — exécute le callback si le mode correspond */
    public function configureMode(string $mode, callable $callback): void
    {
        $currentMode = $this->config('mode') ?? 'development';
        if ($currentMode === $mode) {
            $callback();
        }
    }

    /* -------------------------------------------------- */
    /* Twig View                                          */
    /* -------------------------------------------------- */

    public function setTwig(Twig $twig): void
    {
        $this->twig = $twig;
    }

    /** Slim 2 : $app->view() */
    public function view(): Twig
    {
        return $this->twig;
    }

    /** Slim 2 : $app->render($template, $data) */
    public function render(string $template, array $data = []): void
    {
        if ($this->twig === null) {
            echo "<!-- Twig not initialized -->";
            return;
        }

        // Cherche avec et sans extension
        $env     = $this->twig->getEnvironment();
        $loader  = $env->getLoader();
        $exists  = false;

        try {
            $loader->getSourceContext($template);
            $exists = true;
        } catch (\Twig\Error\LoaderError $e) {
            try {
                $loader->getSourceContext($template . '.html');
                $template .= '.html';
                $exists = true;
            } catch (\Twig\Error\LoaderError $e2) {
                // template introuvable
            }
        }

        if ($exists) {
            echo $env->render($template, array_merge($this->viewGlobalData, $data));
        }
    }

    /** Données globales injectées dans chaque render */
    private array $viewGlobalData = [];

    public function appendViewData(array $data): void
    {
        $this->viewGlobalData = array_merge($this->viewGlobalData, $data);
    }

    public function getViewData(string $key = null)
    {
        if ($key === null) return $this->viewGlobalData;
        return $this->viewGlobalData[$key] ?? null;
    }

    /* -------------------------------------------------- */
    /* Flash Messages (slim/flash)                        */
    /* -------------------------------------------------- */

    public function setFlash(FlashMessages $flash): void
    {
        $this->flash = $flash;
    }

    /** Slim 2 : $app->flash($key, $value) */
    public function flash(string $key, $value): void
    {
        if ($this->flash !== null) {
            $this->flash->addMessage($key, $value);
        }
    }

    /** Slim 2 : $app->flashNow() — données disponibles dans la vue courante */
    public function flashNow(string $key, $value): void
    {
        $this->viewGlobalData[$key] = $value;
    }

    /* -------------------------------------------------- */
    /* Request (compatibilité Slim 2)                     */
    /* -------------------------------------------------- */

    /** Slim 2 : $app->request() */
    public function request(): SlimRequestBridge
    {
        return SlimRequestBridge::getInstance();
    }

    /* -------------------------------------------------- */
    /* Response (compatibilité Slim 2)                    */
    /* -------------------------------------------------- */

    /** Slim 2 : $app->response  (accès propriété) */
    public function __get(string $name)
    {
        if ($name === 'response') {
            return $this->currentResponse;
        }
        if ($name === 'environment') {
            return $this->viewGlobalData;
        }
        if ($name === 'log') {
            return new class {
                public function setEnabled(bool $v): void {}
                public function setLevel($l): void {}
                public function error($msg): void { error_log((string)$msg); }
            };
        }
        return null;
    }

    public function __set(string $name, $value): void
    {
        if ($name === 'environment') {
            if (is_array($value)) {
                $this->viewGlobalData = array_merge($this->viewGlobalData, $value);
            }
        }
    }

    /** Slim 2 : $app->response() */
    public function response(): SlimResponse
    {
        return $this->currentResponse;
    }

    /** Slim 2 : $app->contentType('application/json') */
    public function contentType(string $type): void
    {
        $this->contentType = $type;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function resetResponse(): void
    {
        $this->currentResponse = new SlimResponse();
        $this->contentType     = 'text/html';
    }

    /* -------------------------------------------------- */
    /* Routing                                            */
    /* -------------------------------------------------- */

    /**
     * Convertit les patterns Slim 2 en Slim 4.
     *
     *  /:param        →  /{param}
     *  (/:param)      →  [/{param}]
     *  /(:param)      →  [/{param}]
     *  /:page+        →  /{page:.*}
     *  :page+         →  /{page:.*}  (catch-all sans slash)
     *  :param         →  /{param}    (param sans slash)
     */
    public static function convertPattern(string $pattern): string
    {
        // Splat avec slash : /:name+ → /{name:.*}
        $pattern = preg_replace('#/:([a-zA-Z_][a-zA-Z0-9_]*)\+#', '/{$1:.*}', $pattern);
        // Splat sans slash : :name+ → /{name:.*}
        $pattern = preg_replace('#(?<![\/\{]):([a-zA-Z_][a-zA-Z0-9_]*)\+#', '/{$1:.*}', $pattern);
        // Groupes optionnels avec slash précédent : (/:param) → [/{param}]
        $pattern = preg_replace('#/\(:([a-zA-Z_][a-zA-Z0-9_]*)\)#', '[/{$1}]', $pattern);
        // Groupes optionnels : (/:param) → [/{param}]
        $pattern = preg_replace('#\(/:([a-zA-Z_][a-zA-Z0-9_]*)\)#', '[/{$1}]', $pattern);
        // Groupes optionnels simples : (:param) → [/{param}]
        $pattern = preg_replace('#\(:([a-zA-Z_][a-zA-Z0-9_]*)\)#', '[/{$1}]', $pattern);
        // Paramètres avec slash : /:param → /{param}
        $pattern = preg_replace('#/:([a-zA-Z_][a-zA-Z0-9_]*)(?!\w|\+)#', '/{$1}', $pattern);
        // Paramètres sans slash en début de pattern : :param → /{param}
        if (preg_match('#^:[a-zA-Z_]#', $pattern)) {
            $pattern = '/' . preg_replace('#^:([a-zA-Z_][a-zA-Z0-9_]*)#', '{$1}', $pattern);
        }
        return $pattern;
    }

    /**
     * Enveloppe un callback Slim 2 (qui fait echo) pour Slim 4.
     * Capture la sortie via ob_start et la met dans la response.
     * Intercepte RedirectException et PassException.
     */
    private function wrapCallback(callable $callback): callable
    {
        $bridge = $this;
        return function (Request $request, Response $response, array $args) use ($callback, $bridge): Response {
            SlimRequestBridge::setCurrentRequest($request);
            $bridge->resetResponse();

            try {
                ob_start();
                $bridge->applyHook('slim.before');
                call_user_func_array($callback, array_values($args));
                $captured = ob_get_clean();
            } catch (\App\Kernel\Exception\RedirectException $e) {
                ob_get_clean();
                return $response
                    ->withStatus($e->getHttpStatus())
                    ->withHeader('Location', $e->getUrl());
            } catch (\App\Kernel\Exception\PassException $e) {
                ob_get_clean();
                return $response->withStatus(404);
            }

            $body = $bridge->currentResponse->getBuffer();
            if ($body === '') {
                $body = $captured;
            }

            $psrResponse = $response->withHeader('Content-Type', $bridge->getContentType() . '; charset=utf-8');
            $psrResponse->getBody()->write($body);
            return $psrResponse;
        };
    }

    /** Slim 2 : $app->get($pattern, $callback) */
    public function get(string $pattern, callable $callback): RouteProxy
    {
        $route = $this->app->get(self::convertPattern($pattern), $this->wrapCallback($callback));
        return new RouteProxy($route);
    }

    /** Slim 2 : $app->post($pattern, $callback) */
    public function post(string $pattern, callable $callback): RouteProxy
    {
        $route = $this->app->post(self::convertPattern($pattern), $this->wrapCallback($callback));
        return new RouteProxy($route);
    }

    /** Slim 2 : $app->put($pattern, $callback) */
    public function put(string $pattern, callable $callback): RouteProxy
    {
        $route = $this->app->put(self::convertPattern($pattern), $this->wrapCallback($callback));
        return new RouteProxy($route);
    }

    /** Slim 2 : $app->delete($pattern, $callback) */
    public function delete(string $pattern, callable $callback): RouteProxy
    {
        $route = $this->app->delete(self::convertPattern($pattern), $this->wrapCallback($callback));
        return new RouteProxy($route);
    }

    /**
     * Slim 2 : $app->map($pattern, $callback)->via('GET','POST')
     * Retourne un objet avec ->via() pour la compatibilité chaînée.
     */
    public function map(string $pattern, callable $callback): object
    {
        $bridge    = $this;
        $converted = self::convertPattern($pattern);
        $wrapped   = $this->wrapCallback($callback);

        return new class($bridge->app, $converted, $wrapped) {
            public function __construct(
                private App $app,
                private string $pattern,
                private $wrapped
            ) {}

            public function via(string ...$methods): self
            {
                $this->app->map($methods, $this->pattern, $this->wrapped);
                return $this;
            }
        };
    }

    /**
     * Slim 2 : $app->group($prefix, $callback)
     * Dans Slim 4 le callback reçoit un RouteCollectorProxy.
     */
    public function group(string $prefix, callable $callback): void
    {
        $bridge = $this;
        $this->app->group($prefix, function (RouteCollectorProxy $group) use ($callback, $bridge): void {
            // Le callback Slim 2 attend $app (SlimBridge)
            // On substitue temporairement les méthodes de routing du bridge
            // vers le group proxy (via un sous-bridge local).
            $groupBridge = new GroupBridge($bridge, $group);
            $callback($groupBridge);
        });
    }

    /** Slim 2 : $app->pass() — ignore la route courante, laisse Slim chercher la suivante */
    public function pass(): void
    {
        // Slim 4 n'a pas de pass(). On lève une exception spéciale
        // capturée par le wrapCallback pour court-circuiter la route.
        throw new \App\Kernel\Exception\PassException();
    }

    /** Slim 2 : $app->notFound($callback) */
    public function notFound(callable $callback): void
    {
        // slim 4 : error middleware ou custom error handler
        // On stocke le callback pour l'utiliser dans le error handler 404.
        $this->app->map(['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'], '/{routes:.+}', $this->wrapCallback($callback));
    }

    /* -------------------------------------------------- */
    /* Redirect                                           */
    /* -------------------------------------------------- */

    /** Slim 2 : $app->redirect($url, $status) */
    public function redirect(string $url = '/', int $status = 302): void
    {
        // Stocké dans le currentResponse pour être lu par le wrapCallback
        $this->currentResponse->setRedirect($url, $status);
        // Arrête l'exécution du callback courant
        throw new \App\Kernel\Exception\RedirectException($url, $status);
    }

    /* -------------------------------------------------- */
    /* Hooks (compatibilité stub)                         */
    /* -------------------------------------------------- */

    private array $hooks = [];

    /** Slim 2 : $app->hook('slim.before', $callback) */
    public function hook(string $name, callable $callback): void
    {
        $this->hooks[$name][] = $callback;
    }

    public function applyHook(string $name): void
    {
        foreach ($this->hooks[$name] ?? [] as $callback) {
            $callback();
        }
    }

    /* -------------------------------------------------- */
    /* Middleware                                         */
    /* -------------------------------------------------- */

    /** Slim 2 : $app->add($middleware) */
    public function add($middleware): void
    {
        $this->app->add($middleware);
    }

    /* -------------------------------------------------- */
    /* Environment (Slim 2 : $app->environment())        */
    /* -------------------------------------------------- */

    public function environment(): array
    {
        return $this->viewGlobalData;
    }

    public function getLog()
    {
        return $this->__get('log');
    }

    /* -------------------------------------------------- */
    /* Run                                                */
    /* -------------------------------------------------- */

    public function run(): void
    {
        $this->app->run();
    }
}
