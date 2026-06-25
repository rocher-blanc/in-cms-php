<?php

namespace App\Kernel\Middleware\Front;

use App\Kernel\Middleware\AbstractMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

/**
 * Middleware d'injection CSS/JS.
 *
 * Après exécution du handler, recherche l'extension TwigFront parmi les
 * extensions Twig pour remplacer les placeholders ASSET_CSS_VAR / ASSET_JS_VAR
 * dans le HTML produit.
 */
class Assetic extends AbstractMiddleware
{
    public function __construct() {}

    public function process(Request $request, RequestHandler $handler): Response
    {
        $response = $handler->handle($request);
        return $this->modifyResponse($response);
    }

    private function modifyResponse(Response $response): Response
    {
        $twig = $this->app()->view();
        if ($twig === null) return $response;

        $extensions = $twig->getEnvironment()->getExtensions();
        foreach ($extensions as $ext) {
            if (str_ends_with(get_class($ext), 'TwigFront')) {
                $html = (string) $response->getBody();
                $html = str_replace(ASSET_CSS_VAR, $ext->css(), $html);
                $html = str_replace(ASSET_JS_VAR, $ext->javascript(), $html);

                $body = \Slim\Psr7\Factory\StreamFactory::class
                    ? (new \Slim\Psr7\Factory\StreamFactory())->createStream($html)
                    : \GuzzleHttp\Psr7\stream_for($html);

                return $response->withBody($body);
            }
        }
        return $response;
    }
}
