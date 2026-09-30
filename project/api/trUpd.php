<?php

require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRO_N_ID = decryptId($_POST['PRO_N_ID'] ?? '', $sEncryptKey);
    $sField = trim((string) ($_POST['sField'] ?? ''));
    $sValue = (string) ($_POST['sValue'] ?? '');

    $aAllowedFields = [
        'PRO_CH_LABEL' => 'text',
        'PRO_CH_DESCRIPTION' => 'text-null',
        'PRO_N_YEARSTART' => 'year-null',
        'PRO_N_YEAREND' => 'year-null',
    ];

    if (!isset($aAllowedFields[$sField])) {
        throw new RuntimeException('Champ non autorisé');
    }

    if ((int) getfield(
        'count(*)',
        'T_PROJECT',
        'WHERE PRO_N_ID=' . prepNum2Update($PRO_N_ID) . ' AND PRO_DT_SUPPRESSION IS NULL',
        $WM_ADMIN_conn
    ) !== 1) {
        throw new RuntimeException('Projet introuvable');
    }

    switch ($aAllowedFields[$sField]) {
        case 'text':
            $mValue = trim($sValue);
            if ($mValue === '') {
                throw new RuntimeException('Le nom du projet est obligatoire');
            }
            $sPreparedValue = prepString2Update($mValue);
            break;

        case 'text-null':
            $mValue = trim($sValue);
            $sPreparedValue = $mValue === '' ? 'null' : prepString2Update($mValue);
            break;

        case 'year-null':
            $sValue = trim($sValue);
            if ($sValue === '') {
                $sPreparedValue = 'null';
                break;
            }
            if (!ctype_digit($sValue) || (int) $sValue < 1900 || (int) $sValue > 2100) {
                throw new RuntimeException('Année invalide');
            }
            $sPreparedValue = prepNum2Update($sValue);
            break;
    }

    $WM_ADMIN_conn->beginTransaction();
    historiseTable('T_PROJECT', 'PRO', $PRO_N_ID, $WM_ADMIN_conn);

    $sSql = 'UPDATE T_PROJECT
        SET ' . $sField . '=' . $sPreparedValue . '
        WHERE PRO_N_ID=' . prepNum2Update($PRO_N_ID) . '
          AND PRO_DT_SUPPRESSION IS NULL';

    $WM_ADMIN_conn->exec($sSql);
    $WM_ADMIN_conn->commit();

    echo json_encode(['success' => true]);
} catch (Throwable $e) {
    if ($WM_ADMIN_conn->inTransaction()) {
        $WM_ADMIN_conn->rollBack();
    }
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
