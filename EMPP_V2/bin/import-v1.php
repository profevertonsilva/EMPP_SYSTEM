<?php

declare(strict_types=1);

/*
 * Copies the v1 data (V1_DB_*) into the v2 database (DB_*), and the uploaded
 * files from V1_PATH into storage/. Safe to run again: v1 rows are matched by id.
 *
 *   php bin/import-v1.php
 */

use Empp\Config;
use Empp\Database\Connection;
use Empp\Import\V1Importer;

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require dirname(__DIR__) . '/vendor/autoload.php';

try {
    $config = Config::load();
    $v1Db = $config->database('V1_');
    $v2Db = $config->database();
    if ($v1Db['name'] === $v2Db['name'] && $v1Db['host'] === $v2Db['host']) {
        throw new RuntimeException('V1_DB_NAME and DB_NAME point to the same database');
    }

    $v1Root = $config->get('V1_PATH');
    if ($v1Root !== null && !is_dir($v1Root)) {
        throw new RuntimeException("V1_PATH is not a folder: $v1Root");
    }

    $report = (new V1Importer(
        Connection::open($v1Db),
        Connection::open($v2Db),
        $v1Root,
        dirname(__DIR__) . '/storage',
    ))->run();

    echo "Imported {$v1Db['name']} into {$v2Db['name']}\n";
    foreach ($report['stats'] as $what => $n) {
        printf("  %-28s %d\n", $what, $n);
    }
    if ($report['warnings'] !== []) {
        echo "\nWarnings:\n";
        foreach ($report['warnings'] as $warning) {
            echo "  - $warning\n";
        }
    }
} catch (Throwable $e) {
    fwrite(STDERR, 'import-v1: ' . $e->getMessage() . "\n");
    exit(1);
}
