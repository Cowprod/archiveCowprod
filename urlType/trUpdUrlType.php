<?php
require_once __DIR__ . '/../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $sLabel = trim((string) ($_POST['UTY_CH_LABEL'] ?? ''));

    if ($sLabel === '') {
        throw new RuntimeException('Type invalide');
    }

    if (!isset($_POST['UTY_N_ID']) || trim((string) $_POST['UTY_N_ID']) === '') {
        $WM_ADMIN_conn->exec('INSERT INTO T_URLTYPE (UTY_CH_LABEL,UTY_DT_CREATION,UTY_CH_CREATION) VALUES (' . prepString2Update($sLabel) . ',NOW(),' . prepString2Update(sSignature()) . ')');
        echo json_encode(['success' => true]);
        exit;
    }

    $UTY_N_ID = decryptId($_POST['UTY_N_ID'], $sEncryptKey);
    $WM_ADMIN_conn->beginTransaction();
    historiseTable('T_URLTYPE', 'UTY', $UTY_N_ID, $WM_ADMIN_conn);

    $sSql = 'UPDATE T_URLTYPE
        SET UTY_CH_LABEL=' . prepString2Update($sLabel) . '
        WHERE UTY_N_ID=' . prepNum2Update($UTY_N_ID) . '
          AND UTY_DT_SUPPRESSION IS NULL';

    if ($WM_ADMIN_conn->exec($sSql) !== 1) {
        throw new RuntimeException('Type introuvable');
    }

    $WM_ADMIN_conn->commit();
    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    if ($WM_ADMIN_conn->inTransaction()) {
        $WM_ADMIN_conn->rollBack();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
