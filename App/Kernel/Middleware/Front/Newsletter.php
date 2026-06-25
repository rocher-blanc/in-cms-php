<?php

namespace App\Kernel\Middleware\Front;

use App\Kernel\Front\Translate;
use App\Kernel\Front\Newsletter as NewsletterTool;
use App\Kernel\Middleware\AbstractMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class Newsletter extends AbstractMiddleware
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
        if ($this->request()->isPost()) {
            if ($this->request()->post('newsletter_action') == 'add') {
                $this->addNewsletter();
            }
        }
    }

    protected function addNewsletter(): void
    {
        $list  = $this->request()->post('list');
        $email = $this->request()->post('email');

        $nl = new NewsletterTool;
        $nl->setGroupId($list);
        $nl->setEmail($email);
        $result = $nl->subscribe();

        if ($result) {
            $this->returnError($this->translate('newsletter_add_success'), true);
        } else {
            $this->returnError($nl->getError(), false);
        }
    }

    protected function returnError(string $message, bool $result = false): void
    {
        $this->app()->response()->body(json_encode([
            'result' => $result,
            'msg'    => $message,
        ]));
    }

    protected function translate(string $key): string
    {
        return Translate::getInstance()->getText($key);
    }
}
