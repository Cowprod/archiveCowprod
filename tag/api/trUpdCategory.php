<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $TCA_N_ID = decryptId($_POST['TCA_N_ID'] ?? '', $sEncryptKey);
    $sField = trim((string) ($_POST['sField'] ?? ''));
    $sValue = trim((string) ($_POST['sValue'] ?? ''));
    $aAllowed = ['TCA_CH_LABEL','TCA_CH_COLOR'];
    $aColors = ['primary','secondary','success','danger','warning','info','light','dark'];

    if (!in_array($sField,$aAllowed,true) || $sValue === '' || ($sField === 'TCA_CH_COLOR' && !in_array($sValue,$aColors,true))) {
        throw new RuntimeException('Catégorie invalide');
    }

    $WM_ADMIN_conn->beginTransaction();
    historiseTable('T_TAGCATEGORY','TCA',$TCA_N_ID,$WM_ADMIN_conn);

    if ($WM_ADMIN_conn->exec(
        'UPDATE T_TAGCATEGORY SET ' . $sField . '=' . prepString2Update($sValue)
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
