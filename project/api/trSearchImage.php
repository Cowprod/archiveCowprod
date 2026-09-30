<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRF_N_ID = decryptId($_POST['PRF_N_ID'] ?? '', $sEncryptKey);

    $aFiles = oRs(
        '',
        __DIR__ . '/../../sql/project/selectSearchImage.sql',
        'PRF_N_ID=' . urlencode(prepNum2Update($PRF_N_ID)),
        0,
        '',
        $WM_ADMIN_conn
    );;

    $aFile = $aFiles[0] ?? false;

    if (!$aFile || !archiveIsImageMime((string) $aFile['PRF_CH_MIMETYPE'])) {
        throw new RuntimeException('Image invalide');
    }

    $PRO_N_ID = (int) $aFile['PRO_N_ID'];

    $WM_ADMIN_conn->beginTransaction();

    $aImages = oRs(
        '',
        __DIR__ . '/../../sql/project/selectProjectImages.sql',
        'PRO_N_ID=' . urlencode(prepNum2Update($PRO_N_ID)),
        0,
        '',
        $WM_ADMIN_conn
    );;

    foreach ($aImages as $aImage) {
        $nFileId = (int) $aImage['PRF_N_ID'];
        $nWanted = $nFileId === $PRF_N_ID ? 1 : 0;

        if ((int) $aImage['PRF_BL_SEARCHIMAGE'] === $nWanted) {
            continue;
        }

        historiseTable('T_PROJECTFILE', 'PRF', $nFileId, $WM_ADMIN_conn);

        $WM_ADMIN_conn->exec(
            'UPDATE T_PROJECTFILE
             SET PRF_BL_SEARCHIMAGE=' . prepNum2Update($nWanted) . '
             WHERE PRF_N_ID=' . prepNum2Update($nFileId)
        );
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
