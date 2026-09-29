<?php

require_once __DIR__ . '/../secure.php';

$oCategories = $WM_ADMIN_conn->query(
    'SELECT
        T_TAGCATEGORY.TCA_N_ID,
        T_TAGCATEGORY.TCA_CH_LABEL,
        T_TAGCATEGORY.TCA_CH_COLOR,
        T_TAGCATEGORY.TCA_N_ORDER
     FROM T_TAGCATEGORY
     WHERE T_TAGCATEGORY.TCA_DT_SUPPRESSION IS NULL
     ORDER BY
        T_TAGCATEGORY.TCA_N_ORDER ASC,
        T_TAGCATEGORY.TCA_CH_LABEL ASC'
);

$aCategories = $oCategories->fetchAll();

$oTags = $WM_ADMIN_conn->query(
    'SELECT
        T_TAG.TAG_N_ID,
        T_TAG.TCA_N_ID,
        T_TAG.TAG_CH_LABEL,
        T_TAG.TAG_N_ORDER
     FROM T_TAG
     WHERE T_TAG.TAG_DT_SUPPRESSION IS NULL
     ORDER BY
        T_TAG.TAG_N_ORDER ASC,
        T_TAG.TAG_CH_LABEL ASC'
);

$aTagsByCategory = [];

foreach ($oTags->fetchAll() as $aTag) {
    $aTagsByCategory[(int) $aTag['TCA_N_ID']][] = $aTag;
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tags</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
<div class="container-fluid py-3">
    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h5 mb-0">Catégories et tags</h1>
        <button type="button" class="btn btn-primary btn-sm" id="bAddCategory">
            <i class="fa fa-plus me-2"></i>Catégorie
        </button>
    </div>

    <div id="dMessage" class="alert alert-danger d-none"></div>

    <?php foreach ($aCategories as $aCategory): ?>
        <?php $TCA_N_ID = (int) $aCategory['TCA_N_ID']; ?>
        <div class="card mb-3" data-category-id="<?php echo $TCA_N_ID; ?>">
            <div class="card-header">
                <div class="row g-2 align-items-center">
                    <div class="col">
                        <input
                            type="text"
                            class="form-control form-control-sm js-category-text"
                            data-field="TCA_CH_LABEL"
                            value="<?php echo htmlspecialchars($aCategory['TCA_CH_LABEL'], ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    </div>
                    <div class="col-sm-3">
                        <select class="form-select form-select-sm js-category-change" data-field="TCA_CH_COLOR">
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
                    <div class="col-sm-2">
                        <input
                            type="number"
                            class="form-control form-control-sm js-category-change"
                            data-field="TCA_N_ORDER"
                            value="<?php echo (int) $aCategory['TCA_N_ORDER']; ?>"
                        >
                    </div>
                    <div class="col-auto">
                        <button type="button" class="btn btn-outline-danger btn-sm js-delete-category">
                            <i class="fa fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <table class="table table-sm align-middle mb-3">
                    <thead>
                        <tr>
                            <th>Tag</th>
                            <th style="width:120px;">Ordre</th>
                            <th style="width:50px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($aTagsByCategory[$TCA_N_ID] ?? [] as $aTag): ?>
                            <tr data-tag-id="<?php echo (int) $aTag['TAG_N_ID']; ?>">
                                <td>
                                    <input
                                        type="text"
                                        class="form-control form-control-sm js-tag-text"
                                        data-field="TAG_CH_LABEL"
                                        value="<?php echo htmlspecialchars($aTag['TAG_CH_LABEL'], ENT_QUOTES, 'UTF-8'); ?>"
                                    >
                                </td>
                                <td>
                                    <input
                                        type="number"
                                        class="form-control form-control-sm js-tag-change"
                                        data-field="TAG_N_ORDER"
                                        value="<?php echo (int) $aTag['TAG_N_ORDER']; ?>"
                                    >
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-outline-danger btn-sm js-delete-tag">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <button type="button" class="btn btn-outline-primary btn-sm js-add-tag">
                    <i class="fa fa-plus me-2"></i>Tag
                </button>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="/assets/js/jquery.typing-0.2.0.js"></script>
<script>
$(function () {
    function showError(sMessage) {
        $('#dMessage').removeClass('d-none').text(sMessage);
    }

    function ajaxPost(sUrl, data, fDone) {
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
        });
    }

    function saveCategory($field) {
        const $card = $field.closest('[data-category-id]');

        ajaxPost('/tag/api/trUpdCategory.php', {
            TCA_N_ID: $card.data('category-id'),
            sField: $field.data('field'),
            sValue: $field.val()
        });
    }

    function saveTag($field) {
        const $row = $field.closest('[data-tag-id]');

        ajaxPost('/tag/api/trUpdTag.php', {
            TAG_N_ID: $row.data('tag-id'),
            sField: $field.data('field'),
            sValue: $field.val()
        });
    }

    $('.js-category-text').typing({
        delay: 500,
        stop: function (event, $elem) {
            saveCategory($elem);
        }
    });

    $('.js-tag-text').typing({
        delay: 500,
        stop: function (event, $elem) {
            saveTag($elem);
        }
    });

    $('.js-category-change').on('change', function () {
        saveCategory($(this));
    });

    $('.js-tag-change').on('change', function () {
        saveTag($(this));
    });

    $('#bAddCategory').on('click', function () {
        ajaxPost('/tag/api/trAddCategory.php', {}, function () {
            window.location.reload();
        });
    });

    $('.js-add-tag').on('click', function () {
        const $card = $(this).closest('[data-category-id]');

        ajaxPost('/tag/api/trAddTag.php', {
            TCA_N_ID: $card.data('category-id')
        }, function () {
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
