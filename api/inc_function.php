<?php

function sSignature(): string
{
    if (!isset($_SESSION['sUser']) || trim((string) $_SESSION['sUser']) === '') {
        throw new RuntimeException('Utilisateur absent de la session');
    }

    return trim((string) $_SESSION['sUser']);
}
