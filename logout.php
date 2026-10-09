<?php
/* Encerra a sessão do administrador (somente via POST). */
require_once 'includes/config.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_exigir();
    $_SESSION = [];
    session_destroy();
}
header('Location: index.php');
exit;
