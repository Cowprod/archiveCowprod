<?php
require_once __DIR__ . '/../../secure.php';
header('Content-Type: application/json; charset=utf-8');

try {
    $PRU_N_ID = (int) ($_POST['PRU_N_ID'] ?? 0);
    $sField = trim((string) ($_POST['sField'] ?? ''));
    $sValue = trim((string) ($_POST['sValue'] ?? ''));

    $aAllowed = [
        'UTY_N_ID' => 'type',
        'PRU_CH_URL' => 'url',
        'PRU_CH_LABEL' => 'text-null',
        'PRU_N_YEAR' => 'year-null',
    ];

    if ($PRU_N_ID <= 0 || !isset($aAllowed[$sField])) {
        throw new RuntimeException('URL invalide');
    }

    switch ($aAllowed[$sField]) {
        case 'type':
            $mValue = (int) $sValue;
            if ($mValue <= 0) {
                throw new RuntimeException('Type invalide');
            }
            break;
        case 'url':
            if ($sValue === '' || filter_var($sValue, FILTER_VALIDATE_URL) === false) {
                throw new RuntimeException('URL invalide');
            }
            $mValue = $sValue;
            break;
        case 'text-null':
            $mValue = $sValue === '' ? null : $sValue;
            break;
        case 'year-null':
            if ($sValue === '') {
                $mValue = null;
            } else {
                if (!ctype_digit($sValue) || (int) $sValue < 1900 || (int) $sValue > 2100) {
                    throw new RuntimeException('Année invalide');
                }
                $mValue = (int) $sValue;
            }
            break;
    }

    $WM_ADMIN_conn->beginTransaction();
    historiseTable('T_PROJECTURL', 'PRU', $PRU_N_ID, $WM_ADMIN_conn);

    $oUpdate = $WM_ADMIN_conn->prepare(
        'UPDATE T_PROJECTURL
         SET ' . $sField . ' = :sValue
         WHERE T_PROJECTURL.PRU_N_ID = :PRU_N_ID
           AND T_PROJECTURL.PRU_DT_SUPPRESSION IS NULL'
    );
    $oUpdate->bindValue(':sValue', $mValue, $mValue === null ? PDO::PARAM_NULL : (is_int($mValue) ? PDO::PARAM_INT : PDO::PARAM_STR));
    $oUpdate->bindValue(':PRU_N_ID', $PRU_N_ID, PDO::PARAM_INT);
    $oUpdate->execute();

    if ($oUpdate->rowCount() !== 1) {
        throw new RuntimeException('URL introuvable');
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
