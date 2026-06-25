<?php

namespace App\Kernel;

/**
 * Objet response mutable qui émule l'API Slim 2.
 *
 * Slim 2 : $app->response->body($content)
 *          $app->response()->body($content)
 *          $app->response()->status(500)
 *          $app->response->getBody()
 *
 * Le buffer est lu par SlimBridge::wrapCallback et injecté dans la PSR-7 response.
 */
class SlimResponse
{
    private string $buffer   = '';
    private int    $status   = 200;
    private ?string $redirect = null;
    private int     $redirectStatus = 302;

    /* -------------------------------------------------- */
    /* Slim 2 API                                         */
    /* -------------------------------------------------- */

    /** Slim 2 : $app->response->body($content) ou $app->response()->body($content) */
    public function body(string $content = null): string
    {
        if ($content !== null) {
            $this->buffer = $content;
        }
        return $this->buffer;
    }

    /** Slim 2 : $app->response()->status($code) */
    public function status(int $code = null): int
    {
        if ($code !== null) {
            $this->status = $code;
        }
        return $this->status;
    }

    /* -------------------------------------------------- */
    /* Accès interne (lu par SlimBridge)                  */
    /* -------------------------------------------------- */

    public function getBuffer(): string
    {
        return $this->buffer;
    }

    /** Slim 2 compat : $app->response->getBody() */
    public function getBody(): string
    {
        return $this->buffer;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function setRedirect(string $url, int $status = 302): void
    {
        $this->redirect       = $url;
        $this->redirectStatus = $status;
    }

    public function hasRedirect(): bool
    {
        return $this->redirect !== null;
    }

    public function getRedirectUrl(): ?string
    {
        return $this->redirect;
    }

    public function getRedirectStatus(): int
    {
        return $this->redirectStatus;
    }
}
