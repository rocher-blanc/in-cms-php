<?php

namespace App\Kernel\Middleware;

use App\Kernel\SlimBridge;
use App\Kernel\SlimRequestBridge;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Classe de base pour tous les middlewares PSR-15.
 *
 * Fournit l'accès au SlimBridge et à la request courante,
 * réduisant le boilerplate de migration depuis Slim\Middleware.
 */
abstract class AbstractMiddleware implements MiddlewareInterface
{
    abstract public function process(Request $request, RequestHandler $handler): Response;

    protected function app(): SlimBridge
    {
        return SlimBridge::getInstance();
    }

    protected function request(): SlimRequestBridge
    {
        return SlimRequestBridge::getInstance();
    }

    protected function Factory(): \App\Kernel\Factory
    {
        return \App\Kernel\Factory::getInstance();
    }
}
