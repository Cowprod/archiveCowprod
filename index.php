<?php

require_once __DIR__ . '/secure.php';

$sSearch = trim((string) ($_GET['q'] ?? ''));
$sYear = trim((string) ($_GET['year'] ?? ''));
$aSearchTagIds = $_GET['tag'] ?? [];

if (!is_array($aSearchTagIds)) {
    $aSearchTagIds = [$aSearchTagIds];
}

$aSearchTagIds = array_values(array_unique(array_map(
    fn ($sId) => decryptId($sId, $sEncryptKey),
    array_filter($aSearchTagIds, fn ($sId) => trim((string) $sId) !== '')
)));

$nSearchYear = null;
$bInvalidYear = false;

if ($sYear !== '') {
    if (ctype_digit($sYear) && (int) $sYear >= 1900 && (int) $sYear <= 2100) {
        $nSearchYear = (int) $sYear;
    } else {
        $bInvalidYear = true;
    }
}

$sSql = 'SELECT
            T_PROJECT.PRO_N_ID,
            T_PROJECT.PRO_CH_LABEL,
            T_PROJECT.PRO_TX_DESCRIPTION,
            T_PROJECT.PRO_N_YEARSTART,
            T_PROJECT.PRO_N_YEAREND
         FROM T_PROJECT
         WHERE T_PROJECT.PRO_DT_SUPPRESSION IS NULL';

if ($sSearch !== '') {
    $sLike = prepString2Update('%' . $sSearch . '%');

    $sSql .= ' AND (
        T_PROJECT.PRO_CH_LABEL LIKE ' . $sLike . '
        OR T_PROJECT.PRO_TX_DESCRIPTION LIKE ' . $sLike . '
        OR EXISTS (
            SELECT 1
            FROM T_PROJECTURL
            WHERE T_PROJECTURL.PRO_N_ID = T_PROJECT.PRO_N_ID
              AND T_PROJECTURL.PRU_DT_SUPPRESSION IS NULL
              AND (
                  T_PROJECTURL.PRU_CH_LABEL LIKE ' . $sLike . '
                  OR T_PROJECTURL.PRU_CH_URL LIKE ' . $sLike . '
              )
        )
        OR EXISTS (
            SELECT 1
            FROM T_PROJECTFILE
            WHERE T_PROJECTFILE.PRO_N_ID = T_PROJECT.PRO_N_ID
              AND T_PROJECTFILE.PRF_DT_SUPPRESSION IS NULL
              AND (
                  T_PROJECTFILE.PRF_CH_LABEL LIKE ' . $sLike . '
                  OR T_PROJECTFILE.PRF_CH_FILENAME LIKE ' . $sLike . '
              )
        )
        OR EXISTS (
            SELECT 1
            FROM T_PROJECTTAG
            INNER JOIN T_TAG
                ON T_TAG.TAG_N_ID = T_PROJECTTAG.TAG_N_ID
               AND T_TAG.TAG_DT_SUPPRESSION IS NULL
            WHERE T_PROJECTTAG.PRO_N_ID = T_PROJECT.PRO_N_ID
              AND T_PROJECTTAG.PTA_DT_SUPPRESSION IS NULL
              AND T_TAG.TAG_CH_LABEL LIKE ' . $sLike . '
        )
    )';
}

if ($nSearchYear !== null) {
    $sSql .= ' AND (
        (T_PROJECT.PRO_N_YEARSTART IS NULL OR T_PROJECT.PRO_N_YEARSTART <= ' . prepNum2Update($nSearchYear) . ')
        AND (T_PROJECT.PRO_N_YEAREND IS NULL OR T_PROJECT.PRO_N_YEAREND >= :nSearchYear)
        AND (T_PROJECT.PRO_N_YEARSTART IS NOT NULL OR T_PROJECT.PRO_N_YEAREND IS NOT NULL)
    )';
}

