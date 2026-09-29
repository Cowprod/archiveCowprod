<?php

require_once __DIR__ . '/../../secure.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $PRO_N_ID = isset($_POST['PRO_N_ID']) ? (int) $_POST['PRO_N_ID'] : 0;
    $aWantedTagIds = $_POST['TAG_N_ID'] ?? [];

    if ($PRO_N_ID <= 0) {
        throw new RuntimeException('Projet invalide');
    }

    if (!is_array($aWantedTagIds)) {
        $aWantedTagIds = [$aWantedTagIds];
    }

    $aWantedTagIds = array_values(array_unique(array_filter(array_map('intval', $aWantedTagIds))));

    $oProject = $WM_ADMIN_conn->prepare(
        'SELECT T_PROJECT.PRO_N_ID
         FROM T_PROJECT
         WHERE T_PROJECT.PRO_N_ID = :PRO_N_ID
           AND T_PROJECT.PRO_DT_SUPPRESSION IS NULL'
    );

    $oProject->execute([
        'PRO_N_ID' => $PRO_N_ID,
    ]);

    if (!$oProject->fetchColumn()) {
        throw new RuntimeException('Projet introuvable');
    }

    if (count($aWantedTagIds) > 0) {
        $sPlaceholders = implode(',', array_fill(0, count($aWantedTagIds), '?'));

        $oValidTags = $WM_ADMIN_conn->prepare(
            'SELECT T_TAG.TAG_N_ID
             FROM T_TAG
             INNER JOIN T_TAGCATEGORY
                ON T_TAGCATEGORY.TCA_N_ID = T_TAG.TCA_N_ID
               AND T_TAGCATEGORY.TCA_DT_SUPPRESSION IS NULL
             WHERE T_TAG.TAG_DT_SUPPRESSION IS NULL
               AND T_TAG.TAG_N_ID IN (' . $sPlaceholders . ')'
        );

        $oValidTags->execute($aWantedTagIds);
        $aValidTagIds = array_map('intval', array_column($oValidTags->fetchAll(), 'TAG_N_ID'));

        sort($aWantedTagIds);
        sort($aValidTagIds);

        if ($aWantedTagIds !== $aValidTagIds) {
            throw new RuntimeException('Un ou plusieurs tags sont invalides');
        }
    }

    $oCurrent = $WM_ADMIN_conn->prepare(
        'SELECT
            T_PROJECTTAG.PTA_N_ID,
            T_PROJECTTAG.TAG_N_ID
         FROM T_PROJECTTAG
         WHERE T_PROJECTTAG.PRO_N_ID = :PRO_N_ID
           AND T_PROJECTTAG.PTA_DT_SUPPRESSION IS NULL'
    );

    $oCurrent->execute([
        'PRO_N_ID' => $PRO_N_ID,
    ]);

    $aCurrentRows = $oCurrent->fetchAll();
    $aCurrentTagIds = array_map('intval', array_column($aCurrentRows, 'TAG_N_ID'));

    $aToAdd = array_values(array_diff($aWantedTagIds, $aCurrentTagIds));
    $aToRemove = array_values(array_diff($aCurrentTagIds, $aWantedTagIds));

    $WM_ADMIN_conn->beginTransaction();

    if (count($aToAdd) > 0) {
        $oInsert = $WM_ADMIN_conn->prepare(
            'INSERT INTO T_PROJECTTAG (
                PRO_N_ID,
                TAG_N_ID,
                PTA_DT_CREATION,
                PTA_CH_CREATION
            ) VALUES (
                :PRO_N_ID,
                :TAG_N_ID,
                NOW(),
                :PTA_CH_CREATION
            )'
        );

        foreach ($aToAdd as $TAG_N_ID) {
            $oInsert->execute([
                'PRO_N_ID' => $PRO_N_ID,
                'TAG_N_ID' => $TAG_N_ID,
                'PTA_CH_CREATION' => sSignature(),
            ]);
        }
    }

    if (count($aToRemove) > 0) {
        foreach ($aCurrentRows as $aCurrentRow) {
            if (!in_array((int) $aCurrentRow['TAG_N_ID'], $aToRemove, true)) {
                continue;
            }

            $PTA_N_ID = (int) $aCurrentRow['PTA_N_ID'];

            historiseTable('T_PROJECTTAG', 'PTA', $PTA_N_ID, $WM_ADMIN_conn);

            $oDelete = $WM_ADMIN_conn->prepare(
                'UPDATE T_PROJECTTAG
                 SET
                    PTA_DT_SUPPRESSION = NOW(),
                    PTA_CH_SUPPRESSION = :PTA_CH_SUPPRESSION
                 WHERE T_PROJECTTAG.PTA_N_ID = :PTA_N_ID
                   AND T_PROJECTTAG.PTA_DT_SUPPRESSION IS NULL'
            );

            $oDelete->execute([
                'PTA_CH_SUPPRESSION' => sSignature(),
                'PTA_N_ID' => $PTA_N_ID,
            ]);
        }
    }

    $WM_ADMIN_conn->commit();

    echo json_encode([
        'success' => true,
    ]);
} catch (Throwable $e) {
    if (isset($WM_ADMIN_conn) && $WM_ADMIN_conn instanceof PDO && $WM_ADMIN_conn->inTransaction()) {
        $WM_ADMIN_conn->rollBack();
    }

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
    ]);
}
