<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRO_N_ID = decryptId($_POST['PRO_N_ID'] ?? '', $sEncryptKey);
    $aIds = $_POST['PRU_N_ID'] ?? [];

    if (!is_array($aIds)) {
        throw new RuntimeException('Ordre des URLs invalide');
    }

    $aIds = array_values(array_unique(array_map(
        fn ($sId) => decryptId($sId, $sEncryptKey),
        $aIds
    )));

    $aRows = oRs(
        '',
        __DIR__ . '/../../sql/project/selectOrderUrls.sql',
        'PRO_N_ID=' . urlencode(prepNum2Update($PRO_N_ID)),
        0,
        '',
        $WM_ADMIN_conn
    );

    $aExisting = array_map('intval', array_column($aRows, 'PRU_N_ID'));
    $aCheckExisting = $aExisting;
    $aCheckIds = $aIds;
    sort($aCheckExisting);
    sort($aCheckIds);

    if ($aCheckExisting !== $aCheckIds) {
        throw new RuntimeException('Liste des URLs invalide');
    }

    $aCurrentOrder = [];
    foreach ($aRows as $aRow) {
        $aCurrentOrder[(int) $aRow['PRU_N_ID']] = (int) $aRow['PRU_N_ORDER'];
    }

    $WM_ADMIN_conn->beginTransaction();

    foreach ($aIds as $nIndex => $nId) {
        $nOrder = ($nIndex + 1) * 10;

        if (($aCurrentOrder[$nId] ?? null) === $nOrder) {
            continue;
        }

        historiseTable('T_PROJECTURL', 'PRU', $nId, $WM_ADMIN_conn);

        $WM_ADMIN_conn->exec(
            'UPDATE T_PROJECTURL
             SET PRU_N_ORDER=' . prepNum2Update($nOrder) . '
             WHERE PRU_N_ID=' . prepNum2Update($nId) . '
               AND PRU_DT_SUPPRESSION IS NULL'
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
