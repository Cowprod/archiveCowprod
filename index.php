<?php

require_once __DIR__ . '/secure.php';

$sSearch = trim((string) ($_GET['q'] ?? ''));
$sYear = trim((string) ($_GET['year'] ?? ''));
$aSearchTagIds = $_GET['tag'] ?? [];
$sMaintenance = trim((string) ($_GET['maintenance'] ?? ''));
$sMaintenanceTCAId = trim((string) ($_GET['maintenanceTCA_N_ID'] ?? ''));
$nMaintenanceTCAId = null;
$sMaintenanceLabel = '';

if (!in_array($sMaintenance, ['tag', 'year', 'url', 'file'], true)) {
    $sMaintenance = '';
}

if ($sMaintenance === 'tag') {
    if ($sMaintenanceTCAId !== '' && $sMaintenanceTCAId !== 'null') {
        $nMaintenanceTCAId = decryptId($sMaintenanceTCAId, $sEncryptKey);
    } else {
        $sMaintenance = '';
    }
}

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

$sSearchFilter = '';
$sYearFilter = '';
$sTagFilter = '';

if ($sSearch !== '') {
    $sLike = prepString2Update('%' . $sSearch . '%');

    $sSearchFilter = 'AND (
        T_PROJECT.PRO_CH_LABEL LIKE ' . $sLike . '
        OR T_PROJECT.PRO_CH_DESCRIPTION LIKE ' . $sLike . '
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
    $sYearFilter = 'AND (
        (T_PROJECT.PRO_N_YEARSTART IS NULL OR T_PROJECT.PRO_N_YEARSTART <= ' . prepNum2Update($nSearchYear) . ')
        AND (T_PROJECT.PRO_N_YEAREND IS NULL OR T_PROJECT.PRO_N_YEAREND >= ' . prepNum2Update($nSearchYear) . ')
        AND (T_PROJECT.PRO_N_YEARSTART IS NOT NULL OR T_PROJECT.PRO_N_YEAREND IS NOT NULL)
    )';
}

if (count($aSearchTagIds) > 0) {
    $aPreparedTagIds = array_map('prepNum2Update', $aSearchTagIds);

    $sTagFilter = 'AND (
        SELECT COUNT(DISTINCT T_PROJECTTAG.TAG_N_ID)
        FROM T_PROJECTTAG
        WHERE T_PROJECTTAG.PRO_N_ID = T_PROJECT.PRO_N_ID
          AND T_PROJECTTAG.PTA_DT_SUPPRESSION IS NULL
          AND T_PROJECTTAG.TAG_N_ID IN (' . implode(',', $aPreparedTagIds) . ')
    ) = ' . prepNum2Update(count($aSearchTagIds));
}

$nArchiveUrlTypeId = (int) getfield(
    'UTY_N_ID',
    'T_URLTYPE',
    'WHERE UTY_DT_SUPPRESSION IS NULL AND UTY_CH_LABEL=' . prepString2Update('Archive'),
    $WM_ADMIN_conn
);

if ($sMaintenance === 'url' && $nArchiveUrlTypeId <= 0) {
    $sMaintenance = '';
}

if ($sMaintenance === 'tag' && $nMaintenanceTCAId !== null) {
    $sMaintenanceCategoryLabel = (string) getfield(
        'TCA_CH_LABEL',
        'T_TAGCATEGORY',
        'WHERE TCA_N_ID=' . prepNum2Update($nMaintenanceTCAId) . ' AND TCA_DT_SUPPRESSION IS NULL',
        $WM_ADMIN_conn
    );

    if ($sMaintenanceCategoryLabel === '') {
        $sMaintenance = '';
        $nMaintenanceTCAId = null;
    } else {
        $sMaintenanceLabel = 'Sans ' . mb_strtolower($sMaintenanceCategoryLabel);
    }
}

