<?php

require_once __DIR__ . '/../secure.php';

try {
    $PRO_N_ID = decryptId($_GET['PRO_N_ID'] ?? '', $sEncryptKey);
} catch (Throwable $e) {
    http_response_code(400);
    exit('Projet invalide');
}

if ($PRO_N_ID <= 0) {
    http_response_code(400);
    exit('Projet invalide');
}

$oProject = $WM_ADMIN_conn->prepare(
    'SELECT
        T_PROJECT.PRO_N_ID,
        T_PROJECT.PRO_CH_LABEL,
        T_PROJECT.PRO_TX_DESCRIPTION,
        T_PROJECT.PRO_N_YEARSTART,
        T_PROJECT.PRO_N_YEAREND
     FROM T_PROJECT
     WHERE T_PROJECT.PRO_N_ID = :PRO_N_ID
       AND T_PROJECT.PRO_DT_SUPPRESSION IS NULL'
);

$oProject->execute([
    'PRO_N_ID' => $PRO_N_ID,
]);

$aProject = $oProject->fetch();

if (!$aProject) {
    http_response_code(404);
    exit('Projet introuvable');
}

$oTags = $WM_ADMIN_conn->query(
    'SELECT
        T_TAGCATEGORY.TCA_N_ID,
        T_TAGCATEGORY.TCA_CH_LABEL,
        T_TAGCATEGORY.TCA_CH_COLOR,
        T_TAG.TAG_N_ID,
        T_TAG.TAG_CH_LABEL
     FROM T_TAGCATEGORY
     INNER JOIN T_TAG
        ON T_TAG.TCA_N_ID = T_TAGCATEGORY.TCA_N_ID
       AND T_TAG.TAG_DT_SUPPRESSION IS NULL
     WHERE T_TAGCATEGORY.TCA_DT_SUPPRESSION IS NULL
     ORDER BY
        T_TAGCATEGORY.TCA_N_ORDER ASC,
        T_TAGCATEGORY.TCA_CH_LABEL ASC,
        T_TAG.TAG_N_ORDER ASC,
        T_TAG.TAG_CH_LABEL ASC'
);

$aTagsByCategory = [];

foreach ($oTags->fetchAll() as $aTag) {
    $nCategoryId = (int) $aTag['TCA_N_ID'];

    if (!isset($aTagsByCategory[$nCategoryId])) {
        $aTagsByCategory[$nCategoryId] = [
            'label' => $aTag['TCA_CH_LABEL'],
            'color' => $aTag['TCA_CH_COLOR'],
            'tags' => [],
        ];
    }

    $aTagsByCategory[$nCategoryId]['tags'][] = $aTag;
}

$oProjectTags = $WM_ADMIN_conn->prepare(
    'SELECT T_PROJECTTAG.TAG_N_ID
     FROM T_PROJECTTAG
     WHERE T_PROJECTTAG.PRO_N_ID = :PRO_N_ID
       AND T_PROJECTTAG.PTA_DT_SUPPRESSION IS NULL'
);

$oProjectTags->execute([
    'PRO_N_ID' => $PRO_N_ID,
]);

$aSelectedTagIds = array_map(
    'intval',
    array_column($oProjectTags->fetchAll(), 'TAG_N_ID')
);

$oUrlTypes = $WM_ADMIN_conn->query(
    'SELECT
        T_URLTYPE.UTY_N_ID,
        T_URLTYPE.UTY_CH_LABEL
     FROM T_URLTYPE
     WHERE T_URLTYPE.UTY_DT_SUPPRESSION IS NULL
     ORDER BY T_URLTYPE.UTY_CH_LABEL ASC'
);
$aUrlTypes = $oUrlTypes->fetchAll();

$oProjectUrls = $WM_ADMIN_conn->prepare(
    'SELECT
        T_PROJECTURL.PRU_N_ID,
        T_PROJECTURL.UTY_N_ID,
        T_PROJECTURL.PRU_CH_URL,
        T_PROJECTURL.PRU_CH_LABEL,
        T_PROJECTURL.PRU_N_YEAR
     FROM T_PROJECTURL
     WHERE T_PROJECTURL.PRO_N_ID = :PRO_N_ID
       AND T_PROJECTURL.PRU_DT_SUPPRESSION IS NULL
     ORDER BY
        T_PROJECTURL.PRU_N_ORDER ASC,
        T_PROJECTURL.PRU_N_ID ASC'
);
$oProjectUrls->execute(['PRO_N_ID' => $PRO_N_ID]);
$aProjectUrls = $oProjectUrls->fetchAll();

