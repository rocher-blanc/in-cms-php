<?php

namespace App\Kernel\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class APCCache extends AbstractMiddleware
{
    protected array $settings;

    public function __construct(array $settings = [])
    {
        if (extension_loaded('apc') && ini_get('apc.enabled')) {
            $this->settings = array_merge([
                'ttl'            => 300,
                'caching_prefix' => 'SlimCache_',
            ], $settings);
        } elseif (!defined('DEBUG_CMS') || !DEBUG_CMS) {
            $this->Factory()->Response()->error('APC not available');
        }
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        if (defined('DEBUG_CMS') && DEBUG_CMS) {
            return $handler->handle($request);
        }

        $keyName = $this->settings['caching_prefix'] . $request->getUri()->getPath();

        if (apc_exists($keyName)) {
            $data    = apc_fetch($keyName);
            $factory = \Slim\Factory\AppFactory::determineResponseFactory();
            $resp    = $factory->createResponse(200);
            foreach ($data['header'] as $key => $value) {
                $resp = $resp->withHeader($key, $value);
            }
            $resp->getBody()->write($data['body']);
            return $resp;
        }

        $response = $handler->handle($request);

        if ($response->getStatusCode() === 200 && $this->settings['ttl'] > 0) {
            $data = [
                'header' => $response->getHeaders(),
                'body'   => (string) $response->getBody(),
            ];
            apc_store($keyName, $data, $this->settings['ttl']);
        }

        return $response;
    }
}
