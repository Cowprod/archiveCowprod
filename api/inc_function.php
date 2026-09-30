<?php

require_once __DIR__ . '/inc_api_bdd_mysql.php';
require_once __DIR__ . '/inc_api_text.php';
require_once __DIR__ . '/inc_file.php';

function sSignature(): string
{
    if (!isset($_SESSION['sUser']) || trim((string) $_SESSION['sUser']) === '') {
        throw new RuntimeException('Utilisateur absent de la session');
    }

    return trim((string) $_SESSION['sUser']);
}