if (count($aSearchTagIds) > 0) {
    $aPreparedTagIds = array_map('prepNum2Update', $aSearchTagIds);

    $sSql .= ' AND (
        SELECT COUNT(DISTINCT T_PROJECTTAG.TAG_N_ID)
        FROM T_PROJECTTAG
        WHERE T_PROJECTTAG.PRO_N_ID = T_PROJECT.PRO_N_ID
          AND T_PROJECTTAG.PTA_DT_SUPPRESSION IS NULL
          AND T_PROJECTTAG.TAG_N_ID IN (' . implode(',', $aPreparedTagIds) . ')
    ) = ' . prepNum2Update(count($aSearchTagIds));
}

$sSql .= ' ORDER BY T_PROJECT.PRO_N_YEARSTART DESC, T_PROJECT.PRO_CH_LABEL ASC';

$aProjects = oRs($sSql, '', '', 0, '', $WM_ADMIN_conn);

$aProjectTags = oRs('', __DIR__ . '/sql/catalogue/selectProjectTags.sql', '', 0, '', $WM_ADMIN_conn);

$aTagsByProject = [];

foreach ($aProjectTags as $aTag) {
    $aTagsByProject[(int) $aTag['PRO_N_ID']][] = $aTag;
}

$aSearchTags = oRs('', __DIR__ . '/sql/catalogue/selectSearchTags.sql', '', 0, '', $WM_ADMIN_conn);
$aSearchTagsByCategory = [];

foreach ($aSearchTags as $aTag) {
    $nCategoryId = (int) $aTag['TCA_N_ID'];

    if (!isset($aSearchTagsByCategory[$nCategoryId])) {
        $aSearchTagsByCategory[$nCategoryId] = [
            'label' => $aTag['TCA_CH_LABEL'],
            'tags' => [],
        ];
    }

    $aSearchTagsByCategory[$nCategoryId]['tags'][] = $aTag;
}

$aSearchImages = oRs('', __DIR__ . '/sql/catalogue/selectSearchImages.sql', '', 0, '', $WM_ADMIN_conn);
$aSearchImageByProject = [];

foreach ($aSearchImages as $aImage) {
    $aSearchImageByProject[(int) $aImage['PRO_N_ID']] = (int) $aImage['PRF_N_ID'];
}

require_once __DIR__ . '/top.php';
?>

