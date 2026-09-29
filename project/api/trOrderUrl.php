<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRO_N_ID = (int) ($_POST['PRO_N_ID'] ?? 0);
    $aIds = $_POST['PRU_N_ID'] ?? [];

    if ($PRO_N_ID <= 0 || !is_array($aIds)) {
        throw new RuntimeException('Ordre des URLs invalide');
    }

    $aIds = array_values(array_unique(array_map('intval', $aIds)));

    $oRows = $WM_ADMIN_conn->prepare(
        'SELECT T_PROJECTURL.PRU_N_ID, T_PROJECTURL.PRU_N_ORDER
         FROM T_PROJECTURL
         WHERE T_PROJECTURL.PRO_N_ID = :PRO_N_ID
           AND T_PROJECTURL.PRU_DT_SUPPRESSION IS NULL
         ORDER BY T_PROJECTURL.PRU_N_ORDER ASC, T_PROJECTURL.PRU_N_ID ASC'
    );
    $oRows->execute(['PRO_N_ID' => $PRO_N_ID]);
    $aRows = $oRows->fetchAll();

    if (count($aRows) !== count($aIds)) {
        throw new RuntimeException('Liste des URLs incomplète');
    }

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

    foreach ($aIds as $nIndex => $PRU_N_ID) {
        $nOrder = ($nIndex + 1) * 10;

        if (($aCurrentOrder[$PRU_N_ID] ?? null) === $nOrder) {
            continue;
        }

        historiseTable('T_PROJECTURL', 'PRU', $PRU_N_ID, $WM_ADMIN_conn);

        $oUpdate = $WM_ADMIN_conn->prepare(
            'UPDATE T_PROJECTURL
             SET PRU_N_ORDER = :PRU_N_ORDER
             WHERE T_PROJECTURL.PRU_N_ID = :PRU_N_ID
               AND T_PROJECTURL.PRU_DT_SUPPRESSION IS NULL'
        );
        $oUpdate->execute([
            'PRU_N_ORDER' => $nOrder,
            'PRU_N_ID' => $PRU_N_ID,
        ]);
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
