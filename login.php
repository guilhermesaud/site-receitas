<?php
/* Login do administrador (usuário gravado manualmente na tabela usuarios). */
require_once 'includes/config.php';
if (eh_admin()) { header('Location: index.php'); exit; }

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_exigir();
    $st = db()->prepare('SELECT id, senha_hash FROM usuarios WHERE usuario = ?');
    $st->execute([trim($_POST['usuario'] ?? '')]);
    $u = $st->fetch();
    if ($u && password_verify($_POST['senha'] ?? '', $u['senha_hash'])) {
        session_regenerate_id(true);               // evita fixação de sessão
        $_SESSION['admin_id'] = (int)$u['id'];
        header('Location: index.php');
        exit;
    }
    sleep(1);                                       // dificulta tentativas em massa
    $erro = 'Usuário ou senha incorretos.';
}

$titulo_pagina = 'Entrar';
require 'includes/header.php';
?>
<h1>Entrar</h1>
<?php if ($erro): ?><div class="erro estreito" role="alert"><?= e($erro) ?></div><?php endif; ?>
<form class="form estreito" method="post">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
  <label>Usuário <input type="text" name="usuario" required autofocus autocomplete="username"></label>
  <label>Senha <input type="password" name="senha" required autocomplete="current-password"></label>
  <button type="submit">Entrar</button>
</form>
<?php require 'includes/footer.php'; ?>
