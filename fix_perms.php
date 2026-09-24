<?php
/**
 * Correcao de permissoes pos-upload (host sem SSH).
 * Uso: subir na raiz da aplicacao, acessar
 *      https://SEU_DOMINIO/empp/fix_perms.php?token=TROQUE_ESTE_TOKEN
 * APAGAR do servidor logo apos rodar.
 */

$TOKEN = 'TROQUE_ESTE_TOKEN';

if (!isset($_GET['token']) || !hash_equals($TOKEN, $_GET['token'])) {
    http_response_code(403);
    exit('forbidden');
}

header('Content-Type: text/plain; charset=utf-8');
set_time_limit(0);

$root = __DIR__;
$dirs = 0;
$files = 0;
$failed = [];

$it = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

foreach ($it as $path => $info) {
    $mode = $info->isDir() ? 0755 : 0644;
    if (@chmod($path, $mode)) {
        $info->isDir() ? $dirs++ : $files++;
    } else {
        $failed[] = $path;
    }
}

@chmod($root, 0755);

echo "dirs ok:  $dirs\n";
echo "files ok: $files\n";
echo "falhas:   " . count($failed) . "\n\n";

foreach (array_slice($failed, 0, 50) as $f) {
    echo "FALHOU: $f\n";
}

// Confere o arquivo que estava estourando o 500.
$alvo = $root . '/vendor/symfony/deprecation-contracts/function.php';
echo "\n--- alvo ---\n";
echo "existe:  " . (file_exists($alvo) ? 'sim' : 'NAO') . "\n";
if (file_exists($alvo)) {
    echo "tamanho: " . filesize($alvo) . " bytes (esperado 1014)\n";
    echo "perm:    " . substr(sprintf('%o', fileperms($alvo)), -4) . "\n";
    echo "legivel: " . (is_readable($alvo) ? 'sim' : 'NAO') . "\n";
}

echo "\nAPAGUE ESTE ARQUIVO DO SERVIDOR AGORA.\n";
