<?php
/* Perfil do administrador: foto (AJAX em perfil_foto.php), login e senha. A cor fica em Configurações. */
require_once 'includes/salvar_receita.php';
exige_admin();

$pdo = db();
$st = $pdo->prepare('SELECT id, usuario, senha_hash, foto FROM usuarios WHERE id = ?');
$st->execute([$_SESSION['admin_id']]);
$u = $st->fetch();
if (!$u) { $_SESSION = []; header('Location: login.php'); exit; }   // usuário não existe mais

$erros = [];
$usuario = $u['usuario'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_exigir();
    $usuario = trim($_POST['usuario'] ?? '');
    $nova    = (string)($_POST['nova_senha'] ?? '');
    $conf    = (string)($_POST['confirmar_senha'] ?? '');

    // --- Validação ---
    if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $usuario))
        $erros[] = 'O login deve ter de 3 a 50 caracteres (letras, números, ponto, hífen ou _).';
    if ($nova !== '' && strlen($nova) < 8) $erros[] = 'A nova senha deve ter ao menos 8 caracteres.';
    if ($nova !== $conf)                   $erros[] = 'A confirmação não confere com a nova senha.';
    if (!$erros) {
        $dup = $pdo->prepare('SELECT 1 FROM usuarios WHERE usuario = ? AND id <> ?');
        $dup->execute([$usuario, $u['id']]);
        if ($dup->fetchColumn()) $erros[] = 'Esse login já está em uso.';
    }

    // --- Gravação (senha em branco = mantém a atual; nova senha sempre criptografada) ---
    if (!$erros) {
        $hash = $nova !== '' ? password_hash($nova, PASSWORD_DEFAULT) : $u['senha_hash'];
        try {
            $pdo->prepare('UPDATE usuarios SET usuario = ?, senha_hash = ? WHERE id = ?')
                ->execute([$usuario, $hash, $u['id']]);
        } catch (PDOException $ex) {
            $erros[] = 'Não foi possível salvar o perfil.';
        }
    }
    if (!$erros) {
        if ($nova !== '') session_regenerate_id(true);
        flash('Perfil atualizado com sucesso.');          // toast na página seguinte
        header('Location: perfil.php');
        exit;
    }
    foreach ($erros as $m) flash($m, 'erro');              // erros: toasts nesta mesma página
}

$titulo_pagina = 'Meu perfil';
require 'includes/header.php';

/** Ícone de olho (aberto + riscado) usado nos campos de senha. */
$olho = '<button type="button" class="olho" aria-label="Mostrar senha" aria-pressed="false">'
  . '<svg class="o-aberto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/><circle cx="12" cy="12" r="3"/></svg>'
  . '<svg class="o-fechado" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 19c-7 0-11-7-11-7a19.77 19.77 0 0 1 5.06-5.94M9.9 4.24A10.94 10.94 0 0 1 12 5c7 0 11 7 11 7a19.8 19.8 0 0 1-3.17 4.19M14.12 14.12a3 3 0 1 1-4.24-4.24"/><path d="M1 1l22 22"/></svg>'
  . '</button>';
?>
<div class="perfil-topo">
  <div class="avatar-wrap">
    <!-- Clicar na foto abre o seletor de arquivo; o envio é feito por perfil.js (Fetch) -->
    <button type="button" id="avatar-btn" class="avatar-lg img-padrao" title="Alterar foto" aria-label="Alterar foto de perfil">
      <?php if ($u['foto']): ?><img src="uploads/<?= e($u['foto']) ?>" alt=""><?php else: echo avatar_svg(); endif; ?>
    </button>
    <button type="button" id="avatar-rm" class="avatar-rm" title="Remover foto" aria-label="Remover foto de perfil"<?= $u['foto'] ? '' : ' hidden' ?>>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" aria-hidden="true"><path d="M7 7l10 10M17 7L7 17"/></svg>
    </button>
  </div>
  <div>
    <h1><?= e($u['usuario']) ?></h1>
    <span class="badge">Administrador</span>
  </div>
</div>
<input type="file" id="foto-input" accept="image/jpeg,image/png,image/webp" hidden>
<template id="tpl-avatar"><?= avatar_svg() ?></template>

<form class="form" method="post" id="form-perfil">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

  <label>Login
    <input type="text" name="usuario" maxlength="50" required autocomplete="username" value="<?= e($usuario) ?>">
  </label>

  <div class="linha">
    <label>Nova senha
      <span class="campo-senha"><input type="password" name="nova_senha" minlength="8" autocomplete="new-password"><?= $olho ?></span>
    </label>
    <label>Confirmar nova senha
      <span class="campo-senha"><input type="password" name="confirmar_senha" autocomplete="new-password"><?= $olho ?></span>
    </label>
  </div>

  <button type="submit">Salvar</button>
</form>
<script src="assets/js/perfil.js?v=<?= (int)@filemtime(__DIR__ . '/assets/js/perfil.js') ?>" defer></script>
<?php require 'includes/footer.php'; ?>
