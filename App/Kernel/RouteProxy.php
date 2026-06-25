<?php

namespace App\Kernel;

use Slim\Interfaces\RouteInterface;

/**
 * Proxy autour d'un RouteInterface Slim 4.
 *
 * Ajoute les méthodes Slim 2 :
 *   ->conditions(['param' => 'regex'])  — absorbées (Slim 4 intègre le regex dans le pattern)
 *   ->via('GET', 'POST')               — no-op car la méthode est déjà fixée
 *
 * Permet de chaîner sans modifier tous les fichiers de routage.
 */
class RouteProxy
{
    public function __construct(private RouteInterface $route) {}

    /** Slim 2 : ->conditions(['param' => '[a-z]+']) — ignoré en Slim 4 */
    public function conditions(array $conditions): self
    {
        return $this;
    }

    /** Slim 2 : ->via('GET', 'POST') — ignoré si la méthode est déjà définie */
    public function via(string ...$methods): self
    {
        return $this;
    }

    /** Délégation transparente vers le vrai RouteInterface */
    public function __call(string $name, array $args)
    {
        return $this->route->$name(...$args);
    }
}
