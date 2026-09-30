<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $UTY_N_ID = decryptId($_POST['UTY_N_ID'] ?? '', $sEncryptKey);

    $nCount = (int) getfield(
        'count(*)',
        'T_PROJECTURL',
        'WHERE UTY_N_ID=' . prepNum2Update($UTY_N_ID) . ' AND PRU_DT_SUPPRESSION IS NULL',
        $WM_ADMIN_conn
    );

    if ($nCount > 0) {
        throw new RuntimeException('Ce type est utilisé par une ou plusieurs URLs');
    }

    $WM_ADMIN_conn->beginTransaction();
    historiseTable('T_URLTYPE', 'UTY', $UTY_N_ID, $WM_ADMIN_conn);

    $sSql = 'UPDATE T_URLTYPE
        SET UTY_DT_SUPPRESSION=NOW(),
            UTY_CH_SUPPRESSION=' . prepString2Update(sSignature()) . '
        WHERE UTY_N_ID=' . prepNum2Update($UTY_N_ID) . '
          AND UTY_DT_SUPPRESSION IS NULL';

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
