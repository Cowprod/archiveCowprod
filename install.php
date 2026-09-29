<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

date_default_timezone_set('Europe/Paris');

if (file_exists(__DIR__ . '/config/config.php')) {
    header('Location: /login.php');
    exit;
}

$sMessage = $_SESSION['sInstallError'] ?? '';
unset($_SESSION['sInstallError']);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Installation - archiveCowprod</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-7">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h3 mb-4">Installation archiveCowprod</h1>

                    <div id="dInstallMessage" class="alert alert-danger<?php echo $sMessage === '' ? ' d-none' : ''; ?>">
                        <?php echo htmlspecialchars($sMessage, ENT_QUOTES, 'UTF-8'); ?>
                    </div>

                    <form id="fInstall" method="post" action="/trInstall.php">
                        <h2 class="h5 mt-2">Catalogue</h2>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="sUser" class="form-label">Login</label>
                                <input type="text" class="form-control" id="sUser" name="sUser" autocomplete="username" required>
                            </div>

                            <div class="col-md-6"></div>

                            <div class="col-md-6">
                                <label for="sPassword" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="sPassword" name="sPassword" autocomplete="new-password" required>
                            </div>

                            <div class="col-md-6">
                                <label for="sPasswordConfirm" class="form-label">Confirmation</label>
                                <input type="password" class="form-control" id="sPasswordConfirm" name="sPasswordConfirm" autocomplete="new-password" required>
                            </div>
                        </div>

                        <h2 class="h5">MariaDB</h2>

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label for="sDbHost" class="form-label">Serveur</label>
                                <input type="text" class="form-control" id="sDbHost" name="sDbHost" value="localhost" required>
                            </div>

                            <div class="col-md-6">
                                <label for="sDbName" class="form-label">Base</label>
                                <input type="text" class="form-control" id="sDbName" name="sDbName" value="archiveCowprod" required>
                            </div>

                            <div class="col-md-6">
                                <label for="sDbUser" class="form-label">Utilisateur</label>
                                <input type="text" class="form-control" id="sDbUser" name="sDbUser" required>
                            </div>

                            <div class="col-md-6">
                                <label for="sDbPassword" class="form-label">Mot de passe DB</label>
                                <input type="password" class="form-control" id="sDbPassword" name="sDbPassword" autocomplete="off">
                            </div>
                        </div>

                        <h2 class="h5">Stockage</h2>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label for="sStoragePath" class="form-label">Chemin des fichiers</label>
                                <input type="text" class="form-control font-monospace" id="sStoragePath" name="sStoragePath" placeholder="/mnt/data/archiveCowprod" required>
                            </div>

                            <div class="col-12">
                                <label for="sStorageCachePath" class="form-label">Chemin du cache des vignettes</label>
                                <input type="text" class="form-control font-monospace" id="sStorageCachePath" name="sStorageCachePath" placeholder="/mnt/data/archiveCowprod/cache" required>
                            </div>
                        </div>

                        <button id="bInstall" type="submit" class="btn btn-primary">
                            <span id="spInstallSpinner" class="spinner-border spinner-border-sm me-2 d-none" aria-hidden="true"></span>
                            Installer
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function () {
    $('#fInstall').on('submit', function (e) {
        e.preventDefault();

        const $form = $(this);
        const $button = $('#bInstall');
        const $spinner = $('#spInstallSpinner');
        const $message = $('#dInstallMessage');

        $message.addClass('d-none').text('');
        $button.prop('disabled', true);
        $spinner.removeClass('d-none');

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: $form.serialize(),
            dataType: 'json',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .done(function (data) {
            if (data.success === true) {
                window.location.href = data.redirect || '/index.php';
                return;
            }

            $message.removeClass('d-none').text(data.message || 'Erreur lors de l’installation');
        })
        .fail(function (xhr, textStatus, errorThrown) {
            let sMessage = '';

            if (xhr.responseJSON && xhr.responseJSON.message) {
                sMessage = xhr.responseJSON.message;
            } else if (xhr.responseText) {
                try {
                    const data = JSON.parse(xhr.responseText);

                    if (data.message) {
                        sMessage = data.message;
                    }
                } catch (e) {
                    sMessage = xhr.responseText.replace(/<[^>]*>/g, ' ').replace(/\\s+/g, ' ').trim();
                }
            }

            if (!sMessage) {
                sMessage = 'Erreur HTTP ' + xhr.status;

                if (errorThrown) {
                    sMessage += ' - ' + errorThrown;
                }
            }

            $message.removeClass('d-none').text(sMessage);
        })
        .always(function () {
            $button.prop('disabled', false);
            $spinner.addClass('d-none');
        });
    });
});
</script>
</body>
</html>
