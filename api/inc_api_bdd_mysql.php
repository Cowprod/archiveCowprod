<?php

function prepNum2Update($sNum)
{
    $sNum = str_replace(',', '.', (string) $sNum);

    if ($sNum === '' || !is_numeric($sNum)) {
        return 'null';
    }

    return str_replace(',', '.', $sNum);
}

function prepString2Update($sString)
{
    $sString = str_replace("\\", "\\\\", (string) $sString);
    $sString = "'" . str_replace("'", "''", $sString) . "'";
    return $sString;
}

function oRs($sSql, $sFichier, $sFiltre, $bDebug, $sOption, $oConnexion)
{
    if ($sSql === '') {
        if (!is_file($sFichier)) {
            throw new RuntimeException('Fichier SQL introuvable : ' . $sFichier);
        }
        $sSql = (string) file_get_contents($sFichier);
    }

    if (strlen((string) $sFiltre) > 0) {
        $aFiltre = explode('&', rtrim((string) $sFiltre, '&'));

        foreach ($aFiltre as $sFiltreLigne) {
            if ($sFiltreLigne === '') {
                continue;
            }

            $aFiltreLigne = explode('=', $sFiltreLigne, 2);
            $sFiltreVariable = $aFiltreLigne[0] ?? '';
            $sFiltreValeur = $aFiltreLigne[1] ?? '';
            $sFiltreValeur = str_replace('%%egal%%', '=', $sFiltreValeur);
            $sSql = str_replace('%%' . $sFiltreVariable . '%%', urldecode($sFiltreValeur), $sSql);
        }
    }

    if ($bDebug == 1) {
        echo '<div class="alert alert-info">';
        if ($sFichier !== '') {
            echo htmlspecialchars((string) $sFichier, ENT_QUOTES, 'UTF-8') . '<hr/>';
        }
        echo nl2br(htmlspecialchars($sSql, ENT_QUOTES, 'UTF-8'));
        echo '</div>';
    }

    return $oConnexion->query($sSql)->fetchAll(PDO::FETCH_ASSOC);
}

function getfield($field, $table, $filter, $connexion)
{
    $sql = 'SELECT ' . $field . ' AS v FROM ' . $table . ' ' . $filter . ' LIMIT 1';
    $stmt = $connexion->query($sql);
    $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : false;

    return $row['v'] ?? null;
}

function historiseTable($sNomTable, $sMem, $sValeur, &$oConn): void
{
    $sValeurPreparee = prepNum2Update($sValeur);

    if ($sValeurPreparee === 'null') {
        throw new RuntimeException('Identifiant invalide pour historisation');
    }

    $sql_backup = 'SELECT * FROM ' . $sNomTable . ' WHERE ' . $sMem . '_N_ID=' . $sValeurPreparee;
    $sSelect = '';
    $rs_backup = $oConn->query($sql_backup);

    if (!$rs_backup) {
        throw new RuntimeException('Impossible de lire la ligne à historiser');
    }

    for ($i = 0; $i < $rs_backup->columnCount(); $i++) {
        $aCol = $rs_backup->getColumnMeta($i);

        if (
            $aCol['name'] !== $sMem . '_N_ID'
            && $aCol['name'] !== 'article_id'
            && $aCol['name'] !== 'titre_id'
        ) {
            if ($sSelect !== '') {
                $sSelect .= ',';
            }
            $sSelect .= $aCol['name'];
        }
    }

    if ($sSelect === '') {
        throw new RuntimeException('Aucun champ historisable trouvé pour ' . $sNomTable);
    }

    $sql_COPIE = 'INSERT INTO ' . $sNomTable
        . ' (' . $sSelect . ') SELECT ' . $sSelect
        . ' FROM ' . $sNomTable
        . ' WHERE ' . $sMem . '_N_ID=' . $sValeurPreparee;

    $oConn->exec($sql_COPIE);

    $IDbackup = getfield('max(' . $sMem . '_N_ID)', $sNomTable, '', $oConn);

    if ($IDbackup === null) {
        throw new RuntimeException('Impossible de récupérer la ligne historisée');
    }

    $sql_backup = 'UPDATE ' . $sNomTable
        . ' SET ' . $sMem . '_N_MID=' . $sValeurPreparee
        . ', ' . $sMem . '_DT_SUPPRESSION=now()'
        . ', ' . $sMem . '_CH_SUPPRESSION=' . prepString2Update(sSignature())
        . ' WHERE ' . $sMem . '_N_ID=' . prepNum2Update($IDbackup);

    $oConn->exec($sql_backup);
}

function htmlSelectNameChange(
    $table,
    $valeur,
    $affichage,
    $valeurcle,
    $ordre,
    $filtre,
    &$con,
    $name,
    $script,
    $nTabIndex = '',
    $sEncryptKey = '',
    $sClass = 'form-select',
    $sAttributes = ''
) {
    $name = trim((string) $name);
    $sRetour = '<select';

    if ($nTabIndex !== '') {
        $sRetour .= ' tabindex="' . (int) $nTabIndex . '"';
    }

    $sRetour .= ' class="' . htmlspecialchars((string) $sClass, ENT_QUOTES, 'UTF-8')
        . '" name="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8')
        . '" id="' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . '"';

    if ($script !== '') {
        $sRetour .= ' onchange="' . htmlspecialchars((string) $script, ENT_QUOTES, 'UTF-8') . '"';
    }

    if ($sAttributes !== '') {
        $sRetour .= ' ' . $sAttributes;
    }

    $sRetour .= '>' . PHP_EOL;

    $sql = 'SELECT ' . $valeur . ' AS cledeselection, (' . $affichage . ') AS CONTENU FROM ' . $table;

    if ($filtre !== '') {
        $sql .= ' WHERE ' . $filtre;
    }

    if ($ordre !== '') {
        $sql .= ' ORDER BY ' . $ordre;
    }

    $rs = $con->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    $sRetour .= '<option value="null">↓</option>' . PHP_EOL;

    foreach ($rs as $row) {
        $sRawValue = (string) $row['cledeselection'];
        $sOptionValue = $sEncryptKey !== '' ? encrypt($sRawValue, $sEncryptKey) : $sRawValue;
        $bSelected = (string) $valeurcle === $sRawValue || (string) $valeurcle === $sOptionValue;

        $sRetour .= '<option' . ($bSelected ? ' selected' : '')
            . ' value="' . htmlspecialchars($sOptionValue, ENT_QUOTES, 'UTF-8') . '">'
            . htmlspecialchars((string) $row['CONTENU'], ENT_QUOTES, 'UTF-8')
            . '</option>' . PHP_EOL;
    }

    $sRetour .= '</select>';

    return $sRetour;
}
