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

                    <?php if ($sMessage !== ''): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($sMessage, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>

                    <form method="post" action="/trInstall.php">
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

                        <button type="submit" class="btn btn-primary">Installer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