$aFileTypes = $WM_ADMIN_conn->query(
    'SELECT T_FILETYPE.FTY_N_ID, T_FILETYPE.FTY_CH_LABEL
     FROM T_FILETYPE
     WHERE T_FILETYPE.FTY_DT_SUPPRESSION IS NULL
     ORDER BY T_FILETYPE.FTY_CH_LABEL ASC'
)->fetchAll();

$oProjectFiles = $WM_ADMIN_conn->prepare(
    'SELECT
        T_PROJECTFILE.PRF_N_ID,
        T_PROJECTFILE.FTY_N_ID,
        T_PROJECTFILE.PRF_CH_LABEL,
        T_PROJECTFILE.PRF_CH_FILENAME,
        T_PROJECTFILE.PRF_CH_MIMETYPE,
        T_PROJECTFILE.PRF_N_SIZE,
        T_PROJECTFILE.PRF_N_YEAR,
        T_PROJECTFILE.PRF_BL_SEARCHIMAGE
     FROM T_PROJECTFILE
     WHERE T_PROJECTFILE.PRO_N_ID = :PRO_N_ID
       AND T_PROJECTFILE.PRF_DT_SUPPRESSION IS NULL
     ORDER BY T_PROJECTFILE.PRF_N_ORDER ASC, T_PROJECTFILE.PRF_N_ID ASC'
);
$oProjectFiles->execute(['PRO_N_ID' => $PRO_N_ID]);
$aProjectFiles = $oProjectFiles->fetchAll();

require_once __DIR__ . '/../top.php';
?>

