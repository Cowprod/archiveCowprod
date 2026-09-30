<?php
require_once __DIR__ . '/../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $sLabel = trim((string) ($_POST['UTY_CH_LABEL'] ?? ''));

    if ($sLabel === '') {
        throw new RuntimeException('Type invalide');
    }

    if (!isset($_POST['UTY_N_ID']) || trim((string) $_POST['UTY_N_ID']) === '') {
        $UTY_N_ID = getIdConPdo('T_URLTYPE', 'UTY_CH_CREATION', 'temporaire', $WM_ADMIN_conn);

        $WM_ADMIN_conn->exec(
            'UPDATE T_URLTYPE SET '
            . 'UTY_DT_CREATION=NOW(),'
            . 'UTY_CH_CREATION=' . prepString2Update(sSignature()) . ','
            . 'UTY_CH_LABEL=' . prepString2Update($sLabel)
            . ' WHERE UTY_N_ID=' . prepNum2Update($UTY_N_ID)
        );

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
