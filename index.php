<?php
declare(strict_types=1);

date_default_timezone_set('Europe/Paris');
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>archiveCowprod</title>
</head>
<body>
    <h1>archiveCowprod</h1>
    <p>Déploiement aaPanel OK.</p>
    <p>PHP <?= htmlspecialchars(PHP_VERSION, ENT_QUOTES, 'UTF-8') ?></p>
    <p><?= htmlspecialchars(date('d/m/Y H:i:s'), ENT_QUOTES, 'UTF-8') ?></p>
</body>
</html>
