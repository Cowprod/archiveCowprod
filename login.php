<?php

require_once __DIR__ . '/global.php';

$sMessage = $_SESSION['sLoginError'] ?? '';
unset($_SESSION['sLoginError']);
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion - archiveCowprod</title>
    <link rel="icon" href="/favicon.ico" sizes="any">
    <link href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.8/dist/quartz/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h1 class="h4 mb-4">archiveCowprod</h1>

                    <?php if ($sMessage !== ''): ?>
                        <div class="alert alert-danger"><?php echo htmlspecialchars($sMessage, ENT_QUOTES, 'UTF-8'); ?></div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['logged']) && $_SESSION['logged'] === '1'): ?>
                        <div class="alert alert-info">Vous êtes déjà connecté.</div>
                        <a href="/index.php" class="btn btn-primary w-100">Ouvrir le catalogue</a>
                    <?php else: ?>
                        <form method="post" action="/trLogin.php">
                            <div class="mb-3">
                                <label for="sUser" class="form-label">Login</label>
                                <input type="text" class="form-control" id="sUser" name="sUser" autocomplete="username" required autofocus>
                            </div>

                            <div class="mb-3">
                                <label for="sPassword" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="sPassword" name="sPassword" autocomplete="current-password" required>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Se connecter</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
