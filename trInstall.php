<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Europe/Paris');

$configFile = __DIR__ . '/config/config.php';
$bAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
    && strtolower((string) $_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if (file_exists($configFile)) {
    if ($bAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'redirect' => '/login.php',
        ]);
        exit;
    }

    header('Location: /login.php');
    exit;
}

function installError(string $sMessage, bool $bAjax): never
{
    if ($bAjax) {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => $sMessage,
        ]);
        exit;
    }

    $_SESSION['sInstallError'] = $sMessage;
    header('Location: /install.php');
    exit;
}

function ensureWritableDirectory(string $sPath, string $sLabel): void
{
    if (!is_dir($sPath)) {
        if (!mkdir($sPath, 0775, true) && !is_dir($sPath)) {
            throw new RuntimeException('Impossible de créer ' . $sLabel . ' : ' . $sPath);
        }
    }

    if (!is_writable($sPath)) {
        throw new RuntimeException($sLabel . ' non accessible en écriture : ' . $sPath);
    }
}

try {
    $sUser = trim((string) ($_POST['sUser'] ?? ''));
    $sPassword = (string) ($_POST['sPassword'] ?? '');
    $sPasswordConfirm = (string) ($_POST['sPasswordConfirm'] ?? '');

    $sDbHost = trim((string) ($_POST['sDbHost'] ?? ''));
    $sDbName = trim((string) ($_POST['sDbName'] ?? ''));
    $sDbUser = trim((string) ($_POST['sDbUser'] ?? ''));
    $sDbPassword = (string) ($_POST['sDbPassword'] ?? '');

    $sStoragePath = rtrim(trim((string) ($_POST['sStoragePath'] ?? '')), '/');
    $sStorageCachePath = rtrim(trim((string) ($_POST['sStorageCachePath'] ?? '')), '/');

    if ($sUser === '') {
        throw new RuntimeException('Le login est obligatoire');
    }

    if ($sPassword === '') {
        throw new RuntimeException('Le mot de passe est obligatoire');
    }

    if ($sPassword !== $sPasswordConfirm) {
        throw new RuntimeException('La confirmation du mot de passe ne correspond pas');
    }

    if ($sDbHost === '' || $sDbName === '' || $sDbUser === '') {
        throw new RuntimeException('La configuration MariaDB est incomplète');
    }

    if ($sStoragePath === '' || $sStorageCachePath === '') {
        throw new RuntimeException('Les chemins de stockage sont obligatoires');
    }

    ensureWritableDirectory($sStoragePath, 'le dossier de stockage');
    ensureWritableDirectory($sStorageCachePath, 'le dossier de cache');

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

    $sSqlFile = __DIR__ . '/sql/install.sql';

    if (!file_exists($sSqlFile)) {
        throw new RuntimeException('Fichier SQL d’installation absent');
    }

    $sSql = trim((string) file_get_contents($sSqlFile));
    $aStatements = preg_split('/;\s*(?:\r?\n|$)/', $sSql);

    foreach ($aStatements as $sStatement) {
        $sStatement = trim($sStatement);

        if ($sStatement !== '') {
            $WM_ADMIN_conn->exec($sStatement);
        }
    }

    $nUserCount = (int) $WM_ADMIN_conn->query(
        'SELECT COUNT(*) FROM T_USER WHERE USR_DT_SUPPRESSION IS NULL'
    )->fetchColumn();

    if ($nUserCount > 0) {
        throw new RuntimeException('La base contient déjà un utilisateur actif. Installation interrompue.');
    }

    $WM_ADMIN_conn->beginTransaction();

    $oUserInsert = $WM_ADMIN_conn->prepare(
        'INSERT INTO T_USER (
            USR_CH_LOGIN,
            USR_CH_PASSWORD,
            USR_DT_CREATION,
            USR_CH_CREATION
        ) VALUES (
            :USR_CH_LOGIN,
            :USR_CH_PASSWORD,
            NOW(),
            :USR_CH_CREATION
        )'
    );

    $oUserInsert->execute([
        'USR_CH_LOGIN' => $sUser,
        'USR_CH_PASSWORD' => password_hash($sPassword, PASSWORD_DEFAULT),
        'USR_CH_CREATION' => $sUser,
    ]);

    $aUrlTypes = [
        'Production',
        'Archive',
        'Repository',
        'App Store',
        'Play Store',
        'Documentation',
    ];

    $oUrlTypeInsert = $WM_ADMIN_conn->prepare(
        'INSERT INTO T_URLTYPE (
            UTY_CH_LABEL,
            UTY_DT_CREATION,
            UTY_CH_CREATION
        ) VALUES (
            :UTY_CH_LABEL,
            NOW(),
            :UTY_CH_CREATION
        )'
    );

    foreach ($aUrlTypes as $sLabel) {
        $oUrlTypeInsert->execute([
            'UTY_CH_LABEL' => $sLabel,
            'UTY_CH_CREATION' => $sUser,
        ]);
    }

    $aFileTypes = [
        'Screenshot',
        'Image',
        'Document',
        'Archive',
        'Autre',
    ];

    $oFileTypeInsert = $WM_ADMIN_conn->prepare(
        'INSERT INTO T_FILETYPE (
            FTY_CH_LABEL,
            FTY_DT_CREATION,
            FTY_CH_CREATION
        ) VALUES (
            :FTY_CH_LABEL,
            NOW(),
            :FTY_CH_CREATION
        )'
    );

    foreach ($aFileTypes as $sLabel) {
        $oFileTypeInsert->execute([
            'FTY_CH_LABEL' => $sLabel,
            'FTY_CH_CREATION' => $sUser,
        ]);
    }

    $aTagCategories = [
        ['Client', 'primary', 10],
        ['Type', 'info', 20],
        ['Technologie', 'success', 30],
        ['Plateforme', 'warning', 40],
        ['État', 'secondary', 50],
    ];

    $oTagCategoryInsert = $WM_ADMIN_conn->prepare(
        'INSERT INTO T_TAGCATEGORY (
            TCA_CH_LABEL,
            TCA_CH_COLOR,
            TCA_N_ORDER,
            TCA_DT_CREATION,
            TCA_CH_CREATION
        ) VALUES (
            :TCA_CH_LABEL,
            :TCA_CH_COLOR,
            :TCA_N_ORDER,
            NOW(),
            :TCA_CH_CREATION
        )'
    );

    foreach ($aTagCategories as $aCategory) {
        $oTagCategoryInsert->execute([
            'TCA_CH_LABEL' => $aCategory[0],
            'TCA_CH_COLOR' => $aCategory[1],
            'TCA_N_ORDER' => $aCategory[2],
            'TCA_CH_CREATION' => $sUser,
        ]);
    }

    $WM_ADMIN_conn->commit();

    $sConfigContent = "<?php\n\n"
        . '$sDbHost = ' . var_export($sDbHost, true) . ";\n"
        . '$sDbName = ' . var_export($sDbName, true) . ";\n"
        . '$sDbUser = ' . var_export($sDbUser, true) . ";\n"
        . '$sDbPassword = ' . var_export($sDbPassword, true) . ";\n\n"
        . '$sStoragePath = ' . var_export($sStoragePath, true) . ";\n"
        . '$sStorageCachePath = ' . var_export($sStorageCachePath, true) . ";\n";

    $sTempConfigFile = __DIR__ . '/config/config.php.tmp';

    if (file_put_contents($sTempConfigFile, $sConfigContent, LOCK_EX) === false) {
        throw new RuntimeException('Impossible d’écrire le fichier de configuration');
    }

    if (!rename($sTempConfigFile, $configFile)) {
        @unlink($sTempConfigFile);
        throw new RuntimeException('Impossible d’activer le fichier de configuration');
    }

    session_regenerate_id(true);
    $_SESSION['logged'] = '1';
    $_SESSION['sUser'] = $sUser;

    if ($bAjax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'redirect' => '/index.php',
        ]);
        exit;
    }

    header('Location: /index.php');
    exit;
} catch (Throwable $e) {
    if (isset($WM_ADMIN_conn) && $WM_ADMIN_conn instanceof PDO && $WM_ADMIN_conn->inTransaction()) {
        $WM_ADMIN_conn->rollBack();
    }

    installError($e->getMessage(), $bAjax);
}
