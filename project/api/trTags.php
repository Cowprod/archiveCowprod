<?php

require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRO_N_ID = decryptId($_POST['PRO_N_ID'] ?? '', $sEncryptKey);
    $aWantedTagIds = $_POST['TAG_N_ID'] ?? [];

    if (!is_array($aWantedTagIds)) {
        $aWantedTagIds = [$aWantedTagIds];
    }

    $aWantedTagIds = array_values(array_unique(array_map(
        fn ($sId) => decryptId($sId, $sEncryptKey),
        array_filter($aWantedTagIds, fn ($sId) => trim((string) $sId) !== '')
    )));

    archiveAssertProject($WM_ADMIN_conn, $PRO_N_ID);

    if (count($aWantedTagIds) > 0) {
        $aPreparedTagIds = array_map('prepNum2Update', $aWantedTagIds);

        $aValidRows = $WM_ADMIN_conn->query(
            'SELECT T_TAG.TAG_N_ID
             FROM T_TAG
             INNER JOIN T_TAGCATEGORY
                ON T_TAGCATEGORY.TCA_N_ID=T_TAG.TCA_N_ID
               AND T_TAGCATEGORY.TCA_DT_SUPPRESSION IS NULL
             WHERE T_TAG.TAG_DT_SUPPRESSION IS NULL
               AND T_TAG.TAG_N_ID IN (' . implode(',', $aPreparedTagIds) . ')'
        )->fetchAll();

        $aValidTagIds = array_map('intval', array_column($aValidRows, 'TAG_N_ID'));
        sort($aWantedTagIds);
        sort($aValidTagIds);

        if ($aWantedTagIds !== $aValidTagIds) {
            throw new RuntimeException('Un ou plusieurs tags sont invalides');
        }
    }

    $aCurrentRows = $WM_ADMIN_conn->query(
        'SELECT PTA_N_ID,TAG_N_ID
         FROM T_PROJECTTAG
         WHERE PRO_N_ID=' . prepNum2Update($PRO_N_ID) . '
           AND PTA_DT_SUPPRESSION IS NULL'
    )->fetchAll();

    $aCurrentTagIds = array_map('intval', array_column($aCurrentRows, 'TAG_N_ID'));
    $aToAdd = array_values(array_diff($aWantedTagIds, $aCurrentTagIds));
    $aToRemove = array_values(array_diff($aCurrentTagIds, $aWantedTagIds));

    $WM_ADMIN_conn->beginTransaction();

    foreach ($aToAdd as $TAG_N_ID) {
        $WM_ADMIN_conn->exec(
            'INSERT INTO T_PROJECTTAG (
                PRO_N_ID,TAG_N_ID,PTA_DT_CREATION,PTA_CH_CREATION
             ) VALUES (
                ' . prepNum2Update($PRO_N_ID) . ',
                ' . prepNum2Update($TAG_N_ID) . ',
                NOW(),
                ' . prepString2Update(sSignature()) . '
             )'
        );
    }

    foreach ($aCurrentRows as $aCurrentRow) {
        if (!in_array((int) $aCurrentRow['TAG_N_ID'], $aToRemove, true)) {
            continue;
        }

        $PTA_N_ID = (int) $aCurrentRow['PTA_N_ID'];
        historiseTable('T_PROJECTTAG', 'PTA', $PTA_N_ID, $WM_ADMIN_conn);

        $WM_ADMIN_conn->exec(
            'UPDATE T_PROJECTTAG
             SET PTA_DT_SUPPRESSION=NOW(),
                 PTA_CH_SUPPRESSION=' . prepString2Update(sSignature()) . '
             WHERE PTA_N_ID=' . prepNum2Update($PTA_N_ID) . '
               AND PTA_DT_SUPPRESSION IS NULL'
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
