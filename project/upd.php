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

$aProjects = oRs(
    '',
    __DIR__ . '/project.sql',
    'PRO_N_ID=' . prepNum2Update($PRO_N_ID),
    0,
    '',
    $WM_ADMIN_conn
);
$aProject = $aProjects[0] ?? false;

if (!$aProject) {
    http_response_code(404);
    exit('Projet introuvable');
}

$aTags = oRs('', __DIR__ . '/../projectTag/tag.sql', '', 0, '', $WM_ADMIN_conn);

$aTagsByCategory = [];

foreach ($aTags as $aTag) {
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

$aProjectTags = oRs(
    '',
    __DIR__ . '/../projectTag/projectTag.sql',
    'PRO_N_ID=' . prepNum2Update($PRO_N_ID),
    0,
    '',
    $WM_ADMIN_conn
);

$aSelectedTagIds = array_map('intval', array_column($aProjectTags, 'TAG_N_ID'));



$aProjectUrls = oRs(
    '',
    __DIR__ . '/../projectUrl/projectUrl.sql',
    'PRO_N_ID=' . prepNum2Update($PRO_N_ID),
    0,
    '',
    $WM_ADMIN_conn
);



$aProjectFiles = oRs(
    '',
    __DIR__ . '/../projectFile/projectFile.sql',
    'PRO_N_ID=' . prepNum2Update($PRO_N_ID),
    0,
    '',
    $WM_ADMIN_conn
);

require_once __DIR__ . '/../top.php';
?>

<main class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0"><?php echo htmlspecialchars($aProject['PRO_CH_LABEL'], ENT_QUOTES, 'UTF-8'); ?></h1>
        <span id="dSaveStatus" class="text-body-secondary"></span>
    </div>

    <input type="hidden" id="PRO_N_ID" value="<?php echo htmlspecialchars(encrypt((string) $aProject['PRO_N_ID'], $sEncryptKey), ENT_QUOTES, 'UTF-8'); ?>">

    <div class="card mb-4">
        <div class="card-header">Projet</div>

        <div class="card-body">
            <div class="input-group mb-3">
                <span class="input-group-text w-25">Nom</span>
                <input
                    type="text"
                    class="form-control js-autosave-text"
                    id="PRO_CH_LABEL"
                    data-field="PRO_CH_LABEL"
                    value="<?php echo htmlspecialchars((string) $aProject['PRO_CH_LABEL'], ENT_QUOTES, 'UTF-8'); ?>"
                >
            </div>

            <div class="input-group mb-3">
                <span class="input-group-text w-25">Début</span>
                <input
                    type="number"
                    min="1900"
                    max="2100"
                    class="form-control js-autosave-change"
                    id="PRO_N_YEARSTART"
                    data-field="PRO_N_YEARSTART"
                    value="<?php echo htmlspecialchars((string) ($aProject['PRO_N_YEARSTART'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                >

                <span class="input-group-text w-25">Fin</span>
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

            <div class="input-group">
                <span class="input-group-text w-25 align-items-start">Description</span>
                <textarea
                    class="form-control js-autosave-text"
                    id="PRO_CH_DESCRIPTION"
                    data-field="PRO_CH_DESCRIPTION"
                    rows="8"
                ><?php echo htmlspecialchars((string) ($aProject['PRO_CH_DESCRIPTION'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></textarea>
            </div>
        </div>

        <div class="card-footer text-center">
            <div class="btn-group">
                <a href="/index.php" class="btn btn-secondary">
                    <i class="fa fa-arrow-left me-2"></i>Retour
                </a>
                <button type="button" class="btn btn-danger" id="bDeleteProject">
                    <i class="fa fa-trash me-2"></i>Supprimer
                </button>
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
                <div class="input-group">
                    <span class="input-group-text">Type</span>
                    <div
                        id="dSelUrlTypeAdd"
                        class="input-group-text p-0 resource-type-host"
                        data-sel-url="/urlType/sel.php?sId=UTY_N_ID&amp;updateDiv=dSelUrlTypeAdd&amp;required=1"
                    ></div>

                    <span class="input-group-text">URL</span>
                    <input type="url" class="form-control resource-url" name="PRU_CH_URL" required>

                    <span class="input-group-text">Libellé</span>
                    <input type="text" class="form-control resource-label" name="PRU_CH_LABEL">

                    <span class="input-group-text">Année</span>
                    <input type="number" min="1900" max="2100" class="form-control resource-year" name="PRU_N_YEAR">

                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-plus-circle me-2"></i>Ajouter
                    </button>
                </div>
            </form>

            <?php if (count($aProjectUrls) > 0): ?>
                <table class="table table-bordered table-striped table-sm align-middle mb-0" id="tUrlTable">
                    <tbody id="tUrlBody">
                        <?php foreach ($aProjectUrls as $nUrlIndex => $aProjectUrl): ?>
                            <tr data-url-id="<?php echo htmlspecialchars(encrypt((string) $aProjectUrl['PRU_N_ID'], $sEncryptKey), ENT_QUOTES, 'UTF-8'); ?>">
                                <td class="text-center js-drag-url" style="width:38px;cursor:move;" title="Déplacer"><i class="fa fa-grip-vertical text-body-secondary"></i></td>
                                <td class="text-center" style="width:50px;">
                                    <button type="button" class="btn btn-danger btn-sm js-delete-url" title="Supprimer">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                                <td>
                                    <?php
                                    $sUrlTypeHostId = 'dSelUrlTypeRow' . $nUrlIndex;
                                    $sUrlTypeSelUrl = '/urlType/sel.php?' . http_build_query([
                                        'UTY_N_ID' => encrypt((string) $aProjectUrl['UTY_N_ID'], $sEncryptKey),
                                        'sId' => 'UTY_N_ID_ROW_' . $nUrlIndex,
                                        'updateDiv' => $sUrlTypeHostId,
                                        'small' => '1',
                                        'field' => 'UTY_N_ID',
                                        'noAdmin' => '1',
                                    ]);
                                    ?>
                                    <div class="input-group input-group-sm resource-edit-group">
                                        <span class="input-group-text">Type</span>
                                        <div
                                            id="<?php echo $sUrlTypeHostId; ?>"
                                            class="input-group-text p-0 resource-type-host"
                                            data-sel-url="<?php echo htmlspecialchars($sUrlTypeSelUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                        ></div>

                                        <span class="input-group-text">URL</span>
                                        <input
                                            type="url"
                                            class="form-control js-url-text resource-url"
                                            data-field="PRU_CH_URL"
                                            value="<?php echo htmlspecialchars($aProjectUrl['PRU_CH_URL'], ENT_QUOTES, 'UTF-8'); ?>"
                                        >

                                        <span class="input-group-text">Libellé</span>
                                        <input
                                            type="text"
                                            class="form-control js-url-text resource-label"
                                            data-field="PRU_CH_LABEL"
                                            value="<?php echo htmlspecialchars((string) ($aProjectUrl['PRU_CH_LABEL'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                        >

                                        <span class="input-group-text">Année</span>
                                        <input
                                            type="number"
                                            min="1900"
                                            max="2100"
                                            class="form-control js-url-change resource-year"
                                            data-field="PRU_N_YEAR"
                                            value="<?php echo htmlspecialchars((string) ($aProjectUrl['PRU_N_YEAR'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"
                                        >

                                        <a
                                            class="btn btn-light"
                                            href="<?php echo htmlspecialchars($aProjectUrl['PRU_CH_URL'], ENT_QUOTES, 'UTF-8'); ?>"
                                            target="_blank"
                                            rel="noopener"
                                            title="Ouvrir"
                                        >
                                            <i class="fa fa-external-link"></i>
                                        </a>
                                    </div>
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
                <div class="input-group">
                    <span class="input-group-text">Type</span>
                    <div
                        id="dSelFileTypeAdd"
                        class="input-group-text p-0 resource-type-host"
                        data-sel-url="/fileType/sel.php?sId=FTY_N_ID&amp;updateDiv=dSelFileTypeAdd&amp;required=1"
                    ></div>

                    <span class="input-group-text">Fichier</span>
                    <input type="file" class="form-control resource-file" name="file" id="PRF_FILE" required>

                    <span class="input-group-text">Libellé</span>
                    <input type="text" class="form-control resource-label" name="PRF_CH_LABEL">

                    <span class="input-group-text">Année</span>
                    <input type="number" min="1900" max="2100" class="form-control resource-year" name="PRF_N_YEAR">

                    <button type="submit" class="btn btn-success">
                        <i class="fa fa-upload me-2"></i>Ajouter
                    </button>
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
                    <table class="table table-bordered table-striped table-sm align-middle mb-0" id="tFileTable">
                        <tbody id="tFileBody">
                        <?php foreach ($aProjectFiles as $nFileIndex => $aProjectFile): ?>
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
                                <td>
                                    <?php
                                    $sFileTypeHostId = 'dSelFileTypeRow' . $nFileIndex;
                                    $sFileTypeSelUrl = '/fileType/sel.php?' . http_build_query([
                                        'FTY_N_ID' => encrypt((string) $aProjectFile['FTY_N_ID'], $sEncryptKey),
                                        'sId' => 'FTY_N_ID_ROW_' . $nFileIndex,
                                        'updateDiv' => $sFileTypeHostId,
                                        'small' => '1',
                                        'field' => 'FTY_N_ID',
                                        'noAdmin' => '1',
                                    ]);
                                    ?>
                                    <div class="input-group input-group-sm resource-edit-group">
                                        <span class="input-group-text">Type</span>
                                        <div
                                            id="<?php echo $sFileTypeHostId; ?>"
                                            class="input-group-text p-0 resource-type-host"
                                            data-sel-url="<?php echo htmlspecialchars($sFileTypeSelUrl, ENT_QUOTES, 'UTF-8'); ?>"
                                        ></div>

                                        <span class="input-group-text">Libellé</span>
                                        <input
                                            type="text"
                                            class="form-control js-file-text resource-label"
                                            data-field="PRF_CH_LABEL"
                                            value="<?php echo htmlspecialchars((string)($aProjectFile['PRF_CH_LABEL']??''),ENT_QUOTES,'UTF-8'); ?>"
                                        >

                                        <span class="input-group-text">Année</span>
                                        <input
                                            type="number"
                                            min="1900"
                                            max="2100"
                                            class="form-control js-file-change resource-year"
                                            data-field="PRF_N_YEAR"
                                            value="<?php echo htmlspecialchars((string)($aProjectFile['PRF_N_YEAR']??''),ENT_QUOTES,'UTF-8'); ?>"
                                        >

                                        <?php if ($bImage): ?>
                                            <span class="input-group-text">Catalogue</span>
                                            <span class="input-group-text bg-light">
                                                <input
                                                    class="form-check-input mt-0 js-search-image"
                                                    type="radio"
                                                    name="PRF_BL_SEARCHIMAGE"
                                                    value="<?php echo htmlspecialchars(encrypt((string) $aProjectFile['PRF_N_ID'], $sEncryptKey), ENT_QUOTES, 'UTF-8'); ?>"
                                                    <?php echo (int)$aProjectFile['PRF_BL_SEARCHIMAGE']===1?'checked':''; ?>
                                                    title="Image du catalogue"
                                                >
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-body-secondary">
                                        <?php echo htmlspecialchars($aProjectFile['PRF_CH_FILENAME'],ENT_QUOTES,'UTF-8'); ?>
                                        · <?php echo number_format(((int)$aProjectFile['PRF_N_SIZE'])/1024,0,',',' '); ?> Ko
                                    </small>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?><div class="text-body-secondary">Aucun fichier.</div><?php endif; ?>
        </div>
    </div>
</main>

<style>
.resource-type-host {
    flex: 0 1 240px;
    min-width: 180px;
    padding: 0 !important;
    border: 0 !important;
    background: transparent !important;
}

.resource-type-host > .input-group {
    height: 100%;
    flex-wrap: nowrap;
}

.resource-type-host .form-select,
.resource-type-host .btn {
    height: 100%;
    border-radius: 0 !important;
}

.resource-url,
.resource-file {
    flex: 2 1 300px !important;
    min-width: 180px;
}

.resource-label {
    flex: 1 1 180px !important;
    min-width: 120px;
}

.resource-year {
    flex: 0 0 95px !important;
    max-width: 95px;
}

.resource-edit-group {
    flex-wrap: nowrap;
}

.resource-edit-group .resource-type-host {
    flex-basis: 180px;
    min-width: 150px;
}
</style>

<script>
$(function () {
    let nPendingSave = 0;

    $('[data-sel-url]').each(function () {
        updDiv(this, $(this).data('sel-url'));
    });

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
            url: '/project/trUpdProject.php',
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
            url: '/projectTag/trUpdProjectTag.php',
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
        bootBoxAdmin('Administrer les tags', '/tag/index.php', function () {
            window.location.reload();
        });
    });

    function setOrderRowState($row, sState) {
        clearTimeout($row.data('order-state-timer'));
        $row.removeClass('table-warning table-success table-danger');

        if (sState === 'warning') {
            $row.addClass('table-warning');
        } else if (sState === 'success') {
            $row.addClass('table-success');
            $row.data('order-state-timer', setTimeout(function () {
                $row.removeClass('table-success');
            }, 1500));
        } else if (sState === 'danger') {
            $row.addClass('table-danger');
        }
    }

    function saveResourceOrder(sType, $row) {
        const bUrl = sType === 'url';
        const $body = bUrl ? $('#tUrlBody') : $('#tFileBody');
        const sDataName = bUrl ? 'url-id' : 'file-id';
        const sParam = bUrl ? 'PRU_N_ID[]' : 'PRF_N_ID[]';
        const sUrl = bUrl ? '/projectUrl/trOrderProjectUrl.php' : '/projectFile/trOrderProjectFile.php';
        const aData = [{name:'PRO_N_ID', value:$('#PRO_N_ID').val()}];

        $body.children('tr').each(function () {
            aData.push({name:sParam, value:$(this).data(sDataName)});
        });

        setOrderRowState($row, 'warning');
        setSaveStatus('Enregistrement de l’ordre…', false);

        $.ajax({
            url: sUrl,
            type: 'POST',
            dataType: 'json',
            data: aData
        })
        .done(function(data) {
            if (data.success === true) {
                setOrderRowState($row, 'success');
                setSaveStatus('Enregistré', false);
                return;
            }

            setOrderRowState($row, 'danger');
            setSaveStatus(data.message || 'Erreur lors du classement', true);
        })
        .fail(function(xhr) {
            setOrderRowState($row, 'danger');
            setSaveStatus(
                xhr.responseJSON && xhr.responseJSON.message
                    ? xhr.responseJSON.message
                    : 'Erreur lors du classement',
                true
            );
        });
    }

    if ($('#tUrlTable').length) {
        $('#tUrlTable').tableDnD({
            dragHandle: '.js-drag-url',
            onDragStart: function(table, row) {
                setOrderRowState($(row), 'warning');
            },
            onDrop: function(table, row) {
                saveResourceOrder('url', $(row));
            }
        });
    }

    if ($('#tFileTable').length) {
        $('#tFileTable').tableDnD({
            dragHandle: '.js-drag-file',
            onDragStart: function(table, row) {
                setOrderRowState($(row), 'warning');
            },
            onDrop: function(table, row) {
                saveResourceOrder('file', $(row));
            }
        });
    }

    function saveUrl($field) {
        const $row = $field.closest('[data-url-id]');

        nPendingSave++;
        setFieldState($field, 'warning');
        setSaveStatus('Enregistrement…', false);

        $.ajax({
            url: '/projectUrl/trUpdProjectUrl.php',
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

    $(document).on('change', '.js-url-change', function () {
        setFieldState($(this), 'warning');
        saveUrl($(this));
    });

    $('#fAddUrl').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);

        $.ajax({
            url: '/projectUrl/trUpdProjectUrl.php',
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

        cowprodConfirm('Supprimer cette URL ?', function () {
        $.ajax({
            url: '/projectUrl/trSupProjectUrl.php',
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
    });

    function getClipboardFileType() {
        let nType = $('#FTY_N_ID').val();

        if (nType) {
            return nType;
        }

        $('#FTY_N_ID option').each(function () {
            const sLabel = $(this).text().trim().toLowerCase();

            if (!nType && (sLabel === 'screenshot' || sLabel === 'image')) {
                nType = $(this).val();
            }
        });

        if (nType) {
            $('#FTY_N_ID').val(nType);
        }

        return nType;
    }

    function uploadFile(oFile, bClipboard) {
        const nType = bClipboard ? getClipboardFileType() : $('#FTY_N_ID').val();

        if (!nType) {
            setSaveStatus('Choisir un type de fichier avant l’envoi', true);
            $('#FTY_N_ID').addClass('autosave-warning');
            return;
        }
        const fd = new FormData();
        fd.append('PRO_N_ID', $('#PRO_N_ID').val());
        fd.append('FTY_N_ID', nType);
        fd.append('file', oFile, oFile.name || 'presse-papiers.png');

        setSaveStatus('Envoi du fichier…', false);
        $.ajax({
            url: bClipboard ? '/projectFile/trPasteProjectFile.php' : '/projectFile/trUploadProjectFile.php',
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
        $.ajax({url:'/projectFile/trUploadProjectFile.php',type:'POST',dataType:'json',data:fd,processData:false,contentType:false})
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
        $.ajax({url:'/projectFile/trUpdProjectFile.php',type:'POST',dataType:'json',data:{PRF_N_ID:$row.data('file-id'),sField:$field.data('field'),sValue:$field.val()}})
        .done(function(data){if(data.success===true){setFieldState($field,'success');return;}setFieldState($field,'danger');setSaveStatus(data.message||'Erreur',true);})
        .fail(function(xhr){setFieldState($field,'danger');setSaveStatus(xhr.responseJSON&&xhr.responseJSON.message?xhr.responseJSON.message:'Erreur',true);});
    }
    $('.js-file-text').typing({delay:600,start:function(e,x){setFieldState(x,'warning')},stop:function(e,x){saveFile(x)}});
    $(document).on('change','.js-file-change',function(){saveFile($(this))});
    $('.js-delete-file').on('click',function(){const $row=$(this).closest('[data-file-id]');cowprodConfirm('Supprimer ce fichier du catalogue ?',function(){$.post('/projectFile/trSupProjectFile.php',{PRF_N_ID:$row.data('file-id')},function(data){if(data.success)$row.remove();},'json');});});
    $('.js-search-image').on('change',function(){const $field=$(this);setFieldState($field,'warning');$.post('/projectFile/trSearchImageProjectFile.php',{PRF_N_ID:$field.val()},function(data){if(data.success)setFieldState($field,'success');else setFieldState($field,'danger');},'json').fail(function(){setFieldState($field,'danger')});});

    $('#bDeleteProject').on('click', function () {
        cowprodConfirm('Supprimer ce projet du catalogue ?', function () {
        $.ajax({
            url: '/project/trSupProject.php',
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
});
</script>

<?php require_once __DIR__ . '/../bottom.php'; ?>
