<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRU_N_ID = (int) ($_POST['PRU_N_ID'] ?? 0);

    if ($PRU_N_ID <= 0) {
        throw new RuntimeException('URL invalide');
    }

    $WM_ADMIN_conn->beginTransaction();
    historiseTable('T_PROJECTURL', 'PRU', $PRU_N_ID, $WM_ADMIN_conn);

    $oDelete = $WM_ADMIN_conn->prepare(
        'UPDATE T_PROJECTURL
         SET
            PRU_DT_SUPPRESSION = NOW(),
            PRU_CH_SUPPRESSION = :PRU_CH_SUPPRESSION
         WHERE T_PROJECTURL.PRU_N_ID = :PRU_N_ID
           AND T_PROJECTURL.PRU_DT_SUPPRESSION IS NULL'
    );
    $oDelete->execute([
        'PRU_CH_SUPPRESSION' => sSignature(),
        'PRU_N_ID' => $PRU_N_ID,
    ]);

    if ($oDelete->rowCount() !== 1) {
        throw new RuntimeException('URL introuvable');
    }

    $WM_ADMIN_conn->commit();
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    if (isset($WM_ADMIN_conn) && $WM_ADMIN_conn instanceof PDO && $WM_ADMIN_conn->inTransaction()) {
        $WM_ADMIN_conn->rollBack();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
