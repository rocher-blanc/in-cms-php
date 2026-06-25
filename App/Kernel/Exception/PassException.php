<?php

namespace App\Kernel\Exception;

/**
 * Levée par SlimBridge::pass() pour signaler que la route courante
 * doit être ignorée (équivalent Slim 2 : $app->pass()).
 *
 * Dans Slim 4 il n'y a plus d'équivalent direct. Le wrapCallback
 * capture cette exception et retourne une 404 pour passer à la
 * prochaine route correspondante.
 */
class PassException extends \RuntimeException
{
    public function __construct()
    {
        parent::__construct('Route pass — try next route');
    }
}
