<?php

namespace App\Kernel\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CsrfGuard extends AbstractMiddleware
{
    protected string $key;

    public function __construct(string $key = 'csrf_token')
    {
        if (empty($key) || preg_match('/[^a-zA-Z0-9\-\_]/', $key)) {
            $this->Factory()->Response()->error('Invalid CSRF token key "' . $key . '"');
        }
        $this->key = $key;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        \App\Kernel\SlimRequestBridge::setCurrentRequest($request);
        $this->check($request);
        return $handler->handle($request);
    }

    public function check(Request $request): void
    {
        if (session_id() === '') {
            $this->Factory()->Response()->error('Sessions are required to use the CSRF Guard middleware.');
        }

        if (!isset($_SESSION[$this->key])) {
            $_SESSION[$this->key] = sha1(serialize($_SERVER) . rand(0, 99999999));
        }

        $token  = $_SESSION[$this->key];
        $method = strtoupper($request->getMethod());

        if (in_array($method, ['POST', 'PUT', 'DELETE'])) {
            $contentType = $request->getHeaderLine('Content-Type');

            if (!empty($contentType) && strpos($contentType, 'application/json') !== false) {
                $json       = (string) $request->getBody();
                $_POST      = json_decode($json, true) ?? [];
                $userToken  = $_POST[$this->key] ?? null;
            } else {
                $body      = $request->getParsedBody();
                $userToken = is_array($body) ? ($body[$this->key] ?? null) : null;
            }

            if ($token !== $userToken) {
                // Réponse 400 CSRF invalid
                throw new \App\Kernel\Exception\RedirectException('/', 400);
            }
        }

        $this->app()->appendViewData([
            'csrf_key'   => $this->key,
            'csrf_token' => $token,
        ]);
    }
}
