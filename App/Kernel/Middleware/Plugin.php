<?php

namespace App\Kernel\Middleware;

use App\Kernel\AppContext;
use App\Kernel\Exception\NotFoundException;
use App\Kernel\Exception\RedirectException;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

class Plugin extends AbstractMiddleware
{
    private array $arrayPlugin = [];

    public function __construct(array $arrayPlugin = [])
    {
        $this->arrayPlugin = $arrayPlugin;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        AppContext::setRequest($request);

        try {
            $this->load();
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

    public function load(): void
    {
        foreach ($this->arrayPlugin as $row) {
            if (method_exists($row, 'load')) {
                $row->load();
            } else {
                throw new \App\Kernel\Exception('Function "load" is not defined on plugin "' . get_class($row) . '"');
            }
        }
    }
}
