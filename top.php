<?php

require_once __DIR__ . '/secure.php';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>archiveCowprod</title>
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.8/dist/quartz/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .select2-container { flex: 1 1 auto; width: 1% !important; }
        .select2-container .select2-selection--multiple {
            min-height: 38px;
            border: 1px solid var(--bs-light) !important;
            box-shadow: none !important;
            background: transparent;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            border: 0;
            background: transparent;
            padding-left: 0;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__display {
            margin-left: .5rem;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            display: inline-flex;
            align-items: center;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            position: static;
            border: 0;
            background: transparent;
            padding: 0;
            margin: 0;
        }
    </style>
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
