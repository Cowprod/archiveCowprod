<?php

require_once __DIR__ . '/../secure.php';

$oInsert = $WM_ADMIN_conn->prepare(
    'INSERT INTO T_PROJECT (
        PRO_CH_LABEL,
        PRO_DT_CREATION,
        PRO_CH_CREATION
    ) VALUES (
        :PRO_CH_LABEL,
        NOW(),
        :PRO_CH_CREATION
    )'
);

$oInsert->execute([
    'PRO_CH_LABEL' => 'Nouveau projet',
    'PRO_CH_CREATION' => sSignature(),
]);

$PRO_N_ID = (int) $WM_ADMIN_conn->lastInsertId();

header('Location: /project/upd.php?PRO_N_ID=' . urlencode(encrypt((string) $PRO_N_ID, $sEncryptKey)));
exit;
