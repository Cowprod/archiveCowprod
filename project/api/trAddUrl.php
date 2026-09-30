<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRO_N_ID = decryptId($_POST['PRO_N_ID'] ?? '', $sEncryptKey);
    $UTY_N_ID = decryptId($_POST['UTY_N_ID'] ?? '', $sEncryptKey);
    $sUrl = trim((string) ($_POST['PRU_CH_URL'] ?? ''));
    $sLabel = trim((string) ($_POST['PRU_CH_LABEL'] ?? ''));
    $sYear = trim((string) ($_POST['PRU_N_YEAR'] ?? ''));

    archiveAssertProject($WM_ADMIN_conn, $PRO_N_ID);

    if ((int) getfield(
        'count(*)',
        'T_URLTYPE',
        'WHERE UTY_N_ID=' . prepNum2Update($UTY_N_ID) . ' AND UTY_DT_SUPPRESSION IS NULL',
        $WM_ADMIN_conn
    ) !== 1) {
        throw new RuntimeException('Type d’URL introuvable');
    }

    if ($sUrl === '' || filter_var($sUrl, FILTER_VALIDATE_URL) === false) {
        throw new RuntimeException('URL invalide');
    }

    $nYear = $sYear === '' ? null : (int) $sYear;

    if ($sYear !== '' && (!ctype_digit($sYear) || $nYear < 1900 || $nYear > 2100)) {
        throw new RuntimeException('Année invalide');
    }

    $nOrder = (int) getfield(
        'COALESCE(MAX(PRU_N_ORDER),0)+10',
        'T_PROJECTURL',
        'WHERE PRO_N_ID=' . prepNum2Update($PRO_N_ID) . ' AND PRU_DT_SUPPRESSION IS NULL',
        $WM_ADMIN_conn
    );

    $sSql = 'INSERT INTO T_PROJECTURL (
        PRO_N_ID,UTY_N_ID,PRU_CH_URL,PRU_CH_LABEL,PRU_N_YEAR,PRU_N_ORDER,PRU_DT_CREATION,PRU_CH_CREATION
    ) VALUES (
        ' . prepNum2Update($PRO_N_ID) . ',
        ' . prepNum2Update($UTY_N_ID) . ',
        ' . prepString2Update($sUrl) . ',
        ' . ($sLabel === '' ? 'null' : prepString2Update($sLabel)) . ',
        ' . ($nYear === null ? 'null' : prepNum2Update($nYear)) . ',
        ' . prepNum2Update($nOrder) . ',
        NOW(),
        ' . prepString2Update(sSignature()) . '
    )';

    $WM_ADMIN_conn->exec($sSql);
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
