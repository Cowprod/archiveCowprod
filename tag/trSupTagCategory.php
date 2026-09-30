<?php
require_once __DIR__ . '/../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $TCA_N_ID = decryptId($_POST['TCA_N_ID'] ?? '', $sEncryptKey);

    if ((int) getfield('count(*)','T_TAG','WHERE TCA_N_ID=' . prepNum2Update($TCA_N_ID) . ' AND TAG_DT_SUPPRESSION IS NULL',$WM_ADMIN_conn) > 0) {
        throw new RuntimeException('Supprime d’abord les tags de cette catégorie');
    }

    $WM_ADMIN_conn->beginTransaction();

    if ($WM_ADMIN_conn->exec(
        'UPDATE T_TAGCATEGORY SET TCA_DT_SUPPRESSION=NOW(),TCA_CH_SUPPRESSION=' . prepString2Update(sSignature())
        . ' WHERE TCA_N_ID=' . prepNum2Update($TCA_N_ID) . ' AND TCA_DT_SUPPRESSION IS NULL'
    ) !== 1) {
        throw new RuntimeException('Catégorie introuvable');
    }

    $WM_ADMIN_conn->commit();
    echo json_encode(['success'=>true]);
} catch(Throwable $e) {
    if($WM_ADMIN_conn->inTransaction()) $WM_ADMIN_conn->rollBack();
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
