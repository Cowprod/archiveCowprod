<?php

require_once __DIR__ . '/../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRF_N_ID = decryptId($_POST['PRF_N_ID'] ?? '', $sEncryptKey);

    if ($WM_ADMIN_conn->exec(
        'UPDATE T_PROJECTFILE SET '
        . 'PRF_DT_SUPPRESSION=NOW(),'
        . 'PRF_CH_SUPPRESSION=' . prepString2Update(sSignature())
        . ' WHERE PRF_N_ID=' . prepNum2Update($PRF_N_ID)
        . ' AND PRF_DT_SUPPRESSION IS NULL'
    ) !== 1) {
        throw new RuntimeException('Fichier introuvable');
    }

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
