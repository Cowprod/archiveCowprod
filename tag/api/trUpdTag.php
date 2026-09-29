<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $TAG_N_ID = (int) ($_POST['TAG_N_ID'] ?? 0);
    $sField = trim((string) ($_POST['sField'] ?? ''));
    $sValue = trim((string) ($_POST['sValue'] ?? ''));

    if ($TAG_N_ID <= 0 || $sField !== 'TAG_CH_LABEL') {
        throw new RuntimeException('Tag invalide');
    }

    if ($sValue === '') {
        throw new RuntimeException('Le libellé est obligatoire');
    }

    $WM_ADMIN_conn->beginTransaction();

    historiseTable('T_TAG', 'TAG', $TAG_N_ID, $WM_ADMIN_conn);

    $oUpdate = $WM_ADMIN_conn->prepare(
        'UPDATE T_TAG
         SET TAG_CH_LABEL = :TAG_CH_LABEL
         WHERE T_TAG.TAG_N_ID = :TAG_N_ID
           AND T_TAG.TAG_DT_SUPPRESSION IS NULL'
    );

    $oUpdate->execute([
        'TAG_CH_LABEL' => $sValue,
        'TAG_N_ID' => $TAG_N_ID,
    ]);

    if ($oUpdate->rowCount() !== 1) {
        throw new RuntimeException('Tag introuvable');
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
