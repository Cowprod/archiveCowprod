<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $sLabel = trim((string) ($_POST['TCA_CH_LABEL'] ?? ''));
    $sColor = trim((string) ($_POST['TCA_CH_COLOR'] ?? ''));

    if ($sLabel === '') {
        throw new RuntimeException('Le libellé de la catégorie est obligatoire');
    }

    $aColors = ['primary','secondary','success','danger','warning','info','light','dark'];

    if (!in_array($sColor, $aColors, true)) {
        throw new RuntimeException('Couleur invalide');
    }

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
        'TCA_CH_LABEL' => $sLabel,
        'TCA_CH_COLOR' => $sColor,
        'TCA_N_ORDER' => $nOrder,
        'TCA_CH_CREATION' => sSignature(),
    ]);

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
