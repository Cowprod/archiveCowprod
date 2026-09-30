<?php

require_once __DIR__ . '/../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRO_N_ID = decryptId($_POST['PRO_N_ID'] ?? '', $sEncryptKey);
    $FTY_N_ID = decryptId($_POST['FTY_N_ID'] ?? '', $sEncryptKey);
    $sLabel = trim((string) ($_POST['PRF_CH_LABEL'] ?? ''));
    $nYear = archiveValidateYear($_POST['PRF_N_YEAR'] ?? null);

    archiveAssertProject($WM_ADMIN_conn, $PRO_N_ID);
    archiveAssertFileType($WM_ADMIN_conn, $FTY_N_ID);

    if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Fichier absent ou upload incomplet');
    }

    if (!is_uploaded_file($_FILES['file']['tmp_name'])) {
        throw new RuntimeException('Upload invalide');
    }

    $sDir = archiveProjectStorageDir($PRO_N_ID);
    $sName = archiveUniqueStorageName((string) $_FILES['file']['name']);
    $sPath = $sDir . '/' . $sName;

    if (!move_uploaded_file($_FILES['file']['tmp_name'], $sPath)) {
        throw new RuntimeException('Impossible d’enregistrer le fichier');
    }

    try {
        $PRF_N_ID = archiveInsertProjectFile(
            $WM_ADMIN_conn,
            $PRO_N_ID,
            $FTY_N_ID,
            $sPath,
            (string) $_FILES['file']['name'],
            $sLabel === '' ? null : $sLabel,
            $nYear,
            'upload'
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
