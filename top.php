<?php

require_once __DIR__ . '/secure.php';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>archiveCowprod</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" rel="stylesheet">
</head>
<body>
<header class="navbar navbar-expand-md border-bottom bg-body-tertiary">
    <div class="container">
        <a class="navbar-brand" href="/index.php">archiveCowprod</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="text-body-secondary"><?php echo htmlspecialchars(sSignature(), ENT_QUOTES, 'UTF-8'); ?></span>
            <a class="btn btn-sm btn-outline-secondary" href="/logout.php">Déconnexion</a>
        </div>
    </div>
</header>
