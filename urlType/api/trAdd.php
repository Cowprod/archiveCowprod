<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $sLabel = trim((string) ($_POST['UTY_CH_LABEL'] ?? ''));

    if ($sLabel === '') {
        throw new RuntimeException('Le libellé est obligatoire');
    }

    $sSql = 'INSERT INTO T_URLTYPE (
        UTY_CH_LABEL,
        UTY_DT_CREATION,
        UTY_CH_CREATION
    ) VALUES (
        ' . prepString2Update($sLabel) . ',
        NOW(),
        ' . prepString2Update(sSignature()) . '
    )';

    $WM_ADMIN_conn->exec($sSql);
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
