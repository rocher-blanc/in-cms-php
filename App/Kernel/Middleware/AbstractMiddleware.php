<?php

namespace App\Kernel\Middleware;

use App\Kernel\AppContext;
use App\Kernel\Config;
use App\Kernel\Exception\NotFoundException;
use App\Kernel\Exception\RedirectException;
use App\Kernel\Factory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

/**
 * Classe de base pour tous les middlewares PSR-15.
 *
 * Fournit :
 *  - Des helpers vers Config, Factory et la requête courante (DI allégé)
 *  - La mise à jour de AppContext::request() à chaque passage
 *  - La capture de RedirectException → réponse HTTP 3xx
 */
abstract class AbstractMiddleware implements MiddlewareInterface
{
    abstract public function process(Request $request, RequestHandler $handler): Response;

    /* ------------------------------------------------------------------ */
    /* Helpers services                                                    */
    /* ------------------------------------------------------------------ */

    protected function config(): Config
    {
        return Config::getInstance();
    }

    protected function Factory(): Factory
    {
        return Factory::getInstance();
    }

    /* ------------------------------------------------------------------ */
    /* Helpers requête (remplacent SlimRequestBridge)                     */
    /* ------------------------------------------------------------------ */

    protected function currentRequest(): ?Request
    {
        return AppContext::request();
    }

    protected function isPost(): bool
    {
        return strtoupper(AppContext::request()?->getMethod() ?? '') === 'POST';
    }

    protected function post(string $key, mixed $default = null): mixed
    {
        $body = AppContext::request()?->getParsedBody();
        return is_array($body) ? ($body[$key] ?? $default) : $default;
    }

    protected function get(string $key, mixed $default = null): mixed
    {
        $params = AppContext::request()?->getQueryParams() ?? [];
        return $params[$key] ?? $default;
    }

    protected function isAjax(): bool
    {
        return AppContext::request()?->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    /* ------------------------------------------------------------------ */
    /* Gestion des redirections depuis la logique métier                  */
    /* ------------------------------------------------------------------ */

    /**
     * Wraps le handler en capturant les RedirectException.
     *
     * Pourquoi : Factory\Response::redirect() est appelé depuis des méthodes
     * imbriquées (ex : observe → redirectLogin → redirect → throw).
     * Ce helper centralise la conversion exception → réponse PSR-7.
     */
    protected function handleRequest(callable $logic, Request $request, RequestHandler $handler): Response
    {
        AppContext::setRequest($request);

        try {
            $logic();
            return $handler->handle($request);
        } catch (NotFoundException $e) {
            $response = new SlimResponse(404);
            $response->getBody()->write($e->getBody());
            return $response;
        } catch (RedirectException $e) {
            return (new SlimResponse($e->getHttpStatus()))
                ->withHeader('Location', $e->getUrl());
        }
    }
}
