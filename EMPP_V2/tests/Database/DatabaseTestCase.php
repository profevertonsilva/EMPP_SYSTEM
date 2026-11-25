<?php

declare(strict_types=1);

namespace Empp\Tests\Database;

use Empp\Config;
use Empp\Database\Connection;
use Empp\Database\Migrator;
use PDO;
use PHPUnit\Framework\TestCase;

/**
 * Tests against a real MySQL database (TEST_DB_NAME in .env), dropped and rebuilt
 * from the migrations. Skipped when .env is missing.
 */
abstract class DatabaseTestCase extends TestCase
{
    protected static ?Config $config = null;

    protected PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = self::freshDatabase('TEST_DB_NAME');
        (new Migrator($this->pdo, dirname(__DIR__, 2) . '/database/migrations'))->migrate();
    }

    /** Drops and recreates the database named by $variable; refuses names not ending in _test */
    protected static function freshDatabase(string $variable): PDO
    {
        $db = self::settings($variable);
        Connection::dropDatabase($db);
        Connection::createDatabase($db);

        return Connection::open($db);
    }

    /** @return array{host: string, port: int, name: string, user: string, pass: string} */
    protected static function settings(string $variable): array
    {
        if (self::$config === null) {
            if (!is_file(dirname(__DIR__, 2) . '/.env')) {
                self::markTestSkipped('No .env: database tests skipped');
            }
            self::$config = Config::load();
        }
        $name = self::$config->get($variable);
        if ($name === null) {
            self::markTestSkipped("$variable is not set: database tests skipped");
        }
        if (!str_ends_with($name, '_test')) {
            self::fail("$variable must end in _test: the tests drop that database");
        }

        return ['name' => $name] + self::$config->database();
    }

    /** A user and a membrane to hang rows on; returns the membrane id */
    protected function membrane(?float $thicknessUm = 12.0): int
    {
        $this->pdo->exec("INSERT INTO users (email, password_hash) VALUES ('t" . uniqid() . "@example.test', 'x')");
        $userId = (int) $this->pdo->lastInsertId();
        $this->pdo->prepare('INSERT INTO membranes (user_id, name, thickness_um) VALUES (?, ?, ?)')
            ->execute([$userId, 'Membrane A', $thicknessUm]);

        return (int) $this->pdo->lastInsertId();
    }

    protected function image(int $membraneId, ?float $porosity, ?float $diameterUm, int $order = 0): int
    {
        $this->pdo->prepare(
            'INSERT INTO membrane_images (membrane_id, file_name, porosity, fiber_diameter_um, analysis_status, sort_order) VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([$membraneId, uniqid() . '.jpg', $porosity, $diameterUm, 'done', $order]);

        return (int) $this->pdo->lastInsertId();
    }
}
