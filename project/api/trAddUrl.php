<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRO_N_ID = decryptId($_POST['PRO_N_ID'] ?? '', $sEncryptKey);
    $UTY_N_ID = decryptId($_POST['UTY_N_ID'] ?? '', $sEncryptKey);
    $sUrl = trim((string) ($_POST['PRU_CH_URL'] ?? ''));
    $sLabel = trim((string) ($_POST['PRU_CH_LABEL'] ?? ''));
    $sYear = trim((string) ($_POST['PRU_N_YEAR'] ?? ''));

    if ($PRO_N_ID <= 0 || $UTY_N_ID <= 0) {
        throw new RuntimeException('Projet ou type d’URL invalide');
    }

    if ($sUrl === '' || filter_var($sUrl, FILTER_VALIDATE_URL) === false) {
        throw new RuntimeException('URL invalide');
    }

    $nYear = null;

    if ($sYear !== '') {
        if (!ctype_digit($sYear) || (int) $sYear < 1900 || (int) $sYear > 2100) {
            throw new RuntimeException('Année invalide');
        }
        $nYear = (int) $sYear;
    }

    $oProject = $WM_ADMIN_conn->prepare(
        'SELECT T_PROJECT.PRO_N_ID
         FROM T_PROJECT
         WHERE T_PROJECT.PRO_N_ID = :PRO_N_ID
           AND T_PROJECT.PRO_DT_SUPPRESSION IS NULL'
    );
    $oProject->execute(['PRO_N_ID' => $PRO_N_ID]);

    if (!$oProject->fetchColumn()) {
        throw new RuntimeException('Projet introuvable');
    }

    $oType = $WM_ADMIN_conn->prepare(
        'SELECT T_URLTYPE.UTY_N_ID
         FROM T_URLTYPE
         WHERE T_URLTYPE.UTY_N_ID = :UTY_N_ID
           AND T_URLTYPE.UTY_DT_SUPPRESSION IS NULL'
    );
    $oType->execute(['UTY_N_ID' => $UTY_N_ID]);

    if (!$oType->fetchColumn()) {
        throw new RuntimeException('Type d’URL introuvable');
    }

    $oOrder = $WM_ADMIN_conn->prepare(
        'SELECT COALESCE(MAX(T_PROJECTURL.PRU_N_ORDER), 0) + 10
         FROM T_PROJECTURL
         WHERE T_PROJECTURL.PRO_N_ID = :PRO_N_ID
           AND T_PROJECTURL.PRU_DT_SUPPRESSION IS NULL'
    );
    $oOrder->execute(['PRO_N_ID' => $PRO_N_ID]);

    $oInsert = $WM_ADMIN_conn->prepare(
        'INSERT INTO T_PROJECTURL (
            PRO_N_ID,
            UTY_N_ID,
            PRU_CH_URL,
            PRU_CH_LABEL,
            PRU_N_YEAR,
            PRU_N_ORDER,
            PRU_DT_CREATION,
            PRU_CH_CREATION
        ) VALUES (
            :PRO_N_ID,
            :UTY_N_ID,
            :PRU_CH_URL,
            :PRU_CH_LABEL,
            :PRU_N_YEAR,
            :PRU_N_ORDER,
            NOW(),
            :PRU_CH_CREATION
        )'
    );

    $oInsert->execute([
        'PRO_N_ID' => $PRO_N_ID,
        'UTY_N_ID' => $UTY_N_ID,
        'PRU_CH_URL' => $sUrl,
        'PRU_CH_LABEL' => $sLabel === '' ? null : $sLabel,
        'PRU_N_YEAR' => $nYear,
        'PRU_N_ORDER' => (int) $oOrder->fetchColumn(),
        'PRU_CH_CREATION' => sSignature(),
    ]);

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