<main class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <div>
            <a href="/index.php" class="text-decoration-none">
                <i class="fa fa-arrow-left me-2"></i>Catalogue
            </a>
            <h1 class="h3 mt-2 mb-0"><?php echo htmlspecialchars($aProject['PRO_CH_LABEL'], ENT_QUOTES, 'UTF-8'); ?></h1>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span id="dSaveStatus" class="text-body-secondary"></span>
            <button type="button" class="btn btn-danger btn-sm" id="bDeleteProject">
                <i class="fa fa-trash me-2"></i>Supprimer
            </button>
        </div>
    </div>

    <input type="hidden" id="PRO_N_ID" value="<?php echo htmlspecialchars(encrypt((string) $aProject['PRO_N_ID'], $sEncryptKey), ENT_QUOTES, 'UTF-8'); ?>">

    <div class="card mb-4">
        <div class="card-header">Projet</div>
        <div class="card-body">
            <div class="mb-3">
                <label for="PRO_CH_LABEL" class="form-label">Nom</label>
                <input
                    type="text"
                    class="form-control js-autosave-text"
                    id="PRO_CH_LABEL"
                    data-field="PRO_CH_LABEL"
                    value="<?php echo htmlspecialchars((string) $aProject['PRO_CH_LABEL'], ENT_QUOTES, 'UTF-8'); ?>"
                >
            </div>

            <div class="row g-3 mb-3">
                <div class="col-sm-6 col-lg-3">
                    <label for="PRO_N_YEARSTART" class="form-label">Année de début</label>
                    <input
                        type="number"
                        min="1900"
                        max="2100"
                        class="form-control js-autosave-change"
                        id="PRO_N_YEARSTART"
                        data-field="PRO_N_YEARSTART"
                        value="<?php echo htmlspecialchars((string) ($aProject['PRO_N_YEARSTART'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>

                <div class="col-sm-6 col-lg-3">
                    <label for="PRO_N_YEAREND" class="form-label">Année de fin</label>
                    <input
                        type="number"
                        min="1900"
                        max="2100"
                        class="form-control js-autosave-change"
                        id="PRO_N_YEAREND"
                        data-field="PRO_N_YEAREND"
                        value="<?php echo htmlspecialchars((string) ($aProject['PRO_N_YEAREND'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                    >
                </div>
            </div>

            <div>
                <label for="PRO_TX_DESCRIPTION" class="form-label">Description</label>
                <textarea
                    class="form-control js-autosave-text"
                    id="PRO_TX_DESCRIPTION"
                    data-field="PRO_TX_DESCRIPTION"
                    rows="8"
                ><?php echo htmlspecialchars((string) ($aProject['PRO_TX_DESCRIPTION'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">Tags</div>
        <div class="card-body">
            <div class="d-flex align-items-stretch tag-select-group">
                <select id="TAG_N_ID" class="form-select" multiple>
                    <?php foreach ($aTagsByCategory as $aCategory): ?>
                        <optgroup label="<?php echo htmlspecialchars($aCategory['label'], ENT_QUOTES, 'UTF-8'); ?>">
                            <?php foreach ($aCategory['tags'] as $aTag): ?>
                                <option
                                    value="<?php echo htmlspecialchars(encrypt((string) $aTag['TAG_N_ID'], $sEncryptKey), ENT_QUOTES, 'UTF-8'); ?>"
                                    data-color="<?php echo htmlspecialchars($aCategory['color'], ENT_QUOTES, 'UTF-8'); ?>"
                                    <?php echo in_array((int) $aTag['TAG_N_ID'], $aSelectedTagIds, true) ? 'selected' : ''; ?>
                                >
                                    <?php echo htmlspecialchars($aTag['TAG_CH_LABEL'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </optgroup>
                    <?php endforeach; ?>
                </select>
                <button
                    type="button"
                    class="btn btn-light tag-admin-button"
                    id="bAdminTags"
                    title="Administrer les tags"
                >
                    <i class="fa fa-cog"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">URLs</div>
        <div class="card-body">
            <form id="fAddUrl" class="mb-3">
                <div class="row g-2">
                    <div class="col-md-3">
                        <div class="input-group"><select class="form-select" name="UTY_N_ID" required>
                            <option value="">Type</option>
                            <?php foreach ($aUrlTypes as $aUrlType): ?>
                                <option value="<?php echo htmlspecialchars(encrypt((string) $aUrlType['UTY_N_ID'], $sEncryptKey), ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php echo htmlspecialchars($aUrlType['UTY_CH_LABEL'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                            <button type="button" class="btn btn-light" id="bAdminUrlTypes" title="Administrer les types d’URL"><i class="fa fa-cog"></i></button>
                        </div>
                    </div>
                    <div class="col-md">
                        <input type="url" class="form-control" name="PRU_CH_URL" placeholder="https://…" required>
                    </div>
                    <div class="col-md-3">
                        <input type="text" class="form-control" name="PRU_CH_LABEL" placeholder="Libellé facultatif">
                    </div>
                    <div class="col-md-2">
                        <input type="number" min="1900" max="2100" class="form-control" name="PRU_N_YEAR" placeholder="Année">
                    </div>
                    <div class="col-auto">
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-plus-circle me-2"></i>Ajouter
                        </button>
                    </div>
                </div>
            </form>

            <?php if (count($aProjectUrls) > 0): ?>
                <table class="table table-bordered table-striped table-sm align-middle mb-0">
                    <tbody id="tUrlBody">
                        <?php foreach ($aProjectUrls as $aProjectUrl): ?>
                            <tr data-url-id="<?php echo htmlspecialchars(encrypt((string) $aProjectUrl['PRU_N_ID'], $sEncryptKey), ENT_QUOTES, 'UTF-8'); ?>">
                                <td class="text-center js-drag-url" style="width:38px;cursor:move;" title="Déplacer"><i class="fa fa-grip-vertical text-body-secondary"></i></td>
                                <td class="text-center" style="width:50px;">
                                    <button type="button" class="btn btn-danger btn-sm js-delete-url" title="Supprimer">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                                <td style="width:180px;">
                                    <select class="form-select form-select-sm js-url-change" data-field="UTY_N_ID">
                                        <?php foreach ($aUrlTypes as $aUrlType): ?>
                                            <option
                                                value="<?php echo htmlspecialchars(encrypt((string) $aUrlType['UTY_N_ID'], $sEncryptKey), ENT_QUOTES, 'UTF-8'); ?>"
                                                <?php echo (int) $aProjectUrl['UTY_N_ID'] === (int) $aUrlType['UTY_N_ID'] ? 'selected' : ''; ?>
                                            >
                                                <?php echo htmlspecialchars($aUrlType['UTY_CH_LABEL'], ENT_QUOTES, 'UTF-8'); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                                <td>
                                    <input
                                        type="url"
                                        class="form-control form-control-sm js-url-text"
                                        data-field="PRU_CH_URL"
                                        value="<?php echo htmlspecialchars($aProjectUrl['PRU_CH_URL'], ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </td>
                                <td style="width:240px;">
                                    <input
                                        type="text"
                                        class="form-control form-control-sm js-url-text"
                                        data-field="PRU_CH_LABEL"
                                        placeholder="Libellé"
                                        value="<?php echo htmlspecialchars((string) ($aProjectUrl['PRU_CH_LABEL'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </td>
                                <td style="width:100px;">
                                    <input
                                        type="number"
                                        min="1900"
                                        max="2100"
                                        class="form-control form-control-sm js-url-change"
                                        data-field="PRU_N_YEAR"
                                        placeholder="Année"
                                        value="<?php echo htmlspecialchars((string) ($aProjectUrl['PRU_N_YEAR'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </td>
                                <td class="text-center" style="width:50px;">
                                    <a
                                        class="btn btn-light btn-sm"
                                        href="<?php echo htmlspecialchars($aProjectUrl['PRU_CH_URL'], ENT_QUOTES, 'UTF-8'); ?>"
                                        target="_blank"
                                        rel="noopener"
                                        title="Ouvrir"
                                    >
                                        <i class="fa fa-external-link"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="text-body-secondary">Aucune URL.</div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Fichiers</span>
            <small class="text-body-secondary">Glisser-déposer ou coller une image avec Cmd/Ctrl+V</small>
        </div>
        <div class="card-body">
            <form id="fAddFile" enctype="multipart/form-data" class="mb-3">
                <div class="row g-2 align-items-stretch">
                    <div class="col-md-3">
                        <div class="input-group h-100">
                            <select class="form-select" name="FTY_N_ID" id="FTY_N_ID_ADD" required>
                                <option value="">Type</option>
                                <?php foreach ($aFileTypes as $aFileType): ?>
                                    <option value="<?php echo htmlspecialchars(encrypt((string) $aFileType['FTY_N_ID'], $sEncryptKey), ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($aFileType['FTY_CH_LABEL'], ENT_QUOTES, 'UTF-8'); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="button" class="btn btn-light" id="bAdminFileTypes" title="Administrer les types de fichier"><i class="fa fa-cog"></i></button>
                        </div>
                    </div>
                    <div class="col-md">
                        <input type="file" class="form-control h-100" name="file" id="PRF_FILE" required>
                    </div>
                    <div class="col-md-2"><input type="text" class="form-control h-100" name="PRF_CH_LABEL" placeholder="Libellé"></div>
                    <div class="col-md-2"><input type="number" min="1900" max="2100" class="form-control h-100" name="PRF_N_YEAR" placeholder="Année"></div>
                    <div class="col-auto"><button type="submit" class="btn btn-success h-100"><i class="fa fa-upload me-2"></i>Ajouter</button></div>
                </div>
            </form>

            <div id="dFileDrop" class="border border-light rounded p-3 mb-3 text-center text-body-secondary" tabindex="0">
                <div>Déposer des fichiers ici ou coller une image depuis le presse-papiers</div>
                <button type="button" class="btn btn-light btn-sm mt-2" id="bPasteImage">
                    <i class="fa fa-clipboard me-2"></i>Coller l’image
                </button>
            </div>

            <?php if (count($aProjectFiles) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-bordered table-striped table-sm align-middle mb-0">
                        <tbody id="tFileBody">
                        <?php foreach ($aProjectFiles as $aProjectFile): ?>
                            <?php $bImage = str_starts_with((string) $aProjectFile['PRF_CH_MIMETYPE'], 'image/'); ?>
                            <tr data-file-id="<?php echo htmlspecialchars(encrypt((string) $aProjectFile['PRF_N_ID'], $sEncryptKey), ENT_QUOTES, 'UTF-8'); ?>">
                                <td class="text-center js-drag-file" style="width:38px;cursor:move;" title="Déplacer"><i class="fa fa-grip-vertical text-body-secondary"></i></td>
                                <td class="text-center" style="width:50px"><button type="button" class="btn btn-danger btn-sm js-delete-file"><i class="fa fa-trash"></i></button></td>
                                <td class="text-center" style="width:110px">
                                    <?php if ($bImage): ?>
                                        <a href="/file.php?PRF_N_ID=<?php echo urlencode(encrypt((string) $aProjectFile['PRF_N_ID'], $sEncryptKey)); ?>" target="_blank">
                                            <img src="/file.php?PRF_N_ID=<?php echo urlencode(encrypt((string) $aProjectFile['PRF_N_ID'], $sEncryptKey)); ?>&thumb=1" class="img-fluid rounded" style="max-height:70px" alt="">
                                        </a>
                                    <?php else: ?>
                                        <a class="btn btn-light btn-sm" href="/file.php?PRF_N_ID=<?php echo urlencode(encrypt((string) $aProjectFile['PRF_N_ID'], $sEncryptKey)); ?>" target="_blank"><i class="fa fa-file"></i></a>
                                    <?php endif; ?>
                                </td>
                                <td style="width:170px"><select class="form-select form-select-sm js-file-change" data-field="FTY_N_ID"><?php foreach ($aFileTypes as $aFileType): ?><option value="<?php echo htmlspecialchars(encrypt((string) $aFileType['FTY_N_ID'], $sEncryptKey), ENT_QUOTES, 'UTF-8'); ?>" <?php echo (int)$aProjectFile['FTY_N_ID']===(int)$aFileType['FTY_N_ID']?'selected':''; ?>><?php echo htmlspecialchars($aFileType['FTY_CH_LABEL'],ENT_QUOTES,'UTF-8'); ?></option><?php endforeach; ?></select></td>
                                <td><input type="text" class="form-control form-control-sm js-file-text" data-field="PRF_CH_LABEL" placeholder="<?php echo htmlspecialchars($aProjectFile['PRF_CH_FILENAME'],ENT_QUOTES,'UTF-8'); ?>" value="<?php echo htmlspecialchars((string)($aProjectFile['PRF_CH_LABEL']??''),ENT_QUOTES,'UTF-8'); ?>"><small class="text-body-secondary"><?php echo htmlspecialchars($aProjectFile['PRF_CH_FILENAME'],ENT_QUOTES,'UTF-8'); ?> · <?php echo number_format(((int)$aProjectFile['PRF_N_SIZE'])/1024,0,',',' '); ?> Ko</small></td>
                                <td style="width:100px"><input type="number" min="1900" max="2100" class="form-control form-control-sm js-file-change" data-field="PRF_N_YEAR" placeholder="Année" value="<?php echo htmlspecialchars((string)($aProjectFile['PRF_N_YEAR']??''),ENT_QUOTES,'UTF-8'); ?>"></td>
                                <td class="text-center" style="width:90px">
                                    <?php if ($bImage): ?>
                                        <div class="form-check d-inline-block" title="Image du catalogue"><input class="form-check-input js-search-image" type="radio" name="PRF_BL_SEARCHIMAGE" value="<?php echo htmlspecialchars(encrypt((string) $aProjectFile['PRF_N_ID'], $sEncryptKey), ENT_QUOTES, 'UTF-8'); ?>" <?php echo (int)$aProjectFile['PRF_BL_SEARCHIMAGE']===1?'checked':''; ?>></div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?><div class="text-body-secondary">Aucun fichier.</div><?php endif; ?>
        </div>
    </div>
    <div class="modal fade" id="mAdminFileTypes" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
            <div class="modal-header"><h2 class="modal-title fs-5">Administrer les types de fichier</h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-0"><iframe id="fAdminFileTypes" src="about:blank" style="width:100%;height:60vh;border:0;"></iframe></div>
        </div></div>
    </div>

    <div class="modal fade" id="mAdminUrlTypes" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content">
            <div class="modal-header"><h2 class="modal-title fs-5">Administrer les types d’URL</h2><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body p-0"><iframe id="fAdminUrlTypes" src="about:blank" style="width:100%;height:60vh;border:0;"></iframe></div>
        </div></div>
    </div>

    <div class="modal fade" id="mAdminTags" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title fs-5">Administrer les tags</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body p-0">
                    <iframe
                        id="fAdminTags"
                        src="about:blank"
                        title="Administration des tags"
                        style="width:100%;height:70vh;border:0;"
                    ></iframe>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="/assets/js/jquery.typing-0.2.0.js"></script>
<script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js"></script>
<script>
$(function () {
    let nPendingSave = 0;

    function setSaveStatus(sStatus, bError) {
        $('#dSaveStatus')
            .toggleClass('text-danger', bError === true)
            .toggleClass('text-body-secondary', bError !== true)
            .text(sStatus);
    }

    function setFieldState($field, sState) {
        const $target = $field.hasClass('select2-hidden-accessible')
            ? $field.next('.select2-container').find('.select2-selection')
            : $field;

        clearTimeout($field.data('autosave-state-timer'));
        $target.removeClass('autosave-warning autosave-success autosave-danger');

        if (sState === 'warning') {
            $target.addClass('autosave-warning');
        } else if (sState === 'success') {
            $target.addClass('autosave-success');

            $field.data('autosave-state-timer', setTimeout(function () {
                $target.removeClass('autosave-success');
            }, 1500));
        } else if (sState === 'danger') {
            $target.addClass('autosave-danger');
        }
    }

    function saveField($field) {
        const sField = $field.data('field');
        const sValue = $field.val();

        nPendingSave++;
        setFieldState($field, 'warning');
        setSaveStatus('Enregistrement…', false);

        $.ajax({
            url: '/project/api/trUpd.php',
            type: 'POST',
            dataType: 'json',
            data: {
                PRO_N_ID: $('#PRO_N_ID').val(),
                sField: sField,
                sValue: sValue
            }
        })
        .done(function (data) {
            if (data.success !== true) {
                setFieldState($field, 'danger');
                setSaveStatus(data.message || 'Erreur', true);
                return;
            }

            setFieldState($field, 'success');

            if (sField === 'PRO_CH_LABEL') {
                $('h1').text(sValue || 'Projet');
            }
        })
        .fail(function (xhr) {
            let sMessage = 'Erreur de sauvegarde';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                sMessage = xhr.responseJSON.message;
            }

            setFieldState($field, 'danger');
            setFieldState($field, 'danger');
            setSaveStatus(sMessage, true);
        })
        .always(function () {
            nPendingSave--;

            if (nPendingSave === 0 && !$('#dSaveStatus').hasClass('text-danger')) {
                setSaveStatus('Enregistré', false);
            }
        });
    }

    $('.js-autosave-text').typing({
        delay: 600,
        start: function (event, $elem) {
            setFieldState($elem, 'warning');
            setSaveStatus('Modification…', false);
        },
        stop: function (event, $elem) {
            saveField($elem);
        }
    });

    $('.js-autosave-change').on('change', function () {
        setFieldState($(this), 'warning');
        saveField($(this));
    });

    function formatTag(state) {
        if (!state.id) {
            return state.text;
        }

        const sColor = $(state.element).data('color') || 'secondary';
        return $('<span class="badge text-bg-' + sColor + '"></span>').text(state.text);
    }

    $('#TAG_N_ID').select2({
        width: '100%',
        placeholder: 'Ajouter des tags',
        templateSelection: formatTag
    });

    $('#TAG_N_ID').on('change', function () {
        const $field = $(this);

        nPendingSave++;
        setFieldState($field, 'warning');
        setSaveStatus('Enregistrement…', false);

        $.ajax({
            url: '/project/api/trTags.php',
            type: 'POST',
            dataType: 'json',
            data: {
                PRO_N_ID: $('#PRO_N_ID').val(),
                TAG_N_ID: $(this).val() || []
            }
        })
        .done(function (data) {
            if (data.success !== true) {
                setFieldState($field, 'danger');
                setSaveStatus(data.message || 'Erreur', true);
                return;
            }

            setFieldState($field, 'success');
        })
        .fail(function (xhr) {
            let sMessage = 'Erreur de sauvegarde des tags';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                sMessage = xhr.responseJSON.message;
            }

            setSaveStatus(sMessage, true);
        })
        .always(function () {
            nPendingSave--;

            if (nPendingSave === 0 && !$('#dSaveStatus').hasClass('text-danger')) {
                setSaveStatus('Enregistré', false);
            }
        });
    });

    $('#bAdminTags').on('click', function () {
        $('#fAdminTags').attr('src', '/tag/index.php');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('mAdminTags')).show();
    });

    document.getElementById('mAdminTags').addEventListener('hidden.bs.modal', function () {
        window.location.reload();
    });

    function saveResourceOrder(sType) {
        const bUrl = sType === 'url';
        const $body = bUrl ? $('#tUrlBody') : $('#tFileBody');
        const sDataName = bUrl ? 'url-id' : 'file-id';
        const sParam = bUrl ? 'PRU_N_ID[]' : 'PRF_N_ID[]';
        const sUrl = bUrl ? '/project/api/trOrderUrl.php' : '/project/api/trOrderFile.php';
        const aData = [{name:'PRO_N_ID', value:$('#PRO_N_ID').val()}];

        $body.children('tr').each(function () {
            aData.push({name:sParam, value:$(this).data(sDataName)});
        });

        setSaveStatus('Enregistrement de l’ordre…', false);

        $.ajax({url:sUrl,type:'POST',dataType:'json',data:aData})
        .done(function(data){
            if(data.success===true){setSaveStatus('Enregistré',false);return;}
            setSaveStatus(data.message||'Erreur lors du classement',true);
        })
        .fail(function(xhr){
            setSaveStatus(xhr.responseJSON&&xhr.responseJSON.message?xhr.responseJSON.message:'Erreur lors du classement',true);
        });
    }

    if ($('#tUrlBody').length) {
        $('#tUrlBody').sortable({
            axis:'y',
            handle:'.js-drag-url',
            helper:function(e,tr){
                const $originals=tr.children();
                const $helper=tr.clone();
                $helper.children().each(function(index){$(this).width($originals.eq(index).width());});
                return $helper;
            },
            update:function(){saveResourceOrder('url');}
        });
    }

    if ($('#tFileBody').length) {
        $('#tFileBody').sortable({
            axis:'y',
            handle:'.js-drag-file',
            helper:function(e,tr){
                const $originals=tr.children();
                const $helper=tr.clone();
                $helper.children().each(function(index){$(this).width($originals.eq(index).width());});
                return $helper;
            },
            update:function(){saveResourceOrder('file');}
        });
    }

    $('#bAdminUrlTypes').on('click', function () {
        $('#fAdminUrlTypes').attr('src', '/urlType/index.php');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('mAdminUrlTypes')).show();
    });
    document.getElementById('mAdminUrlTypes').addEventListener('hidden.bs.modal', function () { window.location.reload(); });

    function saveUrl($field) {
        const $row = $field.closest('[data-url-id]');

        nPendingSave++;
        setFieldState($field, 'warning');
        setSaveStatus('Enregistrement…', false);

        $.ajax({
            url: '/project/api/trUpdUrl.php',
            type: 'POST',
            dataType: 'json',
            data: {
                PRU_N_ID: $row.data('url-id'),
                sField: $field.data('field'),
                sValue: $field.val()
            }
        })
        .done(function (data) {
            if (data.success !== true) {
                setFieldState($field, 'danger');
                setSaveStatus(data.message || 'Erreur', true);
                return;
            }

            setFieldState($field, 'success');
        })
        .fail(function (xhr) {
            setFieldState($field, 'danger');
            setSaveStatus(
                xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Erreur de sauvegarde de l’URL',
                true
            );
        })
        .always(function () {
            nPendingSave--;

            if (nPendingSave === 0 && !$('#dSaveStatus').hasClass('text-danger')) {
                setSaveStatus('Enregistré', false);
            }
        });
    }

    $('.js-url-text').typing({
        delay: 600,
        start: function (event, $elem) {
            setFieldState($elem, 'warning');
        },
        stop: function (event, $elem) {
            saveUrl($elem);
        }
    });

    $('.js-url-change').on('change', function () {
        setFieldState($(this), 'warning');
        saveUrl($(this));
    });

    $('#fAddUrl').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);

        $.ajax({
            url: '/project/api/trAddUrl.php',
            type: 'POST',
            dataType: 'json',
            data: $form.serialize() + '&PRO_N_ID=' + encodeURIComponent($('#PRO_N_ID').val())
        })
        .done(function (data) {
            if (data.success === true) {
                window.location.reload();
                return;
            }

            setSaveStatus(data.message || 'Erreur', true);
        })
        .fail(function (xhr) {
            setSaveStatus(
                xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Erreur lors de l’ajout de l’URL',
                true
            );
        });
    });

    $('.js-delete-url').on('click', function () {
        const $row = $(this).closest('[data-url-id]');

        if (!confirm('Supprimer cette URL ?')) {
            return;
        }

        $.ajax({
            url: '/project/api/trDeleteUrl.php',
            type: 'POST',
            dataType: 'json',
            data: {
                PRU_N_ID: $row.data('url-id')
            }
        })
        .done(function (data) {
            if (data.success === true) {
                $row.remove();
                return;
            }

            setSaveStatus(data.message || 'Erreur', true);
        })
        .fail(function (xhr) {
            setSaveStatus(
                xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Erreur lors de la suppression de l’URL',
                true
            );
        });
    });

    $('#bAdminFileTypes').on('click', function () {
        $('#fAdminFileTypes').attr('src', '/fileType/index.php');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('mAdminFileTypes')).show();
    });
    document.getElementById('mAdminFileTypes').addEventListener('hidden.bs.modal', function () { window.location.reload(); });

    function getClipboardFileType() {
        let nType = $('#FTY_N_ID_ADD').val();

        if (nType) {
            return nType;
        }

        $('#FTY_N_ID_ADD option').each(function () {
            const sLabel = $(this).text().trim().toLowerCase();

            if (!nType && (sLabel === 'screenshot' || sLabel === 'image')) {
                nType = $(this).val();
            }
        });

        if (nType) {
            $('#FTY_N_ID_ADD').val(nType);
        }

        return nType;
    }

    function uploadFile(oFile, bClipboard) {
        const nType = bClipboard ? getClipboardFileType() : $('#FTY_N_ID_ADD').val();

        if (!nType) {
            setSaveStatus('Choisir un type de fichier avant l’envoi', true);
            $('#FTY_N_ID_ADD').addClass('autosave-warning');
            return;
        }
        const fd = new FormData();
        fd.append('PRO_N_ID', $('#PRO_N_ID').val());
        fd.append('FTY_N_ID', nType);
        fd.append('file', oFile, oFile.name || 'presse-papiers.png');

        setSaveStatus('Envoi du fichier…', false);
        $.ajax({
            url: bClipboard ? '/project/api/trPasteFile.php' : '/project/api/trUploadFile.php',
            type: 'POST', dataType: 'json', data: fd, processData: false, contentType: false
        }).done(function (data) {
            if (data.success === true) { window.location.reload(); return; }
            setSaveStatus(data.message || 'Erreur', true);
        }).fail(function (xhr) {
            setSaveStatus(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Erreur lors de l’envoi', true);
        });
    }

    $('#fAddFile').on('submit', function (e) {
        e.preventDefault();
        const fd = new FormData(this);
        fd.append('PRO_N_ID', $('#PRO_N_ID').val());
        setSaveStatus('Envoi du fichier…', false);
        $.ajax({url:'/project/api/trUploadFile.php',type:'POST',dataType:'json',data:fd,processData:false,contentType:false})
        .done(function(data){if(data.success===true){window.location.reload();return;}setSaveStatus(data.message||'Erreur',true);})
        .fail(function(xhr){setSaveStatus(xhr.responseJSON&&xhr.responseJSON.message?xhr.responseJSON.message:'Erreur lors de l’envoi',true);});
    });

    $('#dFileDrop').on('dragover', function (e) { e.preventDefault(); $(this).addClass('border-warning'); })
        .on('dragleave drop', function (e) { e.preventDefault(); $(this).removeClass('border-warning'); })
        .on('drop', function (e) {
            const files = e.originalEvent.dataTransfer.files;
            Array.from(files).forEach(function (file) { uploadFile(file, false); });
        });

    function uploadClipboardData(oClipboardData) {
        if (!oClipboardData) {
            return false;
        }

        if (oClipboardData.files && oClipboardData.files.length > 0) {
            for (const oFile of oClipboardData.files) {
                if (oFile.type && oFile.type.indexOf('image/') === 0) {
                    uploadFile(oFile, true);
                    return true;
                }
            }
        }

        const aItems = oClipboardData.items || [];

        for (const oItem of aItems) {
            if (oItem.kind === 'file' && oItem.type.indexOf('image/') === 0) {
                const oFile = oItem.getAsFile();

                if (oFile) {
                    uploadFile(oFile, true);
                    return true;
                }
            }
        }

        return false;
    }

    $(document).on('paste', function (e) {
        if (uploadClipboardData(e.originalEvent.clipboardData)) {
            e.preventDefault();
        }
    });

    $('#bPasteImage').on('click', async function () {
        if (!navigator.clipboard || !navigator.clipboard.read) {
            setSaveStatus('Le navigateur ne permet pas la lecture directe du presse-papiers. Utiliser Cmd/Ctrl+V dans la page.', true);
            $('#dFileDrop').trigger('focus');
            return;
        }

        try {
            const aClipboardItems = await navigator.clipboard.read();

            for (const oClipboardItem of aClipboardItems) {
                for (const sType of oClipboardItem.types) {
                    if (sType.indexOf('image/') !== 0) {
                        continue;
                    }

                    const oBlob = await oClipboardItem.getType(sType);
                    const sExtension = sType === 'image/jpeg' ? 'jpg' : (sType.split('/')[1] || 'png');
                    const oFile = new File(
                        [oBlob],
                        'presse-papiers_' + Date.now() + '.' + sExtension,
                        {type: sType}
                    );

                    uploadFile(oFile, true);
                    return;
                }
            }

            setSaveStatus('Aucune image trouvée dans le presse-papiers', true);
        } catch (e) {
            setSaveStatus('Lecture du presse-papiers refusée ou indisponible', true);
        }
    });

    function saveFile($field) {
        const $row = $field.closest('[data-file-id]');
        setFieldState($field, 'warning');
        $.ajax({url:'/project/api/trUpdFile.php',type:'POST',dataType:'json',data:{PRF_N_ID:$row.data('file-id'),sField:$field.data('field'),sValue:$field.val()}})
        .done(function(data){if(data.success===true){setFieldState($field,'success');return;}setFieldState($field,'danger');setSaveStatus(data.message||'Erreur',true);})
        .fail(function(xhr){setFieldState($field,'danger');setSaveStatus(xhr.responseJSON&&xhr.responseJSON.message?xhr.responseJSON.message:'Erreur',true);});
    }
    $('.js-file-text').typing({delay:600,start:function(e,x){setFieldState(x,'warning')},stop:function(e,x){saveFile(x)}});
    $('.js-file-change').on('change',function(){saveFile($(this))});
    $('.js-delete-file').on('click',function(){const $row=$(this).closest('[data-file-id]');if(!confirm('Supprimer ce fichier du catalogue ?'))return;$.post('/project/api/trDeleteFile.php',{PRF_N_ID:$row.data('file-id')},function(data){if(data.success)$row.remove();},'json');});
    $('.js-search-image').on('change',function(){const $field=$(this);setFieldState($field,'warning');$.post('/project/api/trSearchImage.php',{PRF_N_ID:$field.val()},function(data){if(data.success)setFieldState($field,'success');else setFieldState($field,'danger');},'json').fail(function(){setFieldState($field,'danger')});});

    $('#bDeleteProject').on('click', function () {
        if (!confirm('Supprimer ce projet du catalogue ?')) {
            return;
        }

        $.ajax({
            url: '/project/api/trDelete.php',
            type: 'POST',
            dataType: 'json',
            data: {
                PRO_N_ID: $('#PRO_N_ID').val()
            }
        })
        .done(function (data) {
            if (data.success === true) {
                window.location.href = '/index.php';
                return;
            }

            setSaveStatus(data.message || 'Erreur lors de la suppression', true);
        })
        .fail(function (xhr) {
            let sMessage = 'Erreur lors de la suppression';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                sMessage = xhr.responseJSON.message;
            }

            setSaveStatus(sMessage, true);
        });
    });
});
</script>

<?php require_once __DIR__ . '/../bottom.php'; ?>
