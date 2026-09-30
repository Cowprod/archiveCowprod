<?php

require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRU_N_ID = decryptId($_POST['PRU_N_ID'] ?? '', $sEncryptKey);
    $sField = trim((string) ($_POST['sField'] ?? ''));
    $sValue = trim((string) ($_POST['sValue'] ?? ''));

    $aAllowed = [
        'UTY_N_ID' => 'type',
        'PRU_CH_URL' => 'url',
        'PRU_CH_LABEL' => 'text-null',
        'PRU_N_YEAR' => 'year-null',
    ];

    if (!isset($aAllowed[$sField])) {
        throw new RuntimeException('URL invalide');
    }

    switch ($aAllowed[$sField]) {
        case 'type':
            $UTY_N_ID = decryptId($sValue, $sEncryptKey);
            if ((int) getfield(
                'count(*)',
                'T_URLTYPE',
                'WHERE UTY_N_ID=' . prepNum2Update($UTY_N_ID) . ' AND UTY_DT_SUPPRESSION IS NULL',
                $WM_ADMIN_conn
            ) !== 1) {
                throw new RuntimeException('Type invalide');
            }
            $sPreparedValue = prepNum2Update($UTY_N_ID);
            break;

        case 'url':
            if ($sValue === '' || filter_var($sValue, FILTER_VALIDATE_URL) === false) {
                throw new RuntimeException('URL invalide');
            }
            $sPreparedValue = prepString2Update($sValue);
            break;

        case 'text-null':
            $sPreparedValue = $sValue === '' ? 'null' : prepString2Update($sValue);
            break;

        case 'year-null':
            if ($sValue === '') {
                $sPreparedValue = 'null';
            } else {
                if (!ctype_digit($sValue) || (int) $sValue < 1900 || (int) $sValue > 2100) {
                    throw new RuntimeException('Année invalide');
                }
                $sPreparedValue = prepNum2Update($sValue);
            }
            break;
    }

    $WM_ADMIN_conn->beginTransaction();
    historiseTable('T_PROJECTURL', 'PRU', $PRU_N_ID, $WM_ADMIN_conn);

    $sSql = 'UPDATE T_PROJECTURL
        SET ' . $sField . '=' . $sPreparedValue . '
        WHERE PRU_N_ID=' . prepNum2Update($PRU_N_ID) . '
          AND PRU_DT_SUPPRESSION IS NULL';

    if ($WM_ADMIN_conn->exec($sSql) !== 1) {
        throw new RuntimeException('URL introuvable');
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
