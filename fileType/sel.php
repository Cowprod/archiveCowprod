<?php
require_once __DIR__ . '/../secure.php';

$FTY_N_ID = '';
if (isset($_GET['FTY_N_ID']) && trim((string) $_GET['FTY_N_ID']) !== '') {
    $FTY_N_ID = decryptId($_GET['FTY_N_ID'], $sEncryptKey);
}

$sId = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($_GET['sId'] ?? 'FTY_N_ID'));
$sUpdateDiv = preg_replace('/[^A-Za-z0-9_-]/', '', (string) ($_GET['updateDiv'] ?? 'dDumy'));
$bSmall = isset($_GET['small']) && $_GET['small'] === '1';
$bRequired = isset($_GET['required']) && $_GET['required'] === '1';
$sField = preg_replace('/[^A-Za-z0-9_]/', '', (string) ($_GET['field'] ?? ''));

$sClass = 'form-select' . ($bSmall ? ' form-select-sm' : '');
if ($sField !== '') {
    $sClass .= ' js-file-change';
}

$sAttributes = '';
if ($bRequired) {
    $sAttributes .= ' required';
}
if ($sField !== '') {
    $sAttributes .= ' data-field="' . htmlspecialchars($sField, ENT_QUOTES, 'UTF-8') . '"';
}

$sReloadUrl = $_SERVER['REQUEST_URI'] ?? '/fileType/sel.php';
$sCallback = "updDiv('#" . $sUpdateDiv . "'," . json_encode($sReloadUrl) . ")";
?>
<?php if (!isset($_GET['noAdmin'])): ?><div class="input-group p-0 m-0 w-100"><?php endif; ?>
<?php
echo htmlSelectNameChange(
    'T_FILETYPE',
    'FTY_N_ID',
    'FTY_CH_LABEL',
    $FTY_N_ID,
    'FTY_CH_LABEL',
    'FTY_DT_SUPPRESSION IS NULL',
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
    title="Administrer les types de fichier"
    onclick="bootBoxAdmin('Administrer les types de fichier','/fileType/index.php',<?php echo htmlspecialchars(json_encode($sCallback), ENT_QUOTES, 'UTF-8'); ?>)"
><i class="fa fa-cog"></i></button>
<?php endif; ?>
<?php if (!isset($_GET['noAdmin'])): ?></div><?php endif; ?>
