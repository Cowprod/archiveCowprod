<?php

require_once __DIR__ . '/../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRF_N_ID = decryptId($_POST['PRF_N_ID'] ?? '', $sEncryptKey);

    $aFiles = oRs(
        '',
        __DIR__ . '/projectFileDelete.sql',
        'PRF_N_ID=' . prepNum2Update($PRF_N_ID),
        0,
        '',
        $WM_ADMIN_conn
    );

    $aFile = $aFiles[0] ?? false;

    if (!$aFile) {
        throw new RuntimeException('Fichier introuvable');
    }

    $PRO_N_ID = (int) $aFile['PRO_N_ID'];
    $bWasSearchImage = (int) $aFile['PRF_BL_SEARCHIMAGE'] === 1;

    $WM_ADMIN_conn->beginTransaction();

    if ($WM_ADMIN_conn->exec(
        'UPDATE T_PROJECTFILE SET '
        . 'PRF_DT_SUPPRESSION=NOW(),'
        . 'PRF_CH_SUPPRESSION=' . prepString2Update(sSignature())
        . ' WHERE PRF_N_ID=' . prepNum2Update($PRF_N_ID)
        . ' AND PRF_DT_SUPPRESSION IS NULL'
    ) !== 1) {
        throw new RuntimeException('Fichier introuvable');
    }

    if ($bWasSearchImage) {
        $aImages = oRs(
            '',
            __DIR__ . '/projectFileFirstImage.sql',
            'PRO_N_ID=' . prepNum2Update($PRO_N_ID),
            0,
            '',
            $WM_ADMIN_conn
        );

        if (isset($aImages[0]['PRF_N_ID'])) {
            $PRF_N_IDreplacement = (int) $aImages[0]['PRF_N_ID'];

            historiseTable('T_PROJECTFILE', 'PRF', $PRF_N_IDreplacement, $WM_ADMIN_conn);

            $WM_ADMIN_conn->exec(
                'UPDATE T_PROJECTFILE SET PRF_BL_SEARCHIMAGE=1'
                . ' WHERE PRF_N_ID=' . prepNum2Update($PRF_N_IDreplacement)
                . ' AND PRF_DT_SUPPRESSION IS NULL'
            );
        }
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