<main class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0">Catalogue</h1>

        <form method="post" action="/project/trAdd.php">
            <button type="submit" class="btn btn-primary">
                <i class="fa fa-plus me-2"></i>Nouveau projet
            </button>
        </form>
    </div>

    <form method="get" class="card mb-4">
        <div class="card-header">Recherche</div>
        <div class="card-body">
            <div class="row g-2">
                <div class="col-12 col-xl">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fa fa-search me-2"></i>Texte</span>
                        <input
                            type="search"
                            class="form-control"
                            id="q"
                            name="q"
                            value="<?php echo htmlspecialchars($sSearch, ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    </div>
                </div>

                <div class="col-sm-5 col-xl-2">
                    <div class="input-group">
                        <span class="input-group-text">Année</span>
                        <input
                            type="number"
                            min="1900"
                            max="2100"
                            class="form-control <?php echo $bInvalidYear ? 'is-invalid' : ''; ?>"
                            id="year"
                            name="year"
                            value="<?php echo htmlspecialchars($sYear, ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    </div>
                </div>

                <div class="col-12 col-xl-5">
                    <div class="input-group search-tag-group">
                        <span class="input-group-text">Tags <span class="ms-1 text-body-secondary">(tous)</span></span>
                        <select class="form-select" id="tagSearch" name="tag[]" multiple>
                            <?php foreach ($aSearchTagsByCategory as $aCategory): ?>
                                <optgroup label="<?php echo htmlspecialchars($aCategory['label'], ENT_QUOTES, 'UTF-8'); ?>">
                                    <?php foreach ($aCategory['tags'] as $aTag): ?>
                                        <option value="<?php echo htmlspecialchars(encrypt((string) $aTag['TAG_N_ID'], $sEncryptKey), ENT_QUOTES, 'UTF-8'); ?>" <?php echo in_array((int) $aTag['TAG_N_ID'], $aSearchTagIds, true) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($aTag['TAG_CH_LABEL'], ENT_QUOTES, 'UTF-8'); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </optgroup>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="col-auto">
                    <div class="btn-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-search me-2"></i>Rechercher
                        </button>
                        <?php if ($sSearch !== '' || $sYear !== '' || count($aSearchTagIds) > 0): ?>
                            <a href="/index.php" class="btn btn-light" title="Effacer la recherche">
                                <i class="fa fa-times"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-body-secondary"><?php echo count($aProjects); ?> projet<?php echo count($aProjects) > 1 ? 's' : ''; ?></span>
    </div>

    <?php if (count($aProjects) === 0): ?>
        <div class="card">
            <div class="card-body text-body-secondary">Aucun projet correspondant.</div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($aProjects as $aProject): ?>
                <?php
                $PRO_N_ID = (int) $aProject['PRO_N_ID'];
                $sYears = '';

                if ($aProject['PRO_N_YEARSTART'] !== null && $aProject['PRO_N_YEAREND'] !== null) {
                    $sYears = $aProject['PRO_N_YEARSTART'] == $aProject['PRO_N_YEAREND']
                        ? (string) $aProject['PRO_N_YEARSTART']
                        : $aProject['PRO_N_YEARSTART'] . '–' . $aProject['PRO_N_YEAREND'];
                } elseif ($aProject['PRO_N_YEARSTART'] !== null) {
                    $sYears = (string) $aProject['PRO_N_YEARSTART'];
                } elseif ($aProject['PRO_N_YEAREND'] !== null) {
                    $sYears = (string) $aProject['PRO_N_YEAREND'];
                }
                ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <a href="/project/upd.php?PRO_N_ID=<?php echo urlencode(encrypt((string) $PRO_N_ID, $sEncryptKey)); ?>" class="card h-100 text-decoration-none text-body overflow-hidden">
                        <?php if (isset($aSearchImageByProject[$PRO_N_ID])): ?>
                            <img
                                src="/file.php?PRF_N_ID=<?php echo urlencode(encrypt((string) $aSearchImageByProject[$PRO_N_ID], $sEncryptKey)); ?>&thumb=1"
                                class="card-img-top"
                                alt=""
                                loading="lazy"
                                style="height:180px;object-fit:cover;"
                            >
                        <?php endif; ?>

                        <div class="card-body">
                            <div class="d-flex justify-content-between gap-3 mb-2">
                                <h2 class="h5 mb-0"><?php echo htmlspecialchars($aProject['PRO_CH_LABEL'], ENT_QUOTES, 'UTF-8'); ?></h2>
                                <?php if ($sYears !== ''): ?>
                                    <span class="text-body-secondary text-nowrap"><?php echo htmlspecialchars($sYears, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($aTagsByProject[$PRO_N_ID])): ?>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <?php foreach ($aTagsByProject[$PRO_N_ID] as $aTag): ?>
                                        <span class="badge text-bg-<?php echo htmlspecialchars($aTag['TCA_CH_COLOR'], ENT_QUOTES, 'UTF-8'); ?>">
                                            <?php echo htmlspecialchars($aTag['TAG_CH_LABEL'], ENT_QUOTES, 'UTF-8'); ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <?php if (trim((string) ($aProject['PRO_TX_DESCRIPTION'] ?? '')) !== ''): ?>
                                <p class="mb-0 text-body-secondary">
                                    <?php
                                    $sDescription = trim((string) $aProject['PRO_TX_DESCRIPTION']);

                                    if (mb_strlen($sDescription) > 180) {
                                        $sDescription = mb_substr($sDescription, 0, 177) . '…';
                                    }

                                    echo nl2br(htmlspecialchars($sDescription, ENT_QUOTES, 'UTF-8'));
                                    ?>
                                </p>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</main>

<style>
.search-tag-group .select2-container {
    flex: 1 1 auto;
    width: 1% !important;
}

.search-tag-group .select2-selection--multiple {
    min-height: 42px;
    border-top-left-radius: 0 !important;
    border-bottom-left-radius: 0 !important;
}
</style>

<script>
$(function () {
    $('#tagSearch').select2({
        width: '100%',
        placeholder: 'Tous les tags sélectionnés',
        closeOnSelect: false
    });
});
</script>

<?php require_once __DIR__ . '/bottom.php'; ?>
