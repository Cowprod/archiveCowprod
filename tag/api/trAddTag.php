<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $TCA_N_ID = (int) ($_POST['TCA_N_ID'] ?? 0);
    $sLabel = trim((string) ($_POST['TAG_CH_LABEL'] ?? ''));

    if ($TCA_N_ID <= 0) {
        throw new RuntimeException('Catégorie invalide');
    }

    if ($sLabel === '') {
        throw new RuntimeException('Le libellé du tag est obligatoire');
    }

    $oCategory = $WM_ADMIN_conn->prepare(
        'SELECT T_TAGCATEGORY.TCA_N_ID
         FROM T_TAGCATEGORY
         WHERE T_TAGCATEGORY.TCA_N_ID = :TCA_N_ID
           AND T_TAGCATEGORY.TCA_DT_SUPPRESSION IS NULL'
    );

    $oCategory->execute(['TCA_N_ID' => $TCA_N_ID]);

    if (!$oCategory->fetchColumn()) {
        throw new RuntimeException('Catégorie introuvable');
    }

    $oOrder = $WM_ADMIN_conn->prepare(
        'SELECT COALESCE(MAX(T_TAG.TAG_N_ORDER), 0) + 10
         FROM T_TAG
         WHERE T_TAG.TCA_N_ID = :TCA_N_ID
           AND T_TAG.TAG_DT_SUPPRESSION IS NULL'
    );

    $oOrder->execute(['TCA_N_ID' => $TCA_N_ID]);
    $nOrder = (int) $oOrder->fetchColumn();

    $oInsert = $WM_ADMIN_conn->prepare(
        'INSERT INTO T_TAG (
            TCA_N_ID,
            TAG_CH_LABEL,
            TAG_N_ORDER,
            TAG_DT_CREATION,
            TAG_CH_CREATION
        ) VALUES (
            :TCA_N_ID,
            :TAG_CH_LABEL,
            :TAG_N_ORDER,
            NOW(),
            :TAG_CH_CREATION
        )'
    );

    $oInsert->execute([
        'TCA_N_ID' => $TCA_N_ID,
        'TAG_CH_LABEL' => $sLabel,
        'TAG_N_ORDER' => $nOrder,
        'TAG_CH_CREATION' => sSignature(),
    ]);

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
