<?php

namespace App\Kernel\Middleware\Front;

use App\Kernel\Middleware\AbstractMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class Adwords extends AbstractMiddleware
{
    public function __construct() {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        \App\Kernel\AppContext::setRequest($request);
        $this->observe();
        return $handler->handle($request);
    }

    public function observe(): void
    {
        if (isset($_GET['gclid'])) {
            $_SESSION['adwords']['gclid'] = $_GET['gclid'];
        }
    }
}
