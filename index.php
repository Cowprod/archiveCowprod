<?php

require_once __DIR__ . '/secure.php';

$oProjects = $WM_ADMIN_conn->query(
    'SELECT
        T_PROJECT.PRO_N_ID,
        T_PROJECT.PRO_CH_LABEL,
        T_PROJECT.PRO_TX_DESCRIPTION,
        T_PROJECT.PRO_N_YEARSTART,
        T_PROJECT.PRO_N_YEAREND
     FROM T_PROJECT
     WHERE T_PROJECT.PRO_DT_SUPPRESSION IS NULL
     ORDER BY
        T_PROJECT.PRO_N_YEARSTART DESC,
        T_PROJECT.PRO_CH_LABEL ASC'
);

$aProjects = $oProjects->fetchAll();

$oProjectTags = $WM_ADMIN_conn->query(
    'SELECT
        T_PROJECTTAG.PRO_N_ID,
        T_TAG.TAG_CH_LABEL,
        T_TAGCATEGORY.TCA_CH_COLOR
     FROM T_PROJECTTAG
     INNER JOIN T_TAG
        ON T_TAG.TAG_N_ID = T_PROJECTTAG.TAG_N_ID
       AND T_TAG.TAG_DT_SUPPRESSION IS NULL
     INNER JOIN T_TAGCATEGORY
        ON T_TAGCATEGORY.TCA_N_ID = T_TAG.TCA_N_ID
       AND T_TAGCATEGORY.TCA_DT_SUPPRESSION IS NULL
     WHERE T_PROJECTTAG.PTA_DT_SUPPRESSION IS NULL
     ORDER BY
        T_TAGCATEGORY.TCA_N_ORDER ASC,
        T_TAG.TAG_N_ORDER ASC,
        T_TAG.TAG_CH_LABEL ASC'
);

$aTagsByProject = [];

foreach ($oProjectTags->fetchAll() as $aTag) {
    $aTagsByProject[(int) $aTag['PRO_N_ID']][] = $aTag;
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

    <?php if (count($aProjects) === 0): ?>
        <div class="card">
            <div class="card-body text-body-secondary">
                Aucun projet pour le moment.
            </div>
        </div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($aProjects as $aProject): ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <a
                        href="/project/upd.php?PRO_N_ID=<?php echo (int) $aProject['PRO_N_ID']; ?>"
                        class="card h-100 text-decoration-none text-body"
                    >
                        <div class="card-body">
                            <div class="d-flex justify-content-between gap-3 mb-2">
                                <h2 class="h5 mb-0">
                                    <?php echo htmlspecialchars($aProject['PRO_CH_LABEL'], ENT_QUOTES, 'UTF-8'); ?>
                                </h2>

                                <?php
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

                                <?php if ($sYears !== ''): ?>
                                    <span class="text-body-secondary text-nowrap"><?php echo htmlspecialchars($sYears, ENT_QUOTES, 'UTF-8'); ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($aTagsByProject[(int) $aProject['PRO_N_ID']])): ?>
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <?php foreach ($aTagsByProject[(int) $aProject['PRO_N_ID']] as $aTag): ?>
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

<?php require_once __DIR__ . '/bottom.php'; ?>
