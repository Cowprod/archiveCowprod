<?php
require_once __DIR__ . '/../secure.php';

$UTY_N_ID = '';
if (isset($_GET['UTY_N_ID']) && trim((string) $_GET['UTY_N_ID']) !== '') {
    $UTY_N_ID = decryptId($_GET['UTY_N_ID'], $sEncryptKey);
}

$sId = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($_GET['sId'] ?? 'UTY_N_ID'));
$sUpdateDiv = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($_GET['updateDiv'] ?? 'dDumy'));
$bSmall = isset($_GET['small']) && $_GET['small'] === '1';
$bRequired = isset($_GET['required']) && $_GET['required'] === '1';
$sField = preg_replace('/[^A-Za-z0-9_]/', '', (string) ($_GET['field'] ?? ''));

$sClass = 'form-select' . ($bSmall ? ' form-select-sm' : '');
if ($sField !== '') {
    $sClass .= ' js-url-change';
}

$sAttributes = '';
if ($bRequired) {
    $sAttributes .= ' required';
}
if ($sField !== '') {
    $sAttributes .= ' data-field="' . htmlspecialchars($sField, ENT_QUOTES, 'UTF-8') . '"';
}

$sReloadUrl = $_SERVER['REQUEST_URI'] ?? '/urlType/sel.php';
$sCallback = "updDiv('#" . $sUpdateDiv . "'," . json_encode($sReloadUrl) . ")";
?>
<?php if (!isset($_GET['noAdmin'])): ?><div class="input-group<?php echo $bSmall ? ' input-group-sm' : ''; ?> p-0 m-0 w-100"><?php endif; ?>
<?php
echo htmlSelectNameChange(
    'T_URLTYPE',
    'UTY_N_ID',
    'UTY_CH_LABEL',
    $UTY_N_ID,
    'UTY_CH_LABEL',
    'UTY_DT_SUPPRESSION IS NULL',
    $WM_ADMIN_conn,
    $sId,
    '',
    '',
    $sEncryptKey,
    $sClass,
    trim($sAttributes)
);
?>
<?php if (!isset($_GET['noAdmin'])): ?>
<button
    type="button"
    class="btn btn-light"
    title="Administrer les types d URL"
    onclick="bootBoxAdmin('Administrer les types d URL','/urlType/index.php',<?php echo htmlspecialchars(json_encode($sCallback), ENT_QUOTES, 'UTF-8'); ?>)"
><i class="fa fa-cog"></i></button>
<?php endif; ?>
<?php if (!isset($_GET['noAdmin'])): ?></div><?php endif; ?>
