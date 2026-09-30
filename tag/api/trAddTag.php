<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $TCA_N_ID = decryptId($_POST['TCA_N_ID'] ?? '', $sEncryptKey);
    $sLabel = trim((string) ($_POST['TAG_CH_LABEL'] ?? ''));

    if ($sLabel === '' || (int) getfield('count(*)','T_TAGCATEGORY','WHERE TCA_N_ID=' . prepNum2Update($TCA_N_ID) . ' AND TCA_DT_SUPPRESSION IS NULL',$WM_ADMIN_conn) !== 1) {
        throw new RuntimeException('Catégorie ou tag invalide');
    }

    $nOrder = (int) getfield('COALESCE(MAX(TAG_N_ORDER),0)+10','T_TAG','WHERE TCA_N_ID=' . prepNum2Update($TCA_N_ID) . ' AND TAG_DT_SUPPRESSION IS NULL',$WM_ADMIN_conn);

    $WM_ADMIN_conn->exec(
        'INSERT INTO T_TAG (TCA_N_ID,TAG_CH_LABEL,TAG_N_ORDER,TAG_DT_CREATION,TAG_CH_CREATION)
         VALUES (' . prepNum2Update($TCA_N_ID) . ',' . prepString2Update($sLabel) . ',' . prepNum2Update($nOrder) . ',NOW(),' . prepString2Update(sSignature()) . ')'
    );

    echo json_encode(['success'=>true]);
} catch(Throwable $e) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
