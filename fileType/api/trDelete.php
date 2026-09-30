<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $FTY_N_ID = decryptId($_POST['FTY_N_ID'] ?? '', $sEncryptKey);

    $nCount = (int) getfield(
        'count(*)',
        'T_PROJECTFILE',
        'WHERE FTY_N_ID=' . prepNum2Update($FTY_N_ID) . ' AND PRF_DT_SUPPRESSION IS NULL',
        $WM_ADMIN_conn
    );

    if ($nCount > 0) {
        throw new RuntimeException('Ce type est utilisé par un ou plusieurs fichiers');
    }

    $WM_ADMIN_conn->beginTransaction();

    $sSql = 'UPDATE T_FILETYPE
        SET FTY_DT_SUPPRESSION=NOW(),
            FTY_CH_SUPPRESSION=' . prepString2Update(sSignature()) . '
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
