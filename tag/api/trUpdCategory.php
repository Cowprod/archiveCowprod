<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $TCA_N_ID = (int) ($_POST['TCA_N_ID'] ?? 0);
    $sField = trim((string) ($_POST['sField'] ?? ''));
    $sValue = trim((string) ($_POST['sValue'] ?? ''));

    $aAllowedFields = [
        'TCA_CH_LABEL' => 'text',
        'TCA_CH_COLOR' => 'color',
    ];

    if ($TCA_N_ID <= 0 || !isset($aAllowedFields[$sField])) {
        throw new RuntimeException('Catégorie invalide');
    }

    if ($aAllowedFields[$sField] === 'text' && $sValue === '') {
        throw new RuntimeException('Le libellé est obligatoire');
    }

    if ($aAllowedFields[$sField] === 'color') {
        $aColors = ['primary','secondary','success','danger','warning','info','light','dark'];

        if (!in_array($sValue, $aColors, true)) {
            throw new RuntimeException('Couleur invalide');
        }
    }

    $WM_ADMIN_conn->beginTransaction();

    historiseTable('T_TAGCATEGORY', 'TCA', $TCA_N_ID, $WM_ADMIN_conn);

    $oUpdate = $WM_ADMIN_conn->prepare(
        'UPDATE T_TAGCATEGORY
         SET ' . $sField . ' = :sValue
         WHERE T_TAGCATEGORY.TCA_N_ID = :TCA_N_ID
           AND T_TAGCATEGORY.TCA_DT_SUPPRESSION IS NULL'
    );

    $oUpdate->execute([
        'sValue' => $sValue,
        'TCA_N_ID' => $TCA_N_ID,
    ]);

    if ($oUpdate->rowCount() !== 1) {
        throw new RuntimeException('Catégorie introuvable');
    }

    $WM_ADMIN_conn->commit();

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    if (isset($WM_ADMIN_conn) && $WM_ADMIN_conn instanceof PDO && $WM_ADMIN_conn->inTransaction()) {
        $WM_ADMIN_conn->rollBack();
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
