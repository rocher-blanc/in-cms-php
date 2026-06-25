<?php

namespace App\Kernel\Middleware\Front;

use App\Kernel\Middleware\AbstractMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class Module extends AbstractMiddleware
{
    public function __construct() {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        \App\Kernel\SlimRequestBridge::setCurrentRequest($request);
        $this->observe();
        return $handler->handle($request);
    }

    private function error(string $msg): array
    {
        return ['error' => true, 'result' => false, 'msg' => $msg];
    }

    public function observe(): void
    {
        $req = $this->request();

        if (
            $req->isPost()
            && $req->post('keyControl') != ''
            && $req->post('moduleName') != ''
        ) {
            $rst = [];
            $key = md5($req->post('moduleName') . $req->post('id_element'));

            if ($key != $req->post('keyControl')) {
                $rst = $this->error("La clef du module est incorrect");
            } else {
                if ($req->post('show') != '') {
                    $action = 'showIf';
                } elseif ($req->post('delete') != '') {
                    $action = 'delete';
                } else {
                    $action = 'default';
                }

                switch ($action) {
                    case 'showIf':
                        $Controller = \App\Kernel\Container::getInstance()->module($req->post('moduleName'))->getController();
                        $rst = $Controller->getShow();
                        break;

                    case 'delete':
                        $Controller = \App\Kernel\Container::getInstance()->module($req->post('moduleName'))->getController();
                        $Controller->setId($req->post('id_element'));
                        $rst = $Controller->delete();
                        break;

                    default:
                        $add        = ($req->post('id_element') == '-1');
                        $Controller = \App\Kernel\Container::getInstance()->module($req->post('moduleName'))->getController();
                        if (!$add) $Controller->setId($req->post('id_element'));
                        $rst = $Controller->listenForm($add);
                        break;
                }
            }

            if (!empty($rst) && $req->isAjax()) {
                $this->app()->contentType('application/json');
                $this->app()->response()->body(json_encode($rst));
            }
        }
    }
}
