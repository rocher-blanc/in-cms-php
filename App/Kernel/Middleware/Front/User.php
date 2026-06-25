<?php

namespace App\Kernel\Middleware\Front;

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

    private function user(): \App\Kernel\Front\User
    {
        return \App\Kernel\Front\User::getInstance();
    }

    public function observe(): void
    {
        $req = $this->request();
        $this->user()->observe();

        if ($req->isPost()) {
            $action = $req->post('user_action');
            if ($action == 'login')             $this->user()->login();
            if ($action == 'lost_password')     $this->user()->lostPassword();
            if ($action == 'recovery_password') $this->user()->recoveryPassword();
            if ($action == 'update')            $this->user()->update();
            if ($action == 'update_password')   $this->user()->updatePassword();
            if ($action == 'register')          $this->user()->register();
        } else {
            $this->user()->connectWithFacebook();
            if ($req->get('logout') == 'me')           $this->user()->logout();
            if ($req->get('user_validation') == 'me')  $this->user()->validation();
        }

        $this->user()->appendVar();
    }
}
