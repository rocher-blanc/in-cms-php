<?php

namespace App\Kernel;

/**
 * Value Object singleton pour la configuration de l'application.
 *
 * Remplace l'API SlimBridge::config() en la découplant du routeur.
 * SRP : lire et écrire de la config, rien d'autre.
 */
class Config
{
    private static ?self $instance = null;

    private array $data = [];

    private function __construct() {}

    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /** Réinitialise le singleton (tests unitaires) */
    public static function reset(): void
    {
        self::$instance = null;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, mixed $value): void
    {
        $this->data[$key] = $value;
    }

    /**
     * Fusionne un tableau de config (appelé par App\Kernel via setConfig).
     * Accepte aussi la notation pointée (ex: 'login.url').
     */
    public function merge(array $data): void
    {
        foreach ($data as $k => $v) {
            $this->data[$k] = $v;
        }
    }

    public function all(): array
    {
        return $this->data;
    }
}
