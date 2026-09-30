<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $FTY_N_ID = decryptId($_POST['FTY_N_ID'] ?? '', $sEncryptKey);
    $sLabel = trim((string) ($_POST['FTY_CH_LABEL'] ?? ''));

    if ($sLabel === '') {
        throw new RuntimeException('Type invalide');
    }

    $WM_ADMIN_conn->beginTransaction();
    historiseTable('T_FILETYPE', 'FTY', $FTY_N_ID, $WM_ADMIN_conn);

    $sSql = 'UPDATE T_FILETYPE
        SET FTY_CH_LABEL=' . prepString2Update($sLabel) . '
        WHERE FTY_N_ID=' . prepNum2Update($FTY_N_ID) . '
          AND FTY_DT_SUPPRESSION IS NULL';

    if ($WM_ADMIN_conn->exec($sSql) !== 1) {
        throw new RuntimeException('Type introuvable');
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