if ($sMaintenance === 'tag') {
    $aProjects = oRs(
        '',
        __DIR__ . '/maintenanceProjectWithoutTag.sql',
        'TCA_N_ID=' . prepNum2Update($nMaintenanceTCAId),
        0,
        '',
        $WM_ADMIN_conn
    );
} elseif ($sMaintenance === 'year') {
    $sMaintenanceLabel = 'Sans année';
    $aProjects = oRs('', __DIR__ . '/maintenanceProjectWithoutYear.sql', '', 0, '', $WM_ADMIN_conn);
} elseif ($sMaintenance === 'url') {
    $sMaintenanceLabel = 'Sans URL archive';
    $aProjects = oRs(
        '',
        __DIR__ . '/maintenanceProjectWithoutUrl.sql',
        'UTY_N_ID=' . prepNum2Update($nArchiveUrlTypeId),
        0,
        '',
        $WM_ADMIN_conn
    );
} elseif ($sMaintenance === 'file') {
    $sMaintenanceLabel = 'Sans fichier';
    $aProjects = oRs('', __DIR__ . '/maintenanceProjectWithoutFile.sql', '', 0, '', $WM_ADMIN_conn);
} else {
    $aProjects = oRs(
        '',
        __DIR__ . '/catalogue.sql',
        'SEARCH_FILTER=' . urlencode($sSearchFilter)
            . '&YEAR_FILTER=' . urlencode($sYearFilter)
            . '&TAG_FILTER=' . urlencode($sTagFilter),
        0,
        '',
        $WM_ADMIN_conn
    );
}

$aProjectTags = oRs('', __DIR__ . '/catalogueTag.sql', '', 0, '', $WM_ADMIN_conn);

$aTagsByProject = [];

foreach ($aProjectTags as $aTag) {
    $aTagsByProject[(int) $aTag['PRO_N_ID']][] = $aTag;
}

$aSearchTags = oRs('', __DIR__ . '/catalogueSearchTag.sql', '', 0, '', $WM_ADMIN_conn);
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

$aYearCoverageRows = oRs('', __DIR__ . '/catalogueYearCoverage.sql', '', 0, '', $WM_ADMIN_conn);
$nCoverageYearStart = 1997;
$nCoverageYearEnd = (int) date('Y');
$aCoveredYears = array_fill_keys(range($nCoverageYearStart, $nCoverageYearEnd), false);

foreach ($aYearCoverageRows as $aYearCoverageRow) {
    $nProjectYearStart = $aYearCoverageRow['PRO_N_YEARSTART'] !== null
        ? (int) $aYearCoverageRow['PRO_N_YEARSTART']
        : $nCoverageYearStart;
    $nProjectYearEnd = $aYearCoverageRow['PRO_N_YEAREND'] !== null
        ? (int) $aYearCoverageRow['PRO_N_YEAREND']
        : $nCoverageYearEnd;

    $nProjectYearStart = max($nProjectYearStart, $nCoverageYearStart);
    $nProjectYearEnd = min($nProjectYearEnd, $nCoverageYearEnd);

    if ($nProjectYearStart <= $nProjectYearEnd) {
        for ($nCoverageYear = $nProjectYearStart; $nCoverageYear <= $nProjectYearEnd; $nCoverageYear++) {
            $aCoveredYears[$nCoverageYear] = true;
        }
    }
}

$aMaintenanceTagCategories = oRs('', __DIR__ . '/maintenanceTagCategory.sql', '', 0, '', $WM_ADMIN_conn);

$nMaintenanceWithoutYear = (int) getfield(
    'COUNT(*)',
    'T_PROJECT',
    'WHERE T_PROJECT.PRO_DT_SUPPRESSION IS NULL'
        . ' AND T_PROJECT.PRO_N_YEARSTART IS NULL'
        . ' AND T_PROJECT.PRO_N_YEAREND IS NULL',
    $WM_ADMIN_conn
);

$nMaintenanceWithoutUrl = 0;

if ($nArchiveUrlTypeId > 0) {
    $nMaintenanceWithoutUrl = (int) getfield(
        'COUNT(*)',
        'T_PROJECT',
        'WHERE T_PROJECT.PRO_DT_SUPPRESSION IS NULL'
            . ' AND NOT EXISTS ('
            . 'SELECT 1 FROM T_PROJECTURL'
            . ' WHERE T_PROJECTURL.PRO_N_ID=T_PROJECT.PRO_N_ID'
            . ' AND T_PROJECTURL.PRU_DT_SUPPRESSION IS NULL'
            . ' AND T_PROJECTURL.UTY_N_ID=' . prepNum2Update($nArchiveUrlTypeId)
            . ')',
        $WM_ADMIN_conn
    );
}

