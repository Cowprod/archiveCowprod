<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $sLabel = trim((string) ($_POST['TCA_CH_LABEL'] ?? ''));
    $sColor = trim((string) ($_POST['TCA_CH_COLOR'] ?? ''));
    $aColors = ['primary','secondary','success','danger','warning','info','light','dark'];

    if ($sLabel === '' || !in_array($sColor, $aColors, true)) {
        throw new RuntimeException('Catégorie invalide');
    }

    $nOrder = (int) getfield('COALESCE(MAX(TCA_N_ORDER),0)+10','T_TAGCATEGORY','WHERE TCA_DT_SUPPRESSION IS NULL',$WM_ADMIN_conn);

    $WM_ADMIN_conn->exec(
        'INSERT INTO T_TAGCATEGORY (TCA_CH_LABEL,TCA_CH_COLOR,TCA_N_ORDER,TCA_DT_CREATION,TCA_CH_CREATION)
         VALUES (' . prepString2Update($sLabel) . ',' . prepString2Update($sColor) . ',' . prepNum2Update($nOrder) . ',NOW(),' . prepString2Update(sSignature()) . ')'
    );

    echo json_encode(['success'=>true]);
} catch(Throwable $e) {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
