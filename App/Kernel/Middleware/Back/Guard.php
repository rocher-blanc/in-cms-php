<?php

namespace App\Kernel\Middleware\Back;

use App\Kernel\AppContext;
use App\Kernel\Middleware\AbstractMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class Guard extends AbstractMiddleware
{
    public function __construct() {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        return $this->handleRequest(fn() => $this->check(), $request, $handler);
    }

    public function check(): void
    {
        $check  = ['ext', 'admin', 'module'];
        $urlTab = $this->Factory()->Url()->cutUrl();
        $Guard  = new \App\Kernel\Back\Acl;

        AppContext::addGlobal('isAdmin', $Guard->isAdmin());

        if (!empty($urlTab)) {
            if (in_array($urlTab[0], $check)) {
                if ($urlTab[0] === 'ext' || $urlTab[0] === 'module') {
                    if ($urlTab[0] === 'ext')        $Guard->setExtension($urlTab[1]);
                    elseif ($urlTab[0] === 'module') $Guard->setModule($urlTab[1]);

                    $Guard->load();

                    if ($Guard->hasRight() === false) {
                        $this->Factory()->Response()->redirectForbidden();
                    } else {
                        if (array_key_exists(2, $urlTab)) {
                            $action = $urlTab[2];
                            if (in_array($action, ['enable', 'disable'])) {
                                if (!$Guard->checkValidation()) $this->Factory()->Response()->redirectForbidden();
                            } elseif ($action === 'add') {
                                if (!$Guard->checkAdd()) $this->Factory()->Response()->redirectForbidden();
                            } elseif ($action === 'config') {
                                if (!$Guard->checkConfig()) $this->Factory()->Response()->redirectForbidden();
                            } elseif ($action === 'delete') {
                                if (!$Guard->checkDelete()) $this->Factory()->Response()->redirectForbidden();
                            }
                        }
                    }
                } elseif ($urlTab[0] === 'admin' && !$Guard->isAdmin()) {
                    $this->Factory()->Response()->redirectForbidden();
                }
            }
        }
    }
}
