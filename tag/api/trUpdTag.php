<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $TAG_N_ID = (int) ($_POST['TAG_N_ID'] ?? 0);
    $sField = trim((string) ($_POST['sField'] ?? ''));
    $sValue = trim((string) ($_POST['sValue'] ?? ''));

    $aAllowedFields = [
        'TAG_CH_LABEL' => 'text',
        'TAG_N_ORDER' => 'int',
    ];

    if ($TAG_N_ID <= 0 || !isset($aAllowedFields[$sField])) {
        throw new RuntimeException('Tag invalide');
    }

    if ($aAllowedFields[$sField] === 'text' && $sValue === '') {
        throw new RuntimeException('Le libellé est obligatoire');
    }

    if ($aAllowedFields[$sField] === 'int') {
        if (!preg_match('/^-?\d+$/', $sValue)) {
            throw new RuntimeException('Ordre invalide');
        }

        $mValue = (int) $sValue;
    } else {
        $mValue = $sValue;
    }

    $WM_ADMIN_conn->beginTransaction();

    historiseTable('T_TAG', 'TAG', $TAG_N_ID, $WM_ADMIN_conn);

    $oUpdate = $WM_ADMIN_conn->prepare(
        'UPDATE T_TAG
         SET ' . $sField . ' = :sValue
         WHERE T_TAG.TAG_N_ID = :TAG_N_ID
           AND T_TAG.TAG_DT_SUPPRESSION IS NULL'
    );

    $oUpdate->bindValue(':sValue', $mValue, is_int($mValue) ? PDO::PARAM_INT : PDO::PARAM_STR);
    $oUpdate->bindValue(':TAG_N_ID', $TAG_N_ID, PDO::PARAM_INT);
    $oUpdate->execute();

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
