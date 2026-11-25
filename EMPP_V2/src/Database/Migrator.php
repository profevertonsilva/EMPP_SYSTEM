<?php

declare(strict_types=1);

namespace Empp\Database;

use PDO;
use RuntimeException;

/**
 * Applies database/migrations/*.sql in name order, once each, recording them in
 * schema_migrations. MySQL commits DDL statement by statement, so a migration
 * that fails half-way is not rolled back: fix it and finish it by hand.
 */
final class Migrator
{
    public function __construct(private readonly PDO $pdo, private readonly string $dir)
    {
    }

    /** @return list<string> versions applied by this call */
    public function migrate(?callable $log = null): array
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS schema_migrations (
                version VARCHAR(100) NOT NULL PRIMARY KEY,
                applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
        );
        $done = array_flip($this->pdo->query('SELECT version FROM schema_migrations')->fetchAll(PDO::FETCH_COLUMN));

        $applied = [];
        foreach ($this->pending($done) as $version => $file) {
            foreach (self::statements((string) file_get_contents($file)) as $n => $sql) {
                try {
                    $this->pdo->exec($sql);
                } catch (\PDOException $e) {
                    throw new RuntimeException(sprintf('Migration %s, statement %d failed: %s', $version, $n + 1, $e->getMessage()), 0, $e);
                }
            }
            $this->pdo->prepare('INSERT INTO schema_migrations (version) VALUES (?)')->execute([$version]);
            $applied[] = $version;
            $log && $log("applied $version");
        }

        return $applied;
    }

    /**
     * @param array<string, int> $done
     *
     * @return array<string, string> version => file
     */
    private function pending(array $done): array
    {
        $files = glob(rtrim($this->dir, '/\\') . '/*.sql') ?: [];
        sort($files, SORT_STRING);
        $pending = [];
        foreach ($files as $file) {
            $version = basename($file, '.sql');
            if (!isset($done[$version])) {
                $pending[$version] = $file;
            }
        }

        return $pending;
    }

    /**
     * Splits a migration into statements: full-line "--" comments are dropped and a
     * statement ends with ";" at the end of a line. Keep migrations within that form.
     *
     * @return list<string>
     */
    public static function statements(string $sql): array
    {
        $lines = array_filter(
            preg_split('/\R/', $sql) ?: [],
            static fn (string $line): bool => !preg_match('/^\s*--/', $line)
        );
        $parts = preg_split('/;\s*$/m', implode("\n", $lines)) ?: [];

        return array_values(array_filter(array_map('trim', $parts), static fn (string $s): bool => $s !== ''));
    }
}
