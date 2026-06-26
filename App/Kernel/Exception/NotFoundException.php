<?php

namespace App\Kernel\Exception;

/**
 * Levée par Response::show404() pour court-circuiter le pipeline Slim
 * et retourner une réponse 404 sans HttpNotFoundException.
 */
class NotFoundException extends \RuntimeException
{
    public function __construct(private readonly string $body = '')
    {
        parent::__construct('Not Found', 404);
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
