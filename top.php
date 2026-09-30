<?php

require_once __DIR__ . '/secure.php';
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>archiveCowprod</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.8/dist/quartz/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <style>
        .select2-container { flex: 1 1 auto; width: 1% !important; }

        .tag-select-group .select2-container {
            display: flex;
            align-items: stretch;
        }
        .select2-container .select2-selection--multiple {
            min-height: 42px;
            border: 1px solid var(--bs-light) !important;
            box-shadow: none !important;
            background: transparent;
        }

        .tag-select-group .select2-selection--multiple {
            width: 100%;
            height: 100%;
        }

        .tag-admin-button {
            align-self: stretch;
            padding-top: 0;
            padding-bottom: 0;
            min-width: 64px;
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
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

        .autosave-warning {
            border-color: var(--bs-warning) !important;
            box-shadow: 0 0 0 .15rem rgba(var(--bs-warning-rgb), .25) !important;
        }

        .autosave-success {
            border-color: var(--bs-success) !important;
            box-shadow: 0 0 0 .15rem rgba(var(--bs-success-rgb), .25) !important;
        }

        .autosave-danger {
            border-color: var(--bs-danger) !important;
            box-shadow: 0 0 0 .15rem rgba(var(--bs-danger-rgb), .25) !important;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="/assets/js/jquery.typing-0.2.0.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/TableDnD/1.0.5/jquery.tablednd.min.js"></script>
    <script src="/assets/js/script.js"></script>
</head>
<body>
<header class="navbar navbar-expand-md border-bottom bg-body-tertiary">
    <div class="container">
        <a class="navbar-brand" href="/index.php">archiveCowprod</a>
        <div class="ms-auto d-flex align-items-center gap-3">
            <span class="text-body-secondary"><?php echo htmlspecialchars(sSignature(), ENT_QUOTES, 'UTF-8'); ?></span>
            <a class="btn btn-sm btn-secondary" href="/logout.php">Déconnexion</a>
        </div>
    </div>
</header>

<div id="dDumy" class="d-none"></div>

<div class="modal fade" id="dAdminModal" tabindex="-1" aria-labelledby="adminModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adminModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
            </div>
            <div class="modal-body" id="modalAdminBody"></div>
            <div class="modal-footer d-none" id="adminModalFooter"></div>
        </div>
    </div>
</div>
