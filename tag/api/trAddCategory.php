<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $nOrder = (int) $WM_ADMIN_conn->query(
        'SELECT COALESCE(MAX(T_TAGCATEGORY.TCA_N_ORDER), 0) + 10
         FROM T_TAGCATEGORY
         WHERE T_TAGCATEGORY.TCA_DT_SUPPRESSION IS NULL'
    )->fetchColumn();

    $oInsert = $WM_ADMIN_conn->prepare(
        'INSERT INTO T_TAGCATEGORY (
            TCA_CH_LABEL,
            TCA_CH_COLOR,
            TCA_N_ORDER,
            TCA_DT_CREATION,
            TCA_CH_CREATION
        ) VALUES (
            :TCA_CH_LABEL,
            :TCA_CH_COLOR,
            :TCA_N_ORDER,
            NOW(),
            :TCA_CH_CREATION
        )'
    );

    $oInsert->execute([
        'TCA_CH_LABEL' => 'Nouvelle catégorie',
        'TCA_CH_COLOR' => 'secondary',
        'TCA_N_ORDER' => $nOrder,
        'TCA_CH_CREATION' => sSignature(),
    ]);

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
