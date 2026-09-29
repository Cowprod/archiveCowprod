<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $TAG_N_ID = (int) ($_POST['TAG_N_ID'] ?? 0);

    if ($TAG_N_ID <= 0) {
        throw new RuntimeException('Tag invalide');
    }

    $WM_ADMIN_conn->beginTransaction();

    $oLinks = $WM_ADMIN_conn->prepare(
        'SELECT T_PROJECTTAG.PTA_N_ID
         FROM T_PROJECTTAG
         WHERE T_PROJECTTAG.TAG_N_ID = :TAG_N_ID
           AND T_PROJECTTAG.PTA_DT_SUPPRESSION IS NULL'
    );

    $oLinks->execute(['TAG_N_ID' => $TAG_N_ID]);

    foreach ($oLinks->fetchAll() as $aLink) {
        $PTA_N_ID = (int) $aLink['PTA_N_ID'];

        historiseTable('T_PROJECTTAG', 'PTA', $PTA_N_ID, $WM_ADMIN_conn);

        $oDeleteLink = $WM_ADMIN_conn->prepare(
            'UPDATE T_PROJECTTAG
             SET
                PTA_DT_SUPPRESSION = NOW(),
                PTA_CH_SUPPRESSION = :PTA_CH_SUPPRESSION
             WHERE T_PROJECTTAG.PTA_N_ID = :PTA_N_ID
               AND T_PROJECTTAG.PTA_DT_SUPPRESSION IS NULL'
        );

        $oDeleteLink->execute([
            'PTA_CH_SUPPRESSION' => sSignature(),
            'PTA_N_ID' => $PTA_N_ID,
        ]);
    }

    historiseTable('T_TAG', 'TAG', $TAG_N_ID, $WM_ADMIN_conn);

    $oDeleteTag = $WM_ADMIN_conn->prepare(
        'UPDATE T_TAG
         SET
            TAG_DT_SUPPRESSION = NOW(),
            TAG_CH_SUPPRESSION = :TAG_CH_SUPPRESSION
         WHERE T_TAG.TAG_N_ID = :TAG_N_ID
           AND T_TAG.TAG_DT_SUPPRESSION IS NULL'
    );

    $oDeleteTag->execute([
        'TAG_CH_SUPPRESSION' => sSignature(),
        'TAG_N_ID' => $TAG_N_ID,
    ]);

    if ($oDeleteTag->rowCount() !== 1) {
        throw new RuntimeException('Tag introuvable');
    }

    $WM_ADMIN_conn->commit();

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    if (isset($WM_ADMIN_conn) && $WM_ADMIN_conn instanceof PDO && $WM_ADMIN_conn->inTransaction()) {
        $WM_ADMIN_conn->rollBack();
    }

    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
