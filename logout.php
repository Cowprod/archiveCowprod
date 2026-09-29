<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION = [];

if (ini_get('session.use_cookies')) {
    $aParams = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $aParams['path'],
        $aParams['domain'],
        $aParams['secure'],
        $aParams['httponly']
    );
}

session_destroy();

header('Location: /login.php');
exit;
