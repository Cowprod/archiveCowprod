<?php
require_once __DIR__ . '/../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_POST['TAG_N_ID']) || trim((string) $_POST['TAG_N_ID']) === '') {
        $TCA_N_ID = decryptId($_POST['TCA_N_ID'] ?? '', $sEncryptKey);
        $sLabel = trim((string) ($_POST['TAG_CH_LABEL'] ?? ''));
        if ($sLabel === '' || (int) getfield('count(*)','T_TAGCATEGORY','WHERE TCA_N_ID=' . prepNum2Update($TCA_N_ID) . ' AND TCA_DT_SUPPRESSION IS NULL',$WM_ADMIN_conn) !== 1) throw new RuntimeException('Catégorie ou tag invalide');
        $nOrder = (int) getfield('COALESCE(MAX(TAG_N_ORDER),0)+10','T_TAG','WHERE TCA_N_ID=' . prepNum2Update($TCA_N_ID) . ' AND TAG_DT_SUPPRESSION IS NULL',$WM_ADMIN_conn);
        $TAG_N_ID = getIdConPdo('T_TAG','TAG_CH_CREATION','temporaire',$WM_ADMIN_conn);
        $WM_ADMIN_conn->exec('UPDATE T_TAG SET TAG_DT_CREATION=NOW(),TAG_CH_CREATION=' . prepString2Update(sSignature()) . ',TCA_N_ID=' . prepNum2Update($TCA_N_ID) . ',TAG_CH_LABEL=' . prepString2Update($sLabel) . ',TAG_N_ORDER=' . prepNum2Update($nOrder) . ' WHERE TAG_N_ID=' . prepNum2Update($TAG_N_ID));
        echo json_encode(['success'=>true]);
        exit;
    }

    $TAG_N_ID = decryptId($_POST['TAG_N_ID'], $sEncryptKey);
    $sValue = trim((string) ($_POST['sValue'] ?? ''));

    if (($_POST['sField'] ?? '') !== 'TAG_CH_LABEL' || $sValue === '') {
        throw new RuntimeException('Tag invalide');
    }

    $WM_ADMIN_conn->beginTransaction();
    historiseTable('T_TAG','TAG',$TAG_N_ID,$WM_ADMIN_conn);

    if ($WM_ADMIN_conn->exec(
        'UPDATE T_TAG SET TAG_CH_LABEL=' . prepString2Update($sValue)
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
