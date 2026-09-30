<?php
require_once __DIR__ . '/../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $sLabel = trim((string) ($_POST['FTY_CH_LABEL'] ?? ''));

    if ($sLabel === '') {
        throw new RuntimeException('Type invalide');
    }

    if (!isset($_POST['FTY_N_ID']) || trim((string) $_POST['FTY_N_ID']) === '') {
        $FTY_N_ID = getIdConPdo('T_FILETYPE', 'FTY_CH_CREATION', 'temporaire', $WM_ADMIN_conn);

        $WM_ADMIN_conn->exec(
            'UPDATE T_FILETYPE SET '
            . 'FTY_DT_CREATION=NOW(),'
            . 'FTY_CH_CREATION=' . prepString2Update(sSignature()) . ','
            . 'FTY_CH_LABEL=' . prepString2Update($sLabel)
            . ' WHERE FTY_N_ID=' . prepNum2Update($FTY_N_ID)
        );
    } else {
        $FTY_N_ID = decryptId($_POST['FTY_N_ID'], $sEncryptKey);

        $WM_ADMIN_conn->beginTransaction();
        historiseTable('T_FILETYPE', 'FTY', $FTY_N_ID, $WM_ADMIN_conn);

        if ($WM_ADMIN_conn->exec(
            'UPDATE T_FILETYPE SET FTY_CH_LABEL=' . prepString2Update($sLabel)
            . ' WHERE FTY_N_ID=' . prepNum2Update($FTY_N_ID)
            . ' AND FTY_DT_SUPPRESSION IS NULL'
        ) !== 1) {
            throw new RuntimeException('Type introuvable');
        }

        $WM_ADMIN_conn->commit();
    }

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    if ($WM_ADMIN_conn->inTransaction()) {
        $WM_ADMIN_conn->rollBack();
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
