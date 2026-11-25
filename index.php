<?php
require_once 'vendor/autoload.php';

use Dotenv\Dotenv;
use Dotenv\Exception\ExceptionInterface as DotenvException;

session_start();

$dotenv = Dotenv::createImmutable(__DIR__);

// Every variable the code reads (see .env.example). A missing or malformed one
// stops here with a message naming it, instead of warnings spread over the pages.
try {
    $dotenv->load();

    // Site and asset URLs: absolute, ending with a slash (the code appends paths)
    $urls = ['BASE_URL', 'BASE_ASSETS', 'BASE_CSS', 'BASE_JS', 'BASE_VENDOR', 'BASE_IMG'];
    $dotenv->required($urls)->notEmpty();
    $dotenv->required($urls)->allowedRegexValues('#^https?://[^\s]+/$#');

    $dotenv->required(['SITE_TITLE_HOME', 'DB_HOST', 'DB_NAME', 'DB_USER'])->notEmpty();

    // Must exist but may be empty: a local database without password, or no SMTP
    // configured (then sign-up and password reset report that the email failed)
    $dotenv->required(['DB_PASS', 'MAIL_HOST', 'MAIL_USER', 'MAIL_PASS', 'MAIL_FROM', 'MAIL_FROM_NAME']);
    $dotenv->required('MAIL_PORT')->allowedRegexValues('/^\d*$/');
} catch (DotenvException $e) {
    // The message names variables, never their values
    error_log('EMPP configuration: ' . $e->getMessage());
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    echo "EMPP configuration error\n\n";
    echo $e instanceof \Dotenv\Exception\InvalidPathException
        ? "The .env file was not found. Copy .env.example to .env and fill it in.\n"
        : $e->getMessage() . "\n\nCheck .env against .env.example.\n";
    exit;
}

$route = new \App\Route();
