<?php

namespace App\Kernel\Middleware\Front;

use App\Kernel\AppContext;
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
        AppContext::setRequest($request);
        $this->observe();
        return $handler->handle($request);
    }

    public function observe(): void
    {
        if ($this->isPost() && $this->post('newsletter_action') == 'add') {
            $this->addNewsletter();
        }
    }

    protected function addNewsletter(): void
    {
        $nl = new NewsletterTool;
        $nl->setGroupId($this->post('list', ''));
        $nl->setEmail($this->post('email', ''));
        $result = $nl->subscribe();

        $message = $result
            ? $this->translate('newsletter_add_success')
            : $nl->getError();

        echo json_encode(['result' => $result, 'msg' => $message]);
    }

    protected function translate(string $key): string
    {
        return Translate::getInstance()->getText($key);
    }
}
