<?php
/**
 * Diagnostico de deploy (host sem SSH, LiteSpeed/lsapi).
 *
 * COMO USAR
 * 1. Gere um token: qualquer string aleatoria de 32+ chars.
 * 2. Crie o arquivo .diag_token na raiz da aplicacao contendo SO o token.
 *    Ele fica fora do alcance HTTP pelo bloco no .htaccess (arquivos .diag*).
 * 3. Acesse https://SEU_DOMINIO/diag.php?token=SEU_TOKEN
 * 4. O script se autodestroi apos a primeira execucao bem-sucedida.
 *
 * Nao imprime valores de configuracao, credenciais nem stack traces.
 */

$tokenFile = __DIR__ . '/.diag_token';
$expected = is_readable($tokenFile) ? trim((string) file_get_contents($tokenFile)) : '';

if (strlen($expected) < 32) {
    http_response_code(403);
    exit('forbidden');
}
if (!isset($_GET['token']) || !is_string($_GET['token']) || !hash_equals($expected, $_GET['token'])) {
    http_response_code(403);
    exit('forbidden');
}

// Erros vao para o error_log do servidor, nunca para a resposta HTTP.
ini_set('display_errors', '0');
ini_set('display_startup_errors', '0');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

$root = __DIR__;
echo "php: " . PHP_VERSION . " (" . PHP_SAPI . ")\n\n";

echo "=== extensoes ===\n";
foreach (['pdo_mysql', 'mbstring', 'gd', 'zip', 'curl', 'json', 'dom', 'fileinfo', 'iconv'] as $ext) {
    printf("%-12s %s\n", $ext, extension_loaded($ext) ? 'ok' : 'FALTANDO');
}

echo "\n=== arquivos-chave ===\n";
$alvos = [
    '.env',
    'index.php',
    'vendor/autoload.php',
    'vendor/composer/autoload_real.php',
    'vendor/composer/installed.php',
    'vendor/symfony/deprecation-contracts/function.php',
    'vendor/FW/Init/Boostrap.php',
    'App/Route.php',
];
foreach ($alvos as $rel) {
    $p = $root . '/' . $rel;
    if (!file_exists($p)) {
        printf("%-50s AUSENTE\n", $rel);
        continue;
    }
    printf(
        "%-50s %-6s %6d bytes  %s\n",
        $rel,
        substr(sprintf('%o', fileperms($p)), -4),
        filesize($p),
        is_readable($p) ? 'legivel' : 'SEM LEITURA'
    );
}

echo "\n=== varredura vendor (ilegiveis / vazios) ===\n";
$ruins = 0;
$total = 0;
$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root . '/vendor', FilesystemIterator::SKIP_DOTS)
);
foreach ($it as $p => $info) {
    if (!$info->isFile() || substr($p, -4) !== '.php') {
        continue;
    }
    $total++;
    if (!is_readable($p) || $info->getSize() === 0) {
        $ruins++;
        if ($ruins <= 40) {
            echo "RUIM: " . str_replace($root . '/', '', $p)
                . " (" . substr(sprintf('%o', fileperms($p)), -4)
                . ", " . $info->getSize() . " bytes)\n";
        }
    }
}
echo "php em vendor: $total | problematicos: $ruins\n";

// Mensagens de excecao vao so para o error_log; a resposta HTTP recebe apenas o veredito.
$reportar = static function (Throwable $e, string $etapa): void {
    error_log("diag.php [$etapa]: " . get_class($e) . ': ' . $e->getMessage());
    echo "FALHOU (detalhe no error_log do servidor)\n";
};

echo "\n=== autoload ===\n";
$autoloadOk = false;
try {
    require_once $root . '/vendor/autoload.php';
    $autoloadOk = true;
    echo "autoload ok\n";
    echo "Dotenv:    " . (class_exists('Dotenv\Dotenv') ? 'ok' : 'AUSENTE') . "\n";
    echo "App\\Route: " . (class_exists('App\Route') ? 'ok' : 'AUSENTE') . "\n";
} catch (Throwable $e) {
    $reportar($e, 'autoload');
}

echo "\n=== .env ===\n";
$envOk = false;
if ($autoloadOk && class_exists('Dotenv\Dotenv')) {
    try {
        Dotenv\Dotenv::createImmutable($root)->load();
        $envOk = true;
        echo "carregado ok\n";
        // Só presenca, nunca o valor.
        foreach (['BASE_URL', 'DB_HOST', 'DB_USER', 'DB_NAME', 'DB_PASS'] as $k) {
            printf("%-10s %s\n", $k, isset($_ENV[$k]) && $_ENV[$k] !== '' ? 'definido' : 'AUSENTE/VAZIO');
        }
    } catch (Throwable $e) {
        $reportar($e, 'dotenv');
    }
} else {
    echo "pulado (autoload falhou)\n";
}

echo "\n=== conexao mysql ===\n";
if ($envOk) {
    try {
        new PDO(
            "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_NAME']}",
            $_ENV['DB_USER'],
            $_ENV['DB_PASS'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        echo "conectou ok\n";
    } catch (Throwable $e) {
        $reportar($e, 'mysql');
    }
} else {
    echo "pulado (.env nao carregou)\n";
}

// Autodestruicao: uma execucao, um resultado.
if (@unlink(__FILE__)) {
    echo "\ndiag.php removido do servidor automaticamente.\n";
} else {
    echo "\nATENCAO: remocao automatica falhou. APAGUE diag.php e .diag_token MANUALMENTE AGORA.\n";
}
echo "Apague tambem o info.php.\n";
