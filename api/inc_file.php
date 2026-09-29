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
    $oFinfo = new finfo(FILEINFO_MIME_TYPE);
    return (string) $oFinfo->file($sPath);
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

    $oOrder = $oConn->prepare(
        'SELECT COALESCE(MAX(T_PROJECTFILE.PRF_N_ORDER), 0) + 10
         FROM T_PROJECTFILE
         WHERE T_PROJECTFILE.PRO_N_ID = :PRO_N_ID
           AND T_PROJECTFILE.PRF_DT_SUPPRESSION IS NULL'
    );
    $oOrder->execute(['PRO_N_ID' => $PRO_N_ID]);

    $oInsert = $oConn->prepare(
        'INSERT INTO T_PROJECTFILE (
            PRO_N_ID,
            FTY_N_ID,
            PRF_CH_LABEL,
            PRF_CH_FILENAME,
            PRF_CH_MIMETYPE,
            PRF_N_SIZE,
            PRF_CH_PATH,
            PRF_CH_SOURCE,
            PRF_CH_SOURCE_URL,
            PRF_N_YEAR,
            PRF_N_ORDER,
            PRF_BL_SEARCHIMAGE,
            PRF_DT_CREATION,
            PRF_CH_CREATION
        ) VALUES (
            :PRO_N_ID,
            :FTY_N_ID,
            :PRF_CH_LABEL,
            :PRF_CH_FILENAME,
            :PRF_CH_MIMETYPE,
            :PRF_N_SIZE,
            :PRF_CH_PATH,
            :PRF_CH_SOURCE,
            :PRF_CH_SOURCE_URL,
            :PRF_N_YEAR,
            :PRF_N_ORDER,
            0,
            NOW(),
            :PRF_CH_CREATION
        )'
    );

    $oInsert->execute([
        'PRO_N_ID' => $PRO_N_ID,
        'FTY_N_ID' => $FTY_N_ID,
        'PRF_CH_LABEL' => $sLabel,
        'PRF_CH_FILENAME' => $sOriginalName,
        'PRF_CH_MIMETYPE' => $sMime,
        'PRF_N_SIZE' => $nSize,
        'PRF_CH_PATH' => $sPath,
        'PRF_CH_SOURCE' => $sSource,
        'PRF_CH_SOURCE_URL' => $sSourceUrl,
        'PRF_N_YEAR' => $nYear,
        'PRF_N_ORDER' => (int) $oOrder->fetchColumn(),
        'PRF_CH_CREATION' => sSignature(),
    ]);

    return (int) $oConn->lastInsertId();
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
    $o = $oConn->prepare(
        'SELECT T_PROJECT.PRO_N_ID
         FROM T_PROJECT
         WHERE T_PROJECT.PRO_N_ID = :PRO_N_ID
           AND T_PROJECT.PRO_DT_SUPPRESSION IS NULL'
    );
    $o->execute(['PRO_N_ID' => $PRO_N_ID]);

    if (!$o->fetchColumn()) {
        throw new RuntimeException('Projet introuvable');
    }
}

function archiveAssertFileType(PDO $oConn, int $FTY_N_ID): void
{
    $o = $oConn->prepare(
        'SELECT T_FILETYPE.FTY_N_ID
         FROM T_FILETYPE
         WHERE T_FILETYPE.FTY_N_ID = :FTY_N_ID
           AND T_FILETYPE.FTY_DT_SUPPRESSION IS NULL'
    );
    $o->execute(['FTY_N_ID' => $FTY_N_ID]);

    if (!$o->fetchColumn()) {
        throw new RuntimeException('Type de fichier introuvable');
    }
}
