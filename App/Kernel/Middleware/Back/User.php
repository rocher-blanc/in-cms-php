<?php

namespace App\Kernel\Middleware\Back;

use App\Kernel\Middleware\AbstractMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class User extends AbstractMiddleware
{
    public function __construct() {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        \App\Kernel\SlimRequestBridge::setCurrentRequest($request);
        if (defined('ACTIVE_USER') && ACTIVE_USER) {
            $this->observe();
        }
        return $handler->handle($request);
    }

    private function user(): \App\Kernel\Back\User
    {
        return \App\Kernel\Back\User::getInstance();
    }

    public function observe(): void
    {
        $req = $this->request();
        if ($req->isPost()) {
            if ($req->post('user_action') == 'update')   $this->user()->update();
            if ($req->post('user_action') == 'register') $this->user()->register();
        }
    }
}
