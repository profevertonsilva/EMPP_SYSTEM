<?php

declare(strict_types=1);

/*
 * Applies pending migrations to the v2 database.
 *
 *   php bin/migrate.php            the database in DB_NAME
 *   php bin/migrate.php --create   creates it first when missing
 */

use Empp\Config;
use Empp\Database\Connection;
use Empp\Database\Migrator;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/vendor/autoload.php';

try {
    $config = Config::load();
    $db = $config->database();
    if (in_array('--create', $argv, true)) {
        Connection::createDatabase($db);
    }
    $applied = (new Migrator(Connection::open($db), dirname(__DIR__) . '/database/migrations'))
        ->migrate(static fn (string $line) => print("$line\n"));
    echo $applied === [] ? "{$db['name']}: up to date\n" : "{$db['name']}: " . count($applied) . " migration(s) applied\n";
} catch (Throwable $e) {
    fwrite(STDERR, 'migrate: ' . $e->getMessage() . "\n");
    exit(1);
}
