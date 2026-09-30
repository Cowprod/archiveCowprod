<?php
require_once __DIR__ . '/../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $TAG_N_ID = decryptId($_POST['TAG_N_ID'] ?? '', $sEncryptKey);
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
