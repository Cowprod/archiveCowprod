<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Europe/Paris');

require_once __DIR__ . '/api/inc_function.php';

$configFile = __DIR__ . '/config/config.php';

if (!file_exists($configFile)) {
    header('Location: /install.php');
    exit;
}

require $configFile;

if (!isset($sEncryptKey) || trim((string) $sEncryptKey) === '') {
    throw new RuntimeException('Clé de chiffrement absente de config/config.php');
}
