<?php

require_once __DIR__ . '/inc_connexion.php';

$sUser = trim((string) ($_POST['sUser'] ?? ''));
$sPassword = (string) ($_POST['sPassword'] ?? '');

if ($sUser === '' || $sPassword === '') {
    $_SESSION['sLoginError'] = 'Identifiant ou mot de passe incorrect';
    header('Location: /login.php');
    exit;
}

$aUsers = oRs(
    '',
    __DIR__ . '/login.sql',
    'USR_CH_LOGIN=' . urlencode(prepString2Update($sUser)),
    0,
    '',
    $WM_ADMIN_conn
);

$aUser = $aUsers[0] ?? false;

if (!$aUser || !password_verify($sPassword, (string) $aUser['USR_CH_PASSWORD'])) {
    $_SESSION['sLoginError'] = 'Identifiant ou mot de passe incorrect';
    header('Location: /login.php');
    exit;
}

session_regenerate_id(true);

$_SESSION['logged'] = '1';
$_SESSION['sUser'] = $sUser;
$_SESSION['USR_N_ID'] = (int) $aUser['USR_N_ID'];

$sAskedPage = $_SESSION['sAskedPage'] ?? '/index.php';
unset($_SESSION['sAskedPage']);

header('Location: ' . $sAskedPage);
exit;
