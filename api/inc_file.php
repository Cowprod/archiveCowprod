<?php

function archiveProjectStorageDir(int $PRO_N_ID): string
{
    global $sStoragePath;

    $sDir = rtrim($sStoragePath, '/') . '/project_' . $PRO_N_ID;

    if (!is_dir($sDir) && !mkdir($sDir, 0775, true) && !is_dir($sDir)) {
        throw new RuntimeException('Impossible de créer le dossier du projet');
    }

    if (!is_writable($sDir)) {
        throw new RuntimeException('Dossier du projet non accessible en écriture');
    }

    return $sDir;
}

function archiveProjectCacheDir(int $PRO_N_ID): string
{
    global $sStorageCachePath;

    $sDir = rtrim($sStorageCachePath, '/') . '/project_' . $PRO_N_ID;

    if (!is_dir($sDir) && !mkdir($sDir, 0775, true) && !is_dir($sDir)) {
        throw new RuntimeException('Impossible de créer le dossier de cache du projet');
    }

    return $sDir;
}

function archiveSafeFilename(string $sFilename): string
{
    $sFilename = basename($sFilename);
    $sFilename = preg_replace('/[^A-Za-z0-9._-]+/', '_', $sFilename) ?: 'file';
    return trim($sFilename, '._-') !== '' ? $sFilename : 'file';
}

function archiveMimeType(string $sPath): string
{
    if (class_exists('finfo')) {
        $oFinfo = new finfo(FILEINFO_MIME_TYPE);
        $sMime = (string) $oFinfo->file($sPath);

        if ($sMime !== '') {
            return $sMime;
        }
    }

    if (function_exists('mime_content_type')) {
        $sMime = (string) @mime_content_type($sPath);

        if ($sMime !== '') {
            return $sMime;
        }
    }

    if (function_exists('getimagesize')) {
        $aImageInfo = @getimagesize($sPath);

        if (is_array($aImageInfo) && isset($aImageInfo['mime'])) {
            return (string) $aImageInfo['mime'];
        }
    }

    $sExtension = strtolower((string) pathinfo($sPath, PATHINFO_EXTENSION));

    $aMimeByExtension = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'pdf' => 'application/pdf',
        'zip' => 'application/zip',
        '7z' => 'application/x-7z-compressed',
        'rar' => 'application/vnd.rar',
        'txt' => 'text/plain',
        'md' => 'text/markdown',
        'html' => 'text/html',
        'htm' => 'text/html',
        'css' => 'text/css',
        'js' => 'text/javascript',
        'json' => 'application/json',
        'xml' => 'application/xml',
        'csv' => 'text/csv',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ];

    return $aMimeByExtension[$sExtension] ?? 'application/octet-stream';
}

function archiveIsImageMime(string $sMime): bool
{
    return str_starts_with(strtolower($sMime), 'image/');
}

function archiveUniqueStorageName(string $sOriginalName): string
{
    $sSafe = archiveSafeFilename($sOriginalName);
    $sExt = pathinfo($sSafe, PATHINFO_EXTENSION);
    $sBase = pathinfo($sSafe, PATHINFO_FILENAME);
    $sToken = date('Ymd_His') . '_' . bin2hex(random_bytes(4));

    return $sBase . '_' . $sToken . ($sExt !== '' ? '.' . $sExt : '');
}

function archiveInsertProjectFile(
    PDO $oConn,
    int $PRO_N_ID,
    int $FTY_N_ID,
    string $sPath,
    string $sOriginalName,
    ?string $sLabel,
    ?int $nYear,
    string $sSource,
    ?string $sSourceUrl = null
): int {
    $sMime = archiveMimeType($sPath);
    $nSize = filesize($sPath);

    $nOrder = (int) getfield(
        'COALESCE(MAX(PRF_N_ORDER), 0) + 10',
        'T_PROJECTFILE',
        'WHERE PRO_N_ID=' . prepNum2Update($PRO_N_ID) . ' AND PRF_DT_SUPPRESSION IS NULL',
        $oConn
    );

    $bSearchImage = false;

    if (archiveIsImageMime($sMime)) {
        $bSearchImage = (int) getfield(
            'count(*)',
            'T_PROJECTFILE',
            'WHERE PRO_N_ID=' . prepNum2Update($PRO_N_ID)
                . ' AND PRF_DT_SUPPRESSION IS NULL'
                . " AND PRF_CH_MIMETYPE LIKE 'image/%'",
            $oConn
        ) === 0;
    }

    $PRF_N_ID = getIdConPdo('T_PROJECTFILE', 'PRF_CH_CREATION', 'temporaire', $oConn);

    $oConn->exec(
        'UPDATE T_PROJECTFILE SET '
        . 'PRO_N_ID=' . prepNum2Update($PRO_N_ID) . ','
        . 'FTY_N_ID=' . prepNum2Update($FTY_N_ID) . ','
        . 'PRF_CH_LABEL=' . ($sLabel === null || trim($sLabel) === '' ? 'null' : prepString2Update($sLabel)) . ','
        . 'PRF_CH_FILENAME=' . prepString2Update($sOriginalName) . ','
        . 'PRF_CH_MIMETYPE=' . prepString2Update($sMime) . ','
        . 'PRF_N_SIZE=' . prepNum2Update($nSize) . ','
        . 'PRF_CH_PATH=' . prepString2Update($sPath) . ','
        . 'PRF_CH_SOURCE=' . prepString2Update($sSource) . ','
        . 'PRF_CH_SOURCE_URL=' . ($sSourceUrl === null || trim($sSourceUrl) === '' ? 'null' : prepString2Update($sSourceUrl)) . ','
        . 'PRF_N_YEAR=' . prepNum2Update($nYear) . ','
        . 'PRF_N_ORDER=' . prepNum2Update($nOrder) . ','
        . 'PRF_BL_SEARCHIMAGE=' . prepNum2Update($bSearchImage ? 1 : 0) . ','
        . 'PRF_DT_CREATION=NOW(),'
        . 'PRF_CH_CREATION=' . prepString2Update(sSignature())
        . ' WHERE PRF_N_ID=' . prepNum2Update($PRF_N_ID)
    );

    return $PRF_N_ID;
}

function archiveValidateYear(?string $sYear): ?int
{
    $sYear = trim((string) $sYear);

    if ($sYear === '') {
        return null;
    }

    if (!ctype_digit($sYear) || (int) $sYear < 1900 || (int) $sYear > 2100) {
        throw new RuntimeException('Année invalide');
    }

    return (int) $sYear;
}

function archiveAssertProject(PDO $oConn, int $PRO_N_ID): void
{
    if ((int) getfield(
        'count(*)',
        'T_PROJECT',
        'WHERE PRO_N_ID=' . prepNum2Update($PRO_N_ID) . ' AND PRO_DT_SUPPRESSION IS NULL',
        $oConn
    ) !== 1) {
        throw new RuntimeException('Projet introuvable');
    }
}

function archiveAssertFileType(PDO $oConn, int $FTY_N_ID): void
{
    if ((int) getfield(
        'count(*)',
        'T_FILETYPE',
        'WHERE FTY_N_ID=' . prepNum2Update($FTY_N_ID) . ' AND FTY_DT_SUPPRESSION IS NULL',
        $oConn
    ) !== 1) {
        throw new RuntimeException('Type de fichier introuvable');
    }
}
