<?php

require_once __DIR__ . '/../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRO_N_ID = decryptId($_POST['PRO_N_ID'] ?? '', $sEncryptKey);
    $FTY_N_ID = decryptId($_POST['FTY_N_ID'] ?? '', $sEncryptKey);

    archiveAssertProject($WM_ADMIN_conn, $PRO_N_ID);
    archiveAssertFileType($WM_ADMIN_conn, $FTY_N_ID);

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image absente');
    }

    $sMime = archiveMimeType($_FILES['file']['tmp_name']);

    if (!archiveIsImageMime($sMime)) {
        throw new RuntimeException('Le presse-papiers ne contient pas une image');
    }

    $sExtension = [
        'image/png' => 'png',
        'image/jpeg' => 'jpg',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ][$sMime] ?? 'img';

    $sOriginal = 'presse-papiers_' . date('Ymd_His') . '.' . $sExtension;
    $sDir = archiveProjectStorageDir($PRO_N_ID);
    $sPath = $sDir . '/' . archiveUniqueStorageName($sOriginal);

    if (!move_uploaded_file($_FILES['file']['tmp_name'], $sPath)) {
        throw new RuntimeException('Impossible d’enregistrer l’image');
    }

    try {
        $PRF_N_ID = archiveInsertProjectFile(
            $WM_ADMIN_conn,
            $PRO_N_ID,
            $FTY_N_ID,
            $sPath,
            $sOriginal,
            null,
            null,
            'clipboard'
        );
    } catch (Throwable $e) {
        @unlink($sPath);
        throw $e;
    }

    echo json_encode(['success' => true, 'PRF_N_ID' => $PRF_N_ID]);
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
