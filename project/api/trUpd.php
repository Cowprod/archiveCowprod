<?php

require_once __DIR__ . '/../../secure.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $PRO_N_ID = decryptId($_POST['PRO_N_ID'] ?? '', $sEncryptKey);
    $sField = trim((string) ($_POST['sField'] ?? ''));
    $sValue = (string) ($_POST['sValue'] ?? '');

    if ($PRO_N_ID <= 0) {
        throw new RuntimeException('Projet invalide');
    }

    $aAllowedFields = [
        'PRO_CH_LABEL' => 'text',
        'PRO_TX_DESCRIPTION' => 'text-null',
        'PRO_N_YEARSTART' => 'year-null',
        'PRO_N_YEAREND' => 'year-null',
    ];

    if (!isset($aAllowedFields[$sField])) {
        throw new RuntimeException('Champ non autorisé');
    }

    $oExists = $WM_ADMIN_conn->prepare(
        'SELECT T_PROJECT.PRO_N_ID
         FROM T_PROJECT
         WHERE T_PROJECT.PRO_N_ID = :PRO_N_ID
           AND T_PROJECT.PRO_DT_SUPPRESSION IS NULL'
    );

    $oExists->execute([
        'PRO_N_ID' => $PRO_N_ID,
    ]);

    if (!$oExists->fetchColumn()) {
        throw new RuntimeException('Projet introuvable');
    }

    switch ($aAllowedFields[$sField]) {
        case 'text':
            $mValue = trim($sValue);

            if ($mValue === '') {
                throw new RuntimeException('Le nom du projet est obligatoire');
            }
            break;

        case 'text-null':
            $mValue = trim($sValue);
            $mValue = $mValue === '' ? null : $mValue;
            break;

        case 'year-null':
            $sValue = trim($sValue);

            if ($sValue === '') {
                $mValue = null;
                break;
            }

            if (!ctype_digit($sValue)) {
                throw new RuntimeException('Année invalide');
            }

            $mValue = (int) $sValue;

            if ($mValue < 1900 || $mValue > 2100) {
                throw new RuntimeException('Année invalide');
            }
            break;

        default:
            throw new RuntimeException('Type de champ non géré');
    }

    $WM_ADMIN_conn->beginTransaction();

    historiseTable('T_PROJECT', 'PRO', $PRO_N_ID, $WM_ADMIN_conn);

    $oUpdate = $WM_ADMIN_conn->prepare(
        'UPDATE T_PROJECT
         SET ' . $sField . ' = :sValue
         WHERE T_PROJECT.PRO_N_ID = :PRO_N_ID
           AND T_PROJECT.PRO_DT_SUPPRESSION IS NULL'
    );

    $oUpdate->bindValue(':sValue', $mValue, $mValue === null ? PDO::PARAM_NULL : (is_int($mValue) ? PDO::PARAM_INT : PDO::PARAM_STR));
    $oUpdate->bindValue(':PRO_N_ID', $PRO_N_ID, PDO::PARAM_INT);
    $oUpdate->execute();

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
