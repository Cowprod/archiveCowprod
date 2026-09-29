<?php

require_once __DIR__ . '/global.php';

if (
    !isset($sDbHost, $sDbName, $sDbUser, $sDbPassword)
    || trim((string) $sDbHost) === ''
    || trim((string) $sDbName) === ''
    || trim((string) $sDbUser) === ''
) {
    throw new RuntimeException('Configuration MariaDB incomplète');
}

$WM_ADMIN_conn = new PDO(
    'mysql:host=' . $sDbHost . ';dbname=' . $sDbName . ';charset=utf8mb4',
    $sDbUser,
    $sDbPassword,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);
