<?php

namespace App\Kernel\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class Plugin extends AbstractMiddleware
{
    private array $arrayPlugin = [];

    public function __construct(array $arrayPlugin = [])
    {
        $this->arrayPlugin = $arrayPlugin;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        \App\Kernel\SlimRequestBridge::setCurrentRequest($request);
        $this->load();
        return $handler->handle($request);
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
