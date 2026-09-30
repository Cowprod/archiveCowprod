<?php

require_once __DIR__ . '/../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    if (!isset($_POST['PRU_N_ID']) || trim((string) $_POST['PRU_N_ID']) === '') {
        $PRO_N_ID = decryptId($_POST['PRO_N_ID'] ?? '', $sEncryptKey);
        $UTY_N_ID = decryptId($_POST['UTY_N_ID'] ?? '', $sEncryptKey);
        $sUrl = trim((string) ($_POST['PRU_CH_URL'] ?? ''));
        $sLabel = trim((string) ($_POST['PRU_CH_LABEL'] ?? ''));
        $sYear = trim((string) ($_POST['PRU_N_YEAR'] ?? ''));

        archiveAssertProject($WM_ADMIN_conn, $PRO_N_ID);

        if ((int) getfield(
            'count(*)',
            'T_URLTYPE',
            'WHERE UTY_N_ID=' . prepNum2Update($UTY_N_ID) . ' AND UTY_DT_SUPPRESSION IS NULL',
            $WM_ADMIN_conn
        ) !== 1) {
            throw new RuntimeException('Type d’URL introuvable');
        }

        if ($sUrl === '' || filter_var($sUrl, FILTER_VALIDATE_URL) === false) {
            throw new RuntimeException('URL invalide');
        }

        if ($sYear !== '' && (!ctype_digit($sYear) || (int) $sYear < 1900 || (int) $sYear > 2100)) {
            throw new RuntimeException('Année invalide');
        }

        $nOrder = (int) getfield(
            'COALESCE(MAX(PRU_N_ORDER),0)+10',
            'T_PROJECTURL',
            'WHERE PRO_N_ID=' . prepNum2Update($PRO_N_ID) . ' AND PRU_DT_SUPPRESSION IS NULL',
            $WM_ADMIN_conn
        );

        $PRU_N_ID = getIdConPdo('T_PROJECTURL', 'PRU_CH_CREATION', 'temporaire', $WM_ADMIN_conn);

        $WM_ADMIN_conn->exec(
            'UPDATE T_PROJECTURL SET '
            . 'PRO_N_ID=' . prepNum2Update($PRO_N_ID) . ','
            . 'UTY_N_ID=' . prepNum2Update($UTY_N_ID) . ','
            . 'PRU_CH_URL=' . prepString2Update($sUrl) . ','
            . 'PRU_CH_LABEL=' . ($sLabel === '' ? 'null' : prepString2Update($sLabel)) . ','
            . 'PRU_N_YEAR=' . prepNum2Update($sYear) . ','
            . 'PRU_N_ORDER=' . prepNum2Update($nOrder) . ','
            . 'PRU_DT_CREATION=NOW(),'
            . 'PRU_CH_CREATION=' . prepString2Update(sSignature())
            . ' WHERE PRU_N_ID=' . prepNum2Update($PRU_N_ID)
        );

        echo json_encode(['success' => true, 'PRU_N_ID' => $PRU_N_ID]);
        exit;
    }

    $PRU_N_ID = decryptId($_POST['PRU_N_ID'], $sEncryptKey);
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
            if ($sValue !== '' && (!ctype_digit($sValue) || (int) $sValue < 1900 || (int) $sValue > 2100)) {
                throw new RuntimeException('Année invalide');
            }

            $sPreparedValue = prepNum2Update($sValue);
            break;
    }

    $WM_ADMIN_conn->beginTransaction();
    historiseTable('T_PROJECTURL', 'PRU', $PRU_N_ID, $WM_ADMIN_conn);

    if ($WM_ADMIN_conn->exec(
        'UPDATE T_PROJECTURL SET ' . $sField . '=' . $sPreparedValue
        . ' WHERE PRU_N_ID=' . prepNum2Update($PRU_N_ID)
        . ' AND PRU_DT_SUPPRESSION IS NULL'
    ) !== 1) {
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
