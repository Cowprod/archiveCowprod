<?php

require_once __DIR__ . '/../secure.php';

$aCategories = oRs(
    'SELECT TCA_N_ID,TCA_CH_LABEL,TCA_CH_COLOR,TCA_N_ORDER
     FROM T_TAGCATEGORY
     WHERE TCA_DT_SUPPRESSION IS NULL
     ORDER BY TCA_N_ORDER ASC,TCA_CH_LABEL ASC',
    '',
    '',
    0,
    '',
    $WM_ADMIN_conn
);

$aTags = oRs(
    'SELECT TAG_N_ID,TCA_N_ID,TAG_CH_LABEL,TAG_N_ORDER
     FROM T_TAG
     WHERE TAG_DT_SUPPRESSION IS NULL
     ORDER BY TAG_N_ORDER ASC,TAG_CH_LABEL ASC',
    '',
    '',
    0,
    '',
    $WM_ADMIN_conn
);

$aTagsByCategory = [];

foreach ($aTags as $aTag) {
    $aTagsByCategory[(int) $aTag['TCA_N_ID']][] = $aTag;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tags</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.8/dist/quartz/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" rel="stylesheet">
<style>
.autosave-warning{border-color:var(--bs-warning)!important;box-shadow:0 0 0 .15rem rgba(var(--bs-warning-rgb),.25)!important}
.autosave-success{border-color:var(--bs-success)!important;box-shadow:0 0 0 .15rem rgba(var(--bs-success-rgb),.25)!important}
.autosave-danger{border-color:var(--bs-danger)!important;box-shadow:0 0 0 .15rem rgba(var(--bs-danger-rgb),.25)!important}
</style></head>
<body class="bg-body-tertiary">
<div class="container-fluid py-3">
    <h1 class="h5 mb-3">Catégories et tags</h1>

    <div id="dMessage" class="alert alert-danger d-none"></div>

    <form id="fAddCategory" class="mb-3" autocomplete="off">
        <div class="input-group">
            <span class="input-group-text">Catégorie</span>
            <input
                type="text"
                class="form-control"
                id="TCA_CH_LABEL_ADD"
                name="TCA_CH_LABEL"
                placeholder="Libellé"
                required
            >
            <select class="form-select" id="TCA_CH_COLOR_ADD" name="TCA_CH_COLOR" style="max-width:180px;">
                <?php foreach (['primary','secondary','success','danger','warning','info','light','dark'] as $sColor): ?>
                    <option value="<?php echo $sColor; ?>"><?php echo $sColor; ?></option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-success">
                <i class="fa fa-plus-circle me-2"></i>Ajouter
            </button>
        </div>
    </form>

    <?php foreach ($aCategories as $aCategory): ?>
        <?php $TCA_N_ID = (int) $aCategory['TCA_N_ID']; ?>

        <div class="card mb-3" data-category-id="<?php echo htmlspecialchars(encrypt((string) $TCA_N_ID, $sEncryptKey), ENT_QUOTES, 'UTF-8'); ?>">
            <div class="card-header">
                <div class="input-group">
                    <button
                        type="button"
                        class="btn btn-danger js-delete-category"
                        title="Supprimer la catégorie"
                    >
                        <i class="fa fa-trash"></i>
                    </button>

                    <input
                        type="text"
                        class="form-control js-category-text"
                        data-field="TCA_CH_LABEL"
                        value="<?php echo htmlspecialchars($aCategory['TCA_CH_LABEL'], ENT_QUOTES, 'UTF-8'); ?>"
                    >

                    <select
                        class="form-select js-category-change"
                        data-field="TCA_CH_COLOR"
                        style="max-width:180px;"
                    >
                        <?php foreach (['primary','secondary','success','danger','warning','info','light','dark'] as $sColor): ?>
                            <option
                                value="<?php echo $sColor; ?>"
                                <?php echo $aCategory['TCA_CH_COLOR'] === $sColor ? 'selected' : ''; ?>
                            >
                                <?php echo $sColor; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="card-body">
                <form class="fAddTag mb-3" autocomplete="off">
                    <input type="hidden" name="TCA_N_ID" value="<?php echo htmlspecialchars(encrypt((string) $TCA_N_ID, $sEncryptKey), ENT_QUOTES, 'UTF-8'); ?>">
                    <div class="input-group">
                        <span class="input-group-text">Tag</span>
                        <input
                            type="text"
                            class="form-control"
                            name="TAG_CH_LABEL"
                            placeholder="Libellé"
                            required
                        >
                        <button type="submit" class="btn btn-success">
                            <i class="fa fa-plus-circle me-2"></i>Ajouter
                        </button>
                    </div>
                </form>

                <?php if (!empty($aTagsByCategory[$TCA_N_ID])): ?>
                    <table class="table table-bordered table-striped table-sm align-middle mb-0">
                        <tbody>
                            <?php foreach ($aTagsByCategory[$TCA_N_ID] as $aTag): ?>
                                <tr data-tag-id="<?php echo htmlspecialchars(encrypt((string) $aTag['TAG_N_ID'], $sEncryptKey), ENT_QUOTES, 'UTF-8'); ?>">
                                    <td class="text-center" style="width:50px;">
                                        <button
                                            type="button"
                                            class="btn btn-danger btn-sm js-delete-tag"
                                            title="Supprimer le tag"
                                        >
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                    <td>
                                        <input
                                            type="text"
                                            class="form-control form-control-sm js-tag-text"
                                            data-field="TAG_CH_LABEL"
                                            value="<?php echo htmlspecialchars($aTag['TAG_CH_LABEL'], ENT_QUOTES, 'UTF-8'); ?>"
                                        >
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/assets/js/jquery.typing-0.2.0.js"></script>
<script>
$(function () {
    function setFieldState($field, sState) {
        clearTimeout($field.data('autosave-state-timer'));
        $field.removeClass('autosave-warning autosave-success autosave-danger');

        if (sState === 'warning') {
            $field.addClass('autosave-warning');
        } else if (sState === 'success') {
            $field.addClass('autosave-success');
            $field.data('autosave-state-timer', setTimeout(function () {
                $field.removeClass('autosave-success');
            }, 1500));
        } else if (sState === 'danger') {
            $field.addClass('autosave-danger');
        }
    }

    function showError(sMessage) {
        $('#dMessage').removeClass('d-none').text(sMessage);
    }

    function ajaxPost(sUrl, data, fDone, fFail) {
        $('#dMessage').addClass('d-none').text('');

        $.ajax({
            url: sUrl,
            type: 'POST',
            dataType: 'json',
            data: data
        })
        .done(function (response) {
            if (response.success !== true) {
                showError(response.message || 'Erreur');

                if (fFail) {
                    fFail();
                }
                return;
            }

            if (fDone) {
                fDone(response);
            }
        })
        .fail(function (xhr) {
            let sMessage = 'Erreur';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                sMessage = xhr.responseJSON.message;
            }

            showError(sMessage);

            if (fFail) {
                fFail();
            }
        });
    }

    function saveCategory($field) {
        const $card = $field.closest('[data-category-id]');
        setFieldState($field, 'warning');

        ajaxPost('/tag/api/trUpdCategory.php', {
            TCA_N_ID: $card.data('category-id'),
            sField: $field.data('field'),
            sValue: $field.val()
        }, function () {
            setFieldState($field, 'success');
        }, function () {
            setFieldState($field, 'danger');
        });
    }

    function saveTag($field) {
        const $row = $field.closest('[data-tag-id]');
        setFieldState($field, 'warning');

        ajaxPost('/tag/api/trUpdTag.php', {
            TAG_N_ID: $row.data('tag-id'),
            sField: $field.data('field'),
            sValue: $field.val()
        }, function () {
            setFieldState($field, 'success');
        }, function () {
            setFieldState($field, 'danger');
        });
    }

    $('.js-category-text').typing({
        delay: 500,
        start: function (event, $elem) {
            setFieldState($elem, 'warning');
        },
        stop: function (event, $elem) {
            saveCategory($elem);
        }
    });

    $('.js-tag-text').typing({
        delay: 500,
        start: function (event, $elem) {
            setFieldState($elem, 'warning');
        },
        stop: function (event, $elem) {
            saveTag($elem);
        }
    });

    $('.js-category-change').on('change', function () {
        saveCategory($(this));
    });

    $('#fAddCategory').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);

        ajaxPost('/tag/api/trAddCategory.php', $form.serialize(), function () {
            window.location.reload();
        });
    });

    $('.fAddTag').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);

        ajaxPost('/tag/api/trAddTag.php', $form.serialize(), function () {
            window.location.reload();
        });
    });

    $('.js-delete-tag').on('click', function () {
        const $row = $(this).closest('[data-tag-id]');

        if (!confirm('Supprimer ce tag ?')) {
            return;
        }

        ajaxPost('/tag/api/trDeleteTag.php', {
            TAG_N_ID: $row.data('tag-id')
        }, function () {
            $row.remove();
        });
    });

    $('.js-delete-category').on('click', function () {
        const $card = $(this).closest('[data-category-id]');

        if (!confirm('Supprimer cette catégorie ?')) {
            return;
        }

        ajaxPost('/tag/api/trDeleteCategory.php', {
            TCA_N_ID: $card.data('category-id')
        }, function () {
            $card.remove();
        });
    });
});
</script>
</body>
</html>
