<?php

require_once __DIR__ . '/../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRU_N_ID = decryptId($_POST['PRU_N_ID'] ?? '', $sEncryptKey);

    $aUrls = oRs(
        '',
        __DIR__ . '/projectUrlCapture.sql',
        'PRU_N_ID=' . prepNum2Update($PRU_N_ID),
        0,
        '',
        $WM_ADMIN_conn
    );

    $aUrl = $aUrls[0] ?? false;

    if (!$aUrl) {
        throw new RuntimeException('URL introuvable');
    }

    $PRO_N_ID = (int) $aUrl['PRO_N_ID'];
    $sUrl = trim((string) $aUrl['PRU_CH_URL']);
    $sLabel = trim((string) ($aUrl['PRU_CH_LABEL'] ?? ''));
    $nYear = $aUrl['PRU_N_YEAR'] === null ? null : (int) $aUrl['PRU_N_YEAR'];

    $FTY_N_ID = (int) getfield(
        'FTY_N_ID',
        'T_FILETYPE',
        "WHERE LOWER(FTY_CH_LABEL)='screenshot' AND FTY_DT_SUPPRESSION IS NULL ORDER BY FTY_N_ID ASC",
        $WM_ADMIN_conn
    );

    if ($FTY_N_ID <= 0) {
        throw new RuntimeException('Type de fichier Screenshot introuvable');
    }

    $sHost = (string) (parse_url($sUrl, PHP_URL_HOST) ?: 'url');
    $sOriginal = 'screenshot_' . archiveSafeFilename($sHost) . '_' . date('Ymd_His') . '.png';
    $sDir = archiveProjectStorageDir($PRO_N_ID);
    $sPath = $sDir . '/' . archiveUniqueStorageName($sOriginal);

    archiveCaptureUrlScreenshot($sUrl, $sPath);

    try {
        $PRF_N_ID = archiveInsertProjectFile(
            $WM_ADMIN_conn,
            $PRO_N_ID,
            $FTY_N_ID,
            $sPath,
            $sOriginal,
            $sLabel === '' ? $sHost : $sLabel,
            $nYear,
            'url-screenshot',
            $sUrl
        );
    } catch (Throwable $e) {
        @unlink($sPath);
        throw $e;
    }

    echo json_encode([
        'success' => true,
        'PRF_N_ID' => $PRF_N_ID,
    ]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
