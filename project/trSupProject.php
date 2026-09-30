<?php

require_once __DIR__ . '/../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRO_N_ID = decryptId($_POST['PRO_N_ID'] ?? '', $sEncryptKey);

    $sSql = 'UPDATE T_PROJECT
        SET PRO_DT_SUPPRESSION=NOW(),
            PRO_CH_SUPPRESSION=' . prepString2Update(sSignature()) . '
        WHERE PRO_N_ID=' . prepNum2Update($PRO_N_ID) . '
          AND PRO_DT_SUPPRESSION IS NULL';

    if ($WM_ADMIN_conn->exec($sSql) !== 1) {
        throw new RuntimeException('Projet introuvable');
    }

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
