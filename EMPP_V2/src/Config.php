<?php

declare(strict_types=1);

namespace Empp;

use Dotenv\Dotenv;
use RuntimeException;

/**
 * Settings from EMPP_V2/.env (see .env.example). Read into an array, never into
 * $_ENV/getenv, so the v1 application in the same process tree is not affected.
 */
final class Config
{
    /** @param array<string, string> $values */
    private function __construct(private readonly array $values)
    {
    }

    public static function load(?string $dir = null): self
    {
        $dir ??= dirname(__DIR__);
        if (!is_file($dir . '/.env')) {
            throw new RuntimeException("EMPP v2: $dir/.env not found. Copy .env.example to .env and fill it in.");
        }
        $dotenv = Dotenv::createArrayBacked($dir);
        $values = $dotenv->load();
        $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER'])->notEmpty();
        $dotenv->required('DB_PASS');

        return new self($values);
    }

    /** @param array<string, string> $values */
    public static function fromArray(array $values): self
    {
        return new self($values);
    }

    public function get(string $key, ?string $default = null): ?string
    {
        $value = $this->values[$key] ?? null;

        return $value === null || $value === '' ? $default : $value;
    }

    public function require(string $key): string
    {
        return $this->get($key) ?? throw new RuntimeException("EMPP v2: $key is not set in .env");
    }

    /**
     * Connection settings: the main database, or another one with the same server
     * settings under a prefix (V1_ for the v1 database read by the importer).
     *
     * @return array{host: string, port: int, name: string, user: string, pass: string}
     */
    public function database(string $prefix = ''): array
    {
        return [
            'host' => $this->get($prefix . 'DB_HOST') ?? $this->require('DB_HOST'),
            'port' => (int) ($this->get($prefix . 'DB_PORT') ?? $this->get('DB_PORT', '3306')),
            'name' => $this->require($prefix . 'DB_NAME'),
            'user' => $this->get($prefix . 'DB_USER') ?? $this->require('DB_USER'),
            'pass' => $prefix !== '' && $this->get($prefix . 'DB_USER') !== null
                ? (string) $this->get($prefix . 'DB_PASS', '')
                : (string) $this->get('DB_PASS', ''),
        ];
    }
}
