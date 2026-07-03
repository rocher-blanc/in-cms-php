<?php

namespace App\Kernel\Http;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Adaptateur (pattern Adapter) autour de la requête PSR-7 de Slim 4.
 *
 * Pourquoi : le code applicatif hérité (controllers de pages, modules…)
 * appelle l'API « requête » de Slim 2 :
 *   $request->isAjax(), isPost(), isGet(), post($k), get($k),
 *   getPath(), getUrl()
 * Slim 4 expose à la place une ServerRequestInterface PSR-7. Plutôt que de
 * réécrire tous les controllers (dont le code métier du projet, hors scope),
 * on encapsule le PSR-7 dans cet adaptateur qui restaure l'API attendue et
 * délègue le reste (getParsedBody, getUri, getMethod…) via __call.
 *
 * Concept senior : Adapter/Decorator — on isole la dette de migration dans
 * une seule classe au lieu de la disséminer, ce qui respecte OCP (ouvert à
 * l'extension) et DRY.
 */
class Request
{
    public function __construct(private readonly ServerRequestInterface $psr) {}

    /* -------------------------------------------------- */
    /* API Slim 2                                         */
    /* -------------------------------------------------- */

    public function isPost(): bool
    {
        return strtoupper($this->psr->getMethod()) === 'POST';
    }

    public function isGet(): bool
    {
        return strtoupper($this->psr->getMethod()) === 'GET';
    }

    public function isAjax(): bool
    {
        return strtolower($this->psr->getHeaderLine('X-Requested-With')) === 'xmlhttprequest';
    }

    /**
     * Retourne un paramètre POST (ou tout le corps parsé si $key === null).
     */
    public function post(?string $key = null, mixed $default = null): mixed
    {
        $body = $this->psr->getParsedBody();
        if (!is_array($body)) {
            $body = [];
        }
        if ($key === null) {
            return $body;
        }
        return $body[$key] ?? $default;
    }

    /**
     * Retourne un paramètre GET (ou tous les paramètres si $key === null).
     */
    public function get(?string $key = null, mixed $default = null): mixed
    {
        $params = $this->psr->getQueryParams();
        if ($key === null) {
            return $params;
        }
        return $params[$key] ?? $default;
    }

    /** Chemin de la requête (ex : /contact). */
    public function getPath(): string
    {
        return $this->psr->getUri()->getPath();
    }

    /** URL de base (scheme://host[:port]) — équivalent Slim 2. */
    public function getUrl(): string
    {
        $uri  = $this->psr->getUri();
        $url  = $uri->getScheme() . '://' . $uri->getHost();
        $port = $uri->getPort();
        if ($port !== null && !in_array($port, [80, 443], true)) {
            $url .= ':' . $port;
        }
        return $url;
    }

    /* -------------------------------------------------- */
    /* Accès au PSR-7 sous-jacent + délégation            */
    /* -------------------------------------------------- */

    public function psr(): ServerRequestInterface
    {
        return $this->psr;
    }

    /** Délègue toute autre méthode (getParsedBody, getUri, getMethod…) au PSR-7. */
    public function __call(string $name, array $arguments): mixed
    {
        return $this->psr->{$name}(...$arguments);
    }
}
