<?php

namespace App\Kernel\Exception;

/**
 * Levée par SlimBridge::redirect() pour interrompre l'exécution du callback
 * et transmettre la redirection à Slim 4 via le wrapCallback.
 */
class RedirectException extends \RuntimeException
{
    public function __construct(
        private readonly string $url,
        private readonly int    $status = 302
    ) {
        parent::__construct("Redirect to {$url}", $status);
    }

    public function getUrl(): string   { return $this->url; }
    public function getHttpStatus(): int { return $this->status; }
}
