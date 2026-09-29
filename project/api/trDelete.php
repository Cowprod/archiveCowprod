<?php

require_once __DIR__ . '/../../secure.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $PRO_N_ID = isset($_POST['PRO_N_ID']) ? (int) $_POST['PRO_N_ID'] : 0;

    if ($PRO_N_ID <= 0) {
        throw new RuntimeException('Projet invalide');
    }

    $WM_ADMIN_conn->beginTransaction();

    historiseTable('T_PROJECT', 'PRO', $PRO_N_ID, $WM_ADMIN_conn);

    $oDelete = $WM_ADMIN_conn->prepare(
        'UPDATE T_PROJECT
         SET
            PRO_DT_SUPPRESSION = NOW(),
            PRO_CH_SUPPRESSION = :PRO_CH_SUPPRESSION
         WHERE T_PROJECT.PRO_N_ID = :PRO_N_ID
           AND T_PROJECT.PRO_DT_SUPPRESSION IS NULL'
    );

    $oDelete->execute([
        'PRO_CH_SUPPRESSION' => sSignature(),
        'PRO_N_ID' => $PRO_N_ID,
    ]);

    if ($oDelete->rowCount() !== 1) {
        throw new RuntimeException('Projet introuvable');
    }

    $WM_ADMIN_conn->commit();

    echo json_encode([
        'success' => true,
    ]);
} catch (Throwable $e) {
    if (isset($WM_ADMIN_conn) && $WM_ADMIN_conn instanceof PDO && $WM_ADMIN_conn->inTransaction()) {
        $WM_ADMIN_conn->rollBack();
    }

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
