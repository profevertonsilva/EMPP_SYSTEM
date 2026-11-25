<?php

declare(strict_types=1);

namespace Empp\Database;

use PDO;

final class Connection
{
    /**
     * Strict SQL mode in every session: the local server runs with an empty mode,
     * production may not, and a value that does not fit must fail the same way in both.
     */
    private const SQL_MODE = 'STRICT_ALL_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,ONLY_FULL_GROUP_BY,NO_ENGINE_SUBSTITUTION';

    /** @param array{host: string, port: int, name: string, user: string, pass: string} $db */
    public static function open(array $db, bool $selectDatabase = true): PDO
    {
        $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', $db['host'], $db['port']);
        if ($selectDatabase) {
            $dsn .= ';dbname=' . $db['name'];
        }

        $pdo = new PDO($dsn, $db['user'], $db['pass'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_STRINGIFY_FETCHES => false,
        ]);
        $pdo->exec("SET SESSION sql_mode = '" . self::SQL_MODE . "', time_zone = '+00:00'");

        return $pdo;
    }

    /**
     * Parameters ready to bind without losing precision. PDO sends a PHP float as
     * text formatted with the `precision` setting (14 digits), which cuts the last
     * digits of a double; var_export writes the shortest text that reads back exactly.
     *
     * @param array<array-key, mixed> $params
     *
     * @return array<array-key, mixed>
     */
    public static function params(array $params): array
    {
        return array_map(static fn (mixed $v): mixed => match (true) {
            is_float($v) && is_finite($v) => var_export($v, true),
            is_float($v) => throw new \InvalidArgumentException('Cannot store a non-finite number'),
            is_bool($v) => (int) $v,
            default => $v,
        }, $params);
    }

    /** Creates the database when missing (utf8mb4). The name is checked, since it cannot be bound. */
    public static function createDatabase(array $db): void
    {
        self::assertName($db['name']);
        self::open($db, false)->exec(
            "CREATE DATABASE IF NOT EXISTS `{$db['name']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
    }

    public static function dropDatabase(array $db): void
    {
        self::assertName($db['name']);
        self::open($db, false)->exec("DROP DATABASE IF EXISTS `{$db['name']}`");
    }

    private static function assertName(string $name): void
    {
        if (!preg_match('/^[A-Za-z0-9_]{1,64}$/', $name)) {
            throw new \InvalidArgumentException('Database names may only use letters, digits and underscores');
        }
    }
}
