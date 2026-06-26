<?php

namespace App\Kernel\Middleware\Front;

use App\Kernel\AppContext;
use App\Kernel\Middleware\AbstractMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class Module extends AbstractMiddleware
{
    public function __construct() {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        AppContext::setRequest($request);
        $this->observe();
        return $handler->handle($request);
    }

    private function error(string $msg): array
    {
        return ['error' => true, 'result' => false, 'msg' => $msg];
    }

    public function observe(): void
    {
        if (
            !$this->isPost() ||
            $this->post('keyControl', '') === '' ||
            $this->post('moduleName', '') === ''
        ) {
            return;
        }

        $key = md5($this->post('moduleName') . $this->post('id_element', ''));

        if ($key !== $this->post('keyControl')) {
            $rst = $this->error('La clef du module est incorrect');
        } else {
            if ($this->post('show', '') !== '') {
                $action = 'showIf';
            } elseif ($this->post('delete', '') !== '') {
                $action = 'delete';
            } else {
                $action = 'default';
            }

            switch ($action) {
                case 'showIf':
                    $Controller = \App\Kernel\Container::getInstance()->module($this->post('moduleName'))->getController();
                    $rst = $Controller->getShow();
                    break;

                case 'delete':
                    $Controller = \App\Kernel\Container::getInstance()->module($this->post('moduleName'))->getController();
                    $Controller->setId($this->post('id_element'));
                    $rst = $Controller->delete();
                    break;

                default:
                    $add        = ($this->post('id_element', '') == '-1');
                    $Controller = \App\Kernel\Container::getInstance()->module($this->post('moduleName'))->getController();
                    if (!$add) $Controller->setId($this->post('id_element'));
                    $rst = $Controller->listenForm($add);
                    break;
            }
        }

        if (!empty($rst) && $this->isAjax()) {
            if (!headers_sent()) {
                header('Content-Type: application/json; charset=utf-8');
            }
            echo json_encode($rst);
        }
    }
}