$nMaintenanceWithoutFile = (int) getfield(
    'COUNT(*)',
    'T_PROJECT',
    'WHERE T_PROJECT.PRO_DT_SUPPRESSION IS NULL'
        . ' AND NOT EXISTS ('
        . 'SELECT 1 FROM T_PROJECTFILE'
        . ' WHERE T_PROJECTFILE.PRO_N_ID=T_PROJECT.PRO_N_ID'
        . ' AND T_PROJECTFILE.PRF_DT_SUPPRESSION IS NULL'
        . ')',
    $WM_ADMIN_conn
);

$aSearchImages = oRs('', __DIR__ . '/catalogueSearchImage.sql', '', 0, '', $WM_ADMIN_conn);
$aSearchImageByProject = [];

foreach ($aSearchImages as $aImage) {
    $aSearchImageByProject[(int) $aImage['PRO_N_ID']] = (int) $aImage['PRF_N_ID'];
}

require_once __DIR__ . '/top.php';
?>

<main class="container py-4">
    <div class="d-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0">Catalogue</h1>

        <form method="post" action="/project/trUpdProject.php">
            <button type="submit" class="btn btn-success">
                <i class="fa fa-plus me-2"></i>Nouveau projet
            </button>
        </form>
    </div>

    <form method="get" class="card mb-4">
        <div class="card-header">Recherche</div>
        <div class="card-body">
            <div class="input-group search-input-group">
                <span class="input-group-text"><i class="fa fa-search me-2"></i>Texte</span>
                <input
                    type="search"
                    class="form-control search-text"
                    id="q"
                    name="q"
                    value="<?php echo htmlspecialchars($sSearch, ENT_QUOTES, 'UTF-8'); ?>"
                >

                <span class="input-group-text">Année</span>
                <input
                    type="number"
                    min="1900"
                    max="2100"
                    class="form-control search-year <?php echo $bInvalidYear ? 'is-invalid' : ''; ?>"
                    id="year"
                    name="year"
                    value="<?php echo htmlspecialchars($sYear, ENT_QUOTES, 'UTF-8'); ?>"
                >

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

                <button type="submit" class="btn btn-success">
                    <i class="fa fa-search me-2"></i>Rechercher
                </button>
                <?php if ($sSearch !== '' || $sYear !== '' || count($aSearchTagIds) > 0): ?>
                    <a href="/index.php" class="btn btn-light" title="Effacer la recherche">
                        <i class="fa fa-times"></i>
                    </a>
                <?php endif; ?>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-1 mt-3">
                <span class="small text-body-secondary me-1"><i class="fa fa-calendar me-1"></i>Couverture</span>

                <?php foreach ($aCoveredYears as $nCoverageYear => $bCoverageYearCovered): ?>
                    <?php if ($bCoverageYearCovered): ?>
                        <button
                            type="button"
                            class="btn btn-sm text-dark js-coverage-year <?php echo $nSearchYear === (int) $nCoverageYear ? 'btn-warning' : 'btn-light'; ?>"
                            data-year="<?php echo (int) $nCoverageYear; ?>"
                            title="Rechercher les projets couvrant <?php echo (int) $nCoverageYear; ?>"
                        ><?php echo (int) $nCoverageYear; ?></button>
                    <?php else: ?>
                        <button
                            type="button"
                            class="btn btn-sm btn-secondary"
                            disabled
                            title="Aucun projet pour <?php echo (int) $nCoverageYear; ?>"
                        ><?php echo (int) $nCoverageYear; ?></button>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <?php if (count($aMaintenanceTagCategories) > 0 || $nMaintenanceWithoutYear > 0 || $nMaintenanceWithoutUrl > 0 || $nMaintenanceWithoutFile > 0): ?>
                <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                    <span class="small text-body-secondary me-1"><i class="fa fa-wrench me-1"></i>Maintenance</span>

                    <?php foreach ($aMaintenanceTagCategories as $aMaintenanceCategory): ?>
                        <?php
                        $nMaintenanceCategoryId = (int) $aMaintenanceCategory['TCA_N_ID'];
                        $bMaintenanceCategoryActive = $sMaintenance === 'tag' && $nMaintenanceTCAId === $nMaintenanceCategoryId;
                        ?>
                        <a
                            href="/index.php?maintenance=tag&maintenanceTCA_N_ID=<?php echo urlencode(encrypt((string) $nMaintenanceCategoryId, $sEncryptKey)); ?>"
                            class="btn btn-sm text-dark <?php echo $bMaintenanceCategoryActive ? 'btn-warning' : 'btn-light'; ?>"
                            title="<?php echo (int) $aMaintenanceCategory['N_MISSING']; ?> projet<?php echo (int) $aMaintenanceCategory['N_MISSING'] > 1 ? 's' : ''; ?> concerné<?php echo (int) $aMaintenanceCategory['N_MISSING'] > 1 ? 's' : ''; ?>"
                        >Sans <?php echo htmlspecialchars(mb_strtolower((string) $aMaintenanceCategory['TCA_CH_LABEL']), ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php endforeach; ?>

                    <?php if ($nMaintenanceWithoutYear > 0): ?>
                        <a
                            href="/index.php?maintenance=year"
                            class="btn btn-sm text-dark <?php echo $sMaintenance === 'year' ? 'btn-warning' : 'btn-light'; ?>"
                            title="<?php echo $nMaintenanceWithoutYear; ?> projet<?php echo $nMaintenanceWithoutYear > 1 ? 's' : ''; ?> concerné<?php echo $nMaintenanceWithoutYear > 1 ? 's' : ''; ?>"
                        >Sans année</a>
                    <?php endif; ?>

                    <?php if ($nMaintenanceWithoutUrl > 0): ?>
                        <a
                            href="/index.php?maintenance=url"
                            class="btn btn-sm text-dark <?php echo $sMaintenance === 'url' ? 'btn-warning' : 'btn-light'; ?>"
                            title="<?php echo $nMaintenanceWithoutUrl; ?> projet<?php echo $nMaintenanceWithoutUrl > 1 ? 's' : ''; ?> concerné<?php echo $nMaintenanceWithoutUrl > 1 ? 's' : ''; ?>"
                        >Sans URL archive</a>
                    <?php endif; ?>

                    <?php if ($nMaintenanceWithoutFile > 0): ?>
                        <a
                            href="/index.php?maintenance=file"
                            class="btn btn-sm text-dark <?php echo $sMaintenance === 'file' ? 'btn-warning' : 'btn-light'; ?>"
                            title="<?php echo $nMaintenanceWithoutFile; ?> projet<?php echo $nMaintenanceWithoutFile > 1 ? 's' : ''; ?> concerné<?php echo $nMaintenanceWithoutFile > 1 ? 's' : ''; ?>"
                        >Sans fichier</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </form>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="text-body-secondary">
            <?php echo count($aProjects); ?> projet<?php echo count($aProjects) > 1 ? 's' : ''; ?>
            <?php if ($sMaintenanceLabel !== ''): ?>
                · <?php echo htmlspecialchars($sMaintenanceLabel, ENT_QUOTES, 'UTF-8'); ?>
            <?php endif; ?>
        </span>
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

                            <?php if (trim((string) ($aProject['PRO_CH_DESCRIPTION'] ?? '')) !== ''): ?>
                                <p class="mb-0 text-body-secondary">
                                    <?php
                                    $sDescription = trim((string) $aProject['PRO_CH_DESCRIPTION']);

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
.search-input-group .search-text {
    flex: 2 1 260px;
}

.search-input-group .search-year {
    flex: 0 0 110px;
    max-width: 110px;
}

.search-input-group #tagSearch + .select2-container {
    flex: 2 1 320px;
    width: auto !important;
    min-width: 220px;
}
</style>

<script>
$(function () {
    $('.js-coverage-year').on('click', function () {
        $('#year').val($(this).data('year'));
        $(this).closest('form').trigger('submit');
    });

    $('#tagSearch').select2({
        width: '100%',
        placeholder: 'Tous les tags sélectionnés',
        closeOnSelect: false
    });
});
</script>

<?php require_once __DIR__ . '/bottom.php'; ?>
