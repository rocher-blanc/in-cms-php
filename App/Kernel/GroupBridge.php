<?php

namespace App\Kernel;

use Slim\Routing\RouteCollectorProxy;
use App\Kernel\RouteProxy;

/**
 * Proxy utilisé à l'intérieur d'un group() Slim 4.
 *
 * Dans Slim 2, le callback d'un group reçoit $app (le SlimBridge global).
 * Dans Slim 4, il reçoit un RouteCollectorProxy local au groupe.
 *
 * Ce bridge expose les méthodes get/post/map/group/notFound du SlimBridge
 * mais redirige la registration des routes vers le RouteCollectorProxy du groupe.
 *
 * Les méthodes non-routing (config, view, render, flash, redirect…) sont
 * déléguées au SlimBridge parent.
 */
class GroupBridge
{
    public function __construct(
        private SlimBridge          $parent,
        private RouteCollectorProxy $proxy
    ) {}

    /* -------------------------------------------------- */
    /* Routing → vers le RouteCollectorProxy              */
    /* -------------------------------------------------- */

    public function get(string $pattern, callable $callback): RouteProxy
    {
        $route = $this->proxy->get(SlimBridge::convertPattern($pattern), $this->wrapCallback($callback));
        return new RouteProxy($route);
    }

    public function post(string $pattern, callable $callback): RouteProxy
    {
        $route = $this->proxy->post(SlimBridge::convertPattern($pattern), $this->wrapCallback($callback));
        return new RouteProxy($route);
    }

    public function put(string $pattern, callable $callback): RouteProxy
    {
        $route = $this->proxy->put(SlimBridge::convertPattern($pattern), $this->wrapCallback($callback));
        return new RouteProxy($route);
    }

    public function delete(string $pattern, callable $callback): RouteProxy
    {
        $route = $this->proxy->delete(SlimBridge::convertPattern($pattern), $this->wrapCallback($callback));
        return new RouteProxy($route);
    }

    public function map(string $pattern, callable $callback): object
    {
        $converted = SlimBridge::convertPattern($pattern);
        $wrapped   = $this->wrapCallback($callback);
        $proxy     = $this->proxy;

        return new class($proxy, $converted, $wrapped) {
            public function __construct(
                private RouteCollectorProxy $proxy,
                private string $pattern,
                private $wrapped
            ) {}

            public function via(string ...$methods): self
            {
                $this->proxy->map($methods, $this->pattern, $this->wrapped);
                return $this;
            }
        };
    }

    public function group(string $prefix, callable $callback): void
    {
        $parent = $this->parent;
        $this->proxy->group($prefix, function (RouteCollectorProxy $inner) use ($callback, $parent): void {
            $groupBridge = new self($parent, $inner);
            $callback($groupBridge);
        });
    }

    /** Slim 2 : $app->notFound() à l'intérieur d'un group */
    public function notFound(callable $callback): void
    {
        $this->proxy->map(
            ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS'],
            '/{routes:.+}',
            $this->wrapCallback($callback)
        );
    }

    /* -------------------------------------------------- */
    /* Délégation au SlimBridge parent                    */
    /* -------------------------------------------------- */

    public function __call(string $method, array $args)
    {
        return $this->parent->$method(...$args);
    }

    public function __get(string $name)
    {
        return $this->parent->$name;
    }

    /* -------------------------------------------------- */
    /* Wrapping callbacks (même logique que SlimBridge)   */
    /* -------------------------------------------------- */

    private function wrapCallback(callable $callback): callable
    {
        $parent = $this->parent;
        return function (
            \Psr\Http\Message\ServerRequestInterface $request,
            \Psr\Http\Message\ResponseInterface      $response,
            array $args
        ) use ($callback, $parent): \Psr\Http\Message\ResponseInterface {
            SlimRequestBridge::setCurrentRequest($request);
            $parent->resetResponse();

            try {
                ob_start();
                $parent->applyHook('slim.before');
                call_user_func_array($callback, array_values($args));
                $captured = ob_get_clean();
            } catch (Exception\RedirectException $e) {
                ob_get_clean();
                return $response
                    ->withStatus($e->getHttpStatus())
                    ->withHeader('Location', $e->getUrl());
            } catch (Exception\PassException $e) {
                ob_get_clean();
                return $response->withStatus(404);
            }

            $body = $parent->response()->getBuffer();
            if ($body === '') {
                $body = $captured;
            }

            $psrResponse = $response->withHeader('Content-Type', $parent->getContentType() . '; charset=utf-8');
            $psrResponse->getBody()->write($body);
            return $psrResponse;
        };
    }
}
