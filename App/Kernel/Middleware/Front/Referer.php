<?php

namespace App\Kernel\Middleware\Front;

use App\Kernel\Middleware\AbstractMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class Referer extends AbstractMiddleware
{
    public function __construct() {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        \App\Kernel\SlimRequestBridge::setCurrentRequest($request);
        $this->observe();
        return $handler->handle($request);
    }

    public function observe(): void
    {
        if (isset($_SERVER['HTTP_REFERER'])) {
            if (strpos($_SERVER['HTTP_REFERER'], $_SERVER['HTTP_HOST'] ?? '') === false) {
                $_SESSION['referer'] = $_SERVER['HTTP_REFERER'];
            }
        }
    }
}
