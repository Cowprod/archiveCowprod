<?php

require_once __DIR__ . '/../secure.php';

$PRO_N_ID = isset($_GET['PRO_N_ID']) ? (int) $_GET['PRO_N_ID'] : 0;

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
            <button type="button" class="btn btn-outline-danger btn-sm" id="bDeleteProject">
                <i class="fa fa-trash me-2"></i>Supprimer
            </button>
        </div>
    </div>

    <input type="hidden" id="PRO_N_ID" value="<?php echo (int) $aProject['PRO_N_ID']; ?>">

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
                                    value="<?php echo (int) $aTag['TAG_N_ID']; ?>"
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
                    class="btn btn-outline-light tag-admin-button"
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
                                <option value="<?php echo (int) $aUrlType['UTY_N_ID']; ?>">
                                    <?php echo htmlspecialchars($aUrlType['UTY_CH_LABEL'], ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                            <button type="button" class="btn btn-outline-light" id="bAdminUrlTypes" title="Administrer les types d’URL"><i class="fa fa-cog"></i></button>
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
                        <button type="submit" class="btn btn-outline-success">
                            <i class="fa fa-plus-circle me-2"></i>Ajouter
                        </button>
                    </div>
                </div>
            </form>

            <?php if (count($aProjectUrls) > 0): ?>
                <table class="table table-bordered table-striped table-sm align-middle mb-0">
                    <tbody>
                        <?php foreach ($aProjectUrls as $aProjectUrl): ?>
                            <tr data-url-id="<?php echo (int) $aProjectUrl['PRU_N_ID']; ?>">
                                <td class="text-center" style="width:50px;">
                                    <button type="button" class="btn btn-outline-danger btn-sm js-delete-url" title="Supprimer">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                                <td style="width:180px;">
                                    <select class="form-select form-select-sm js-url-change" data-field="UTY_N_ID">
                                        <?php foreach ($aUrlTypes as $aUrlType): ?>
                                            <option
                                                value="<?php echo (int) $aUrlType['UTY_N_ID']; ?>"
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
                                        class="btn btn-outline-light btn-sm"
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
        <div class="card-header">Fichiers</div>
        <div class="card-body text-body-secondary">
            Bloc fichiers à venir.
        </div>
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
<script>
$(function () {
    let nPendingSave = 0;

    function setSaveStatus(sStatus, bError) {
        $('#dSaveStatus')
            .toggleClass('text-danger', bError === true)
            .toggleClass('text-body-secondary', bError !== true)
            .text(sStatus);
    }

    function saveField($field) {
        const sField = $field.data('field');
        const sValue = $field.val();

        nPendingSave++;
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
                setSaveStatus(data.message || 'Erreur', true);
                return;
            }

            if (sField === 'PRO_CH_LABEL') {
                $('h1').text(sValue || 'Projet');
            }
        })
        .fail(function (xhr) {
            let sMessage = 'Erreur de sauvegarde';

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
    }

    $('.js-autosave-text').typing({
        delay: 600,
        start: function () {
            setSaveStatus('Modification…', false);
        },
        stop: function (event, $elem) {
            saveField($elem);
        }
    });

    $('.js-autosave-change').on('change', function () {
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
        nPendingSave++;
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
                setSaveStatus(data.message || 'Erreur', true);
            }
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

    $('#bAdminUrlTypes').on('click', function () {
        $('#fAdminUrlTypes').attr('src', '/urlType/index.php');
        bootstrap.Modal.getOrCreateInstance(document.getElementById('mAdminUrlTypes')).show();
    });
    document.getElementById('mAdminUrlTypes').addEventListener('hidden.bs.modal', function () { window.location.reload(); });

    function saveUrl($field) {
        const $row = $field.closest('[data-url-id]');

        nPendingSave++;
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
                setSaveStatus(data.message || 'Erreur', true);
            }
        })
        .fail(function (xhr) {
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
        stop: function (event, $elem) {
            saveUrl($elem);
        }
    });

    $('.js-url-change').on('change', function () {
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
