<?php

namespace App\Kernel\Middleware\Front;

use App\Kernel\AppContext;
use App\Kernel\Middleware\AbstractMiddleware;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Factory\StreamFactory;

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
        $twig = AppContext::twig();
        if ($twig === null) {
            return $response;
        }

        foreach ($twig->getEnvironment()->getExtensions() as $ext) {
            if (str_ends_with(get_class($ext), 'TwigFront')) {
                $html = (string) $response->getBody();
                $html = str_replace(ASSET_CSS_VAR, $ext->css(), $html);
                $html = str_replace(ASSET_JS_VAR, $ext->javascript(), $html);

                $body = (new StreamFactory())->createStream($html);
                return $response->withBody($body);
            }
        }

        return $response;
    }
}
