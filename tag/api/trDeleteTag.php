<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $TAG_N_ID = decryptId($_POST['TAG_N_ID'] ?? '', $sEncryptKey);
    $WM_ADMIN_conn->beginTransaction();

    $aLinks = oRs(
        '',
        __DIR__ . '/../../sql/tag/selectProjectTagLinks.sql',
        'TAG_N_ID=' . urlencode(prepNum2Update($TAG_N_ID)),
        0,
        '',
        $WM_ADMIN_conn
    );

    foreach($aLinks as $aLink) {
        $PTA_N_ID=(int)$aLink['PTA_N_ID'];
        $WM_ADMIN_conn->exec(
            'UPDATE T_PROJECTTAG SET PTA_DT_SUPPRESSION=NOW(),PTA_CH_SUPPRESSION=' . prepString2Update(sSignature())
            . ' WHERE PTA_N_ID=' . prepNum2Update($PTA_N_ID) . ' AND PTA_DT_SUPPRESSION IS NULL'
        );
    }


    if ($WM_ADMIN_conn->exec(
        'UPDATE T_TAG SET TAG_DT_SUPPRESSION=NOW(),TAG_CH_SUPPRESSION=' . prepString2Update(sSignature())
        . ' WHERE TAG_N_ID=' . prepNum2Update($TAG_N_ID) . ' AND TAG_DT_SUPPRESSION IS NULL'
    ) !== 1) {
        throw new RuntimeException('Tag introuvable');
    }

    $WM_ADMIN_conn->commit();
    echo json_encode(['success'=>true]);
} catch(Throwable $e) {
    if($WM_ADMIN_conn->inTransaction()) $WM_ADMIN_conn->rollBack();
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>$e->getMessage()]);
}
