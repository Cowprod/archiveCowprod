<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $TCA_N_ID = decryptId($_POST['TCA_N_ID'] ?? '', $sEncryptKey);

    if ($TCA_N_ID <= 0) {
        throw new RuntimeException('Catégorie invalide');
    }

    $oCount = $WM_ADMIN_conn->prepare(
        'SELECT COUNT(*)
         FROM T_TAG
         WHERE T_TAG.TCA_N_ID = :TCA_N_ID
           AND T_TAG.TAG_DT_SUPPRESSION IS NULL'
    );

    $oCount->execute(['TCA_N_ID' => $TCA_N_ID]);

    if ((int) $oCount->fetchColumn() > 0) {
        throw new RuntimeException('Supprime d’abord les tags de cette catégorie');
    }

    $WM_ADMIN_conn->beginTransaction();

    historiseTable('T_TAGCATEGORY', 'TCA', $TCA_N_ID, $WM_ADMIN_conn);

    $oDelete = $WM_ADMIN_conn->prepare(
        'UPDATE T_TAGCATEGORY
         SET
            TCA_DT_SUPPRESSION = NOW(),
            TCA_CH_SUPPRESSION = :TCA_CH_SUPPRESSION
         WHERE T_TAGCATEGORY.TCA_N_ID = :TCA_N_ID
           AND T_TAGCATEGORY.TCA_DT_SUPPRESSION IS NULL'
    );

    $oDelete->execute([
        'TCA_CH_SUPPRESSION' => sSignature(),
        'TCA_N_ID' => $TCA_N_ID,
    ]);

    if ($oDelete->rowCount() !== 1) {
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
