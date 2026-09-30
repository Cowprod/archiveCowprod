<?php

require_once __DIR__ . '/../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRU_N_ID = decryptId($_POST['PRU_N_ID'] ?? '', $sEncryptKey);

    if ($WM_ADMIN_conn->exec(
        'UPDATE T_PROJECTURL SET '
        . 'PRU_DT_SUPPRESSION=NOW(),'
        . 'PRU_CH_SUPPRESSION=' . prepString2Update(sSignature())
        . ' WHERE PRU_N_ID=' . prepNum2Update($PRU_N_ID)
        . ' AND PRU_DT_SUPPRESSION IS NULL'
    ) !== 1) {
        throw new RuntimeException('URL introuvable');
    }

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
