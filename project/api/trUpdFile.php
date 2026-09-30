<?php

require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRF_N_ID = decryptId($_POST['PRF_N_ID'] ?? '', $sEncryptKey);
    $sField = trim((string) ($_POST['sField'] ?? ''));
    $sValue = trim((string) ($_POST['sValue'] ?? ''));

    $aAllowed = [
        'FTY_N_ID' => 'type',
        'PRF_CH_LABEL' => 'text-null',
        'PRF_N_YEAR' => 'year-null',
    ];

    if (!isset($aAllowed[$sField])) {
        throw new RuntimeException('Fichier invalide');
    }

    if ($aAllowed[$sField] === 'type') {
        $FTY_N_ID = decryptId($sValue, $sEncryptKey);
        archiveAssertFileType($WM_ADMIN_conn, $FTY_N_ID);
        $sPreparedValue = prepNum2Update($FTY_N_ID);
    } elseif ($aAllowed[$sField] === 'year-null') {
        $nYear = archiveValidateYear($sValue);
        $sPreparedValue = $nYear === null ? 'null' : prepNum2Update($nYear);
    } else {
        $sPreparedValue = $sValue === '' ? 'null' : prepString2Update($sValue);
    }

    $WM_ADMIN_conn->beginTransaction();
    historiseTable('T_PROJECTFILE', 'PRF', $PRF_N_ID, $WM_ADMIN_conn);

    $sSql = 'UPDATE T_PROJECTFILE
        SET ' . $sField . '=' . $sPreparedValue . '
        WHERE PRF_N_ID=' . prepNum2Update($PRF_N_ID) . '
          AND PRF_DT_SUPPRESSION IS NULL';

    if ($WM_ADMIN_conn->exec($sSql) !== 1) {
        throw new RuntimeException('Fichier introuvable');
    }

    $WM_ADMIN_conn->commit();
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    if ($WM_ADMIN_conn->inTransaction()) {
        $WM_ADMIN_conn->rollBack();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
