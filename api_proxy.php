<?php
/**
 * Proxy same-origin para as APIs de analise de nanofibras.
 *
 * O navegador chama /api_proxy.php?target=haralick|porosity no proprio dominio,
 * e o PHP repassa a requisicao server-to-server. Isso elimina o bloqueio de CORS
 * (o browser nunca fala com rapzap.com.br) e tira o endereco das APIs do JS publico.
 *
 * Servido direto pelo Apache: a regra do .htaccess so manda para o index.php
 * quando o arquivo requisitado nao existe (RewriteCond REQUEST_FILENAME !-f).
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

// Proxy aberto vira ferramenta de SSRF: exige sessao autenticada.
if (empty($_SESSION['log_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Nao autenticado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Use POST.']);
    exit;
}

// Allowlist fixa: o cliente escolhe um apelido, nunca uma URL.
$targets = [
    'haralick' => 'https://www.rapzap.com.br/api/haralick',
    'porosity' => 'https://www.rapzap.com.br/api/porosity',
];

$target = $_GET['target'] ?? '';
if (!isset($targets[$target])) {
    http_response_code(400);
    echo json_encode(['error' => 'Destino invalido.']);
    exit;
}

$payload = file_get_contents('php://input');
if ($payload === false || $payload === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Corpo da requisicao vazio.']);
    exit;
}
if (json_decode($payload) === null && json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON invalido.']);
    exit;
}

$ch = curl_init($targets[$target]);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $payload,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Accept: application/json'],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_TIMEOUT => 120,      // extracao de Haralick em imagem grande demora.
    CURLOPT_FOLLOWLOCATION => false,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
]);

$body = curl_exec($ch);
$status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($body === false) {
    // Detalhe do cURL vai para o log; o cliente recebe mensagem generica.
    error_log("api_proxy [$target]: $curlErr");
    http_response_code(502);
    echo json_encode(['error' => 'Falha ao contatar o servico de analise.']);
    exit;
}

http_response_code($status ?: 502);
echo $body;
