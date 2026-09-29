<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRO_N_ID = (int) ($_POST['PRO_N_ID'] ?? 0);
    $aIds = $_POST['PRF_N_ID'] ?? [];

    if ($PRO_N_ID <= 0 || !is_array($aIds)) {
        throw new RuntimeException('Ordre des fichiers invalide');
    }

    $aIds = array_values(array_unique(array_map('intval', $aIds)));

    $oRows = $WM_ADMIN_conn->prepare(
        'SELECT T_PROJECTFILE.PRF_N_ID, T_PROJECTFILE.PRF_N_ORDER
         FROM T_PROJECTFILE
         WHERE T_PROJECTFILE.PRO_N_ID = :PRO_N_ID
           AND T_PROJECTFILE.PRF_DT_SUPPRESSION IS NULL
         ORDER BY T_PROJECTFILE.PRF_N_ORDER ASC, T_PROJECTFILE.PRF_N_ID ASC'
    );
    $oRows->execute(['PRO_N_ID' => $PRO_N_ID]);
    $aRows = $oRows->fetchAll();

    if (count($aRows) !== count($aIds)) {
        throw new RuntimeException('Liste des fichiers incomplète');
    }

    $aExisting = array_map('intval', array_column($aRows, 'PRF_N_ID'));
    $aCheckExisting = $aExisting;
    $aCheckIds = $aIds;
    sort($aCheckExisting);
    sort($aCheckIds);

    if ($aCheckExisting !== $aCheckIds) {
        throw new RuntimeException('Liste des fichiers invalide');
    }

    $aCurrentOrder = [];
    foreach ($aRows as $aRow) {
        $aCurrentOrder[(int) $aRow['PRF_N_ID']] = (int) $aRow['PRF_N_ORDER'];
    }

    $WM_ADMIN_conn->beginTransaction();

    foreach ($aIds as $nIndex => $PRF_N_ID) {
        $nOrder = ($nIndex + 1) * 10;

        if (($aCurrentOrder[$PRF_N_ID] ?? null) === $nOrder) {
            continue;
        }

        historiseTable('T_PROJECTFILE', 'PRF', $PRF_N_ID, $WM_ADMIN_conn);

        $oUpdate = $WM_ADMIN_conn->prepare(
            'UPDATE T_PROJECTFILE
             SET PRF_N_ORDER = :PRF_N_ORDER
             WHERE T_PROJECTFILE.PRF_N_ID = :PRF_N_ID
               AND T_PROJECTFILE.PRF_DT_SUPPRESSION IS NULL'
        );
        $oUpdate->execute([
            'PRF_N_ORDER' => $nOrder,
            'PRF_N_ID' => $PRF_N_ID,
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
