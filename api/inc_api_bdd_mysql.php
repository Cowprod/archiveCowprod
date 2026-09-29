<?php

function prepString2update($value): string
{
    return "'" . str_replace("'", "''", (string) $value) . "'";
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
    $sql_backup = 'SELECT * FROM ' . $sNomTable . ' WHERE ' . $sMem . '_N_ID=' . (int) $sValeur;
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
        . ' WHERE ' . $sMem . '_N_ID=' . (int) $sValeur;

    $oConn->exec($sql_COPIE);

    $IDbackup = getfield('max(' . $sMem . '_N_ID)', $sNomTable, '', $oConn);

    if ($IDbackup === null) {
        throw new RuntimeException('Impossible de récupérer la ligne historisée');
    }

    $sql_backup = 'UPDATE ' . $sNomTable
        . ' SET ' . $sMem . '_N_MID=' . (int) $sValeur
        . ', ' . $sMem . '_DT_SUPPRESSION=now()'
        . ', ' . $sMem . '_CH_SUPPRESSION=' . prepString2update(sSignature())
        . ' WHERE ' . $sMem . '_N_ID=' . (int) $IDbackup;

    $oConn->exec($sql_backup);
}
