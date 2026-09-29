<?php

require_once __DIR__ . '/inc_connexion.php';

$sUser = trim((string) ($_POST['sUser'] ?? ''));
$sPassword = (string) ($_POST['sPassword'] ?? '');

if ($sUser === '' || $sPassword === '') {
    $_SESSION['sLoginError'] = 'Identifiant ou mot de passe incorrect';
    header('Location: /login.php');
    exit;
}

$oUser = $WM_ADMIN_conn->prepare(
    'SELECT
        USR_N_ID,
        USR_CH_LOGIN,
        USR_CH_PASSWORD
     FROM T_USER
     WHERE USR_DT_SUPPRESSION IS NULL
       AND LOWER(USR_CH_LOGIN) = LOWER(:USR_CH_LOGIN)
     ORDER BY USR_N_ID DESC
     LIMIT 1'
);

$oUser->execute([
    'USR_CH_LOGIN' => $sUser,
]);

$aUser = $oUser->fetch();

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
