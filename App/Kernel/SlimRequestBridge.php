<?php

namespace App\Kernel;

use Psr\Http\Message\ServerRequestInterface;

/**
 * Façade Slim 2 Request par-dessus PSR-7.
 *
 * Slim 2 :  $app->request()->isPost()
 *           $app->request()->post('field')
 *           $app->request()->get('param')
 *           $app->request()->getPath()
 *           $app->request->isPost()
 *           $app->request->post('field')
 */
class SlimRequestBridge
{
    private static ?self $instance = null;
    private ?ServerRequestInterface $request = null;

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public static function setCurrentRequest(ServerRequestInterface $request): void
    {
        self::getInstance()->request = $request;
    }

    /* -------------------------------------------------- */
    /* Slim 2 Request API                                 */
    /* -------------------------------------------------- */

    public function isPost(): bool
    {
        return $this->getMethod() === 'POST';
    }

    public function isGet(): bool
    {
        return $this->getMethod() === 'GET';
    }

    public function isPut(): bool
    {
        return $this->getMethod() === 'PUT';
    }

    public function isDelete(): bool
    {
        return $this->getMethod() === 'DELETE';
    }

    public function getMethod(): string
    {
        if ($this->request !== null) {
            return strtoupper($this->request->getMethod());
        }
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    /** Slim 2 : $app->request()->post('field') */
    public function post(string $key = null, $default = null)
    {
        $body = $this->request?->getParsedBody() ?? $_POST;
        if ($key === null) return $body;
        return (is_array($body) ? ($body[$key] ?? $default) : $default);
    }

    /** Slim 2 : $app->request()->get('param') */
    public function get(string $key = null, $default = null)
    {
        $query = $this->request?->getQueryParams() ?? $_GET;
        if ($key === null) return $query;
        return $query[$key] ?? $default;
    }

    /** Slim 2 : $app->request()->params('key') — GET + POST fusionnés */
    public function params(string $key = null, $default = null)
    {
        $all = array_merge(
            $this->request?->getQueryParams() ?? $_GET,
            (is_array($this->request?->getParsedBody()) ? $this->request->getParsedBody() : []),
        );
        if ($key === null) return $all;
        return $all[$key] ?? $default;
    }

    /** Slim 2 : $app->request()->getPath() */
    public function getPath(): string
    {
        if ($this->request !== null) {
            return $this->request->getUri()->getPath();
        }
        return parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    }

    /** Slim 2 : $app->request()->getContentType() */
    public function getContentType(): string
    {
        return $this->request?->getHeaderLine('Content-Type') ?? '';
    }

    /** Slim 2 : $app->request()->isAjax() */
    public function isAjax(): bool
    {
        $header = $this->request?->getHeaderLine('X-Requested-With') ?? ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '');
        return strtolower($header) === 'xmlhttprequest';
    }

    /**
     * Slim 2 : $app->request()->getUrl() — retourne scheme://host
     */
    public function getUrl(): string
    {
        if ($this->request !== null) {
            $uri = $this->request->getUri();
            return $uri->getScheme() . '://' . $uri->getHost() . ($uri->getPort() ? ':' . $uri->getPort() : '');
        }
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';
        return $scheme . '://' . $host;
    }

    /**
     * Slim 2 : $app->request()->getResourceUri() — chemin sans query string
     */
    public function getResourceUri(): string
    {
        return $this->getPath();
    }

    /**
     * Slim 2 : $app->request()->headers($key) — accès aux headers
     */
    public function headers(string $key = null)
    {
        if ($this->request === null) return null;
        if ($key === null) return $this->request->getHeaders();
        return $this->request->getHeaderLine($key) ?: null;
    }

    /**
     * Slim 2 accède à la request via propriété : $app->request->isPost()
     * On délègue les appels de propriétés aux méthodes dynamiquement.
     */
    public function __get(string $name)
    {
        if (method_exists($this, $name)) {
            return $this->$name();
        }
        return null;
    }
}
