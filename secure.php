<?php

require_once __DIR__ . '/inc_connexion.php';

if (!isset($_SESSION['logged']) || $_SESSION['logged'] !== '1') {
    $_SESSION['sAskedPage'] = $_SERVER['REQUEST_URI'] ?? '/';
    header('Location: /login.php');
    exit;
}

if (!isset($_SESSION['sUser']) || trim((string) $_SESSION['sUser']) === '') {
    session_unset();
    session_destroy();
    header('Location: /login.php');
    exit;
}
