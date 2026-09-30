<?php

require_once __DIR__ . '/../secure.php';

$sSql = 'INSERT INTO T_PROJECT (
    PRO_CH_LABEL,
    PRO_DT_CREATION,
    PRO_CH_CREATION
) VALUES (
    ' . prepString2Update('Nouveau projet') . ',
    NOW(),
    ' . prepString2Update(sSignature()) . '
)';

$WM_ADMIN_conn->exec($sSql);
$PRO_N_ID = (int) $WM_ADMIN_conn->lastInsertId();

header('Location: /project/upd.php?PRO_N_ID=' . urlencode(encrypt((string) $PRO_N_ID, $sEncryptKey)));
exit;
