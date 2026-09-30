<?php

require_once __DIR__ . '/../secure.php';
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

        $aValidRows = oRs(
            '',
            __DIR__ . '/tagValid.sql',
            'TAG_N_IDS=' . urlencode(implode(',', $aPreparedTagIds)),
            0,
            '',
            $WM_ADMIN_conn
        );

        $aValidTagIds = array_map('intval', array_column($aValidRows, 'TAG_N_ID'));
        sort($aWantedTagIds);
        sort($aValidTagIds);

        if ($aWantedTagIds !== $aValidTagIds) {
            throw new RuntimeException('Un ou plusieurs tags sont invalides');
        }
    }

    $aCurrentRows = oRs(
        '',
        __DIR__ . '/projectTagCurrent.sql',
        'PRO_N_ID=' . prepNum2Update($PRO_N_ID),
        0,
        '',
        $WM_ADMIN_conn
    );

    $aCurrentTagIds = array_map('intval', array_column($aCurrentRows, 'TAG_N_ID'));
    $aToAdd = array_values(array_diff($aWantedTagIds, $aCurrentTagIds));
    $aToRemove = array_values(array_diff($aCurrentTagIds, $aWantedTagIds));

    $WM_ADMIN_conn->beginTransaction();

    foreach ($aToAdd as $TAG_N_ID) {
        $PTA_N_ID = getIdConPdo('T_PROJECTTAG', 'PTA_CH_CREATION', 'temporaire', $WM_ADMIN_conn);

        $WM_ADMIN_conn->exec(
            'UPDATE T_PROJECTTAG SET '
            . 'PRO_N_ID=' . prepNum2Update($PRO_N_ID) . ','
            . 'TAG_N_ID=' . prepNum2Update($TAG_N_ID) . ','
            . 'PTA_DT_CREATION=NOW(),'
            . 'PTA_CH_CREATION=' . prepString2Update(sSignature())
            . ' WHERE PTA_N_ID=' . prepNum2Update($PTA_N_ID)
        );
    }

    foreach ($aCurrentRows as $aCurrentRow) {
        if (!in_array((int) $aCurrentRow['TAG_N_ID'], $aToRemove, true)) {
            continue;
        }

        $PTA_N_ID = (int) $aCurrentRow['PTA_N_ID'];

        $WM_ADMIN_conn->exec(
            'UPDATE T_PROJECTTAG SET '
            . 'PTA_DT_SUPPRESSION=NOW(),'
            . 'PTA_CH_SUPPRESSION=' . prepString2Update(sSignature())
            . ' WHERE PTA_N_ID=' . prepNum2Update($PTA_N_ID)
            . ' AND PTA_DT_SUPPRESSION IS NULL'
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
