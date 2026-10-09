<?php
/* Cabeçalho global: menu, busca, ícone de tema e menu do perfil (só logado). */
require_once __DIR__ . '/config.php';
$q = trim($_GET['q'] ?? '');

$admin = null;
if (eh_admin()) {
    $st = db()->prepare('SELECT usuario, foto, cor_destaque FROM usuarios WHERE id = ?');
    $st->execute([$_SESSION['admin_id']]);
    $admin = $st->fetch() ?: ['usuario' => 'Administrador', 'foto' => null, 'cor_destaque' => 'neutro'];
}
// Cor de destaque: só vale para o administrador logado (visitantes ficam no neutro)
$acc = ($admin && ($admin['cor_destaque'] ?? 'neutro') !== 'neutro' && isset(CORES[$admin['cor_destaque']])) ? 'acc-' . $admin['cor_destaque'] : '';

// Fonte customizada: usa o PRIMEIRO arquivo de fonte (.woff2/.woff/.ttf/.otf) de assets/fonts/ — o nome do arquivo não importa
$fonte = null;
$formatos = ['woff2' => 'woff2', 'woff' => 'woff', 'ttf' => 'truetype', 'otf' => 'opentype'];
foreach (glob(__DIR__ . '/../assets/fonts/*') ?: [] as $arq) {
    $ext = strtolower(pathinfo($arq, PATHINFO_EXTENSION));
    if (isset($formatos[$ext])) { $fonte = ['url' => 'assets/fonts/' . rawurlencode(basename($arq)), 'fmt' => $formatos[$ext]]; break; }
}
?><!DOCTYPE html>
<html lang="pt-BR"<?= $acc ? ' class="' . e($acc) . '"' : '' ?>>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo_pagina ?? 'Minhas Receitas') ?> | Minhas Receitas</title>
  <!-- Aplica o tema salvo ANTES de pintar a página (evita o "flash" de tema claro) -->
  <script>try{var t=localStorage.getItem('tema');if(t==='dark'||(!t&&matchMedia('(prefers-color-scheme: dark)').matches))document.documentElement.classList.add('dark-mode')}catch(e){}</script>
  <?php if ($fonte): ?><style>@font-face{font-family:"Muro";src:url("<?= $fonte['url'] ?>") format("<?= $fonte['fmt'] ?>");font-weight:400;font-style:normal;font-display:swap}</style><?php endif; ?>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<header class="topo">
  <div class="container topo-in">
    <a class="logo" href="index.php">Minhas Receitas</a>
    <nav aria-label="Principal">
      <a href="index.php">Início</a>
      <?php if ($admin): ?><a href="nova_receita.php">Nova Receita</a><?php endif; ?>
    </nav>
    <form class="busca" action="index.php" method="get" role="search">
      <input type="search" name="q" value="<?= e($q) ?>" aria-label="Buscar receitas por nome ou ingrediente">
      <button type="submit" aria-label="Buscar">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
      </button>
    </form>

    <!-- Tema: lua no modo claro (vai para o escuro) / sol no modo escuro (vai para o claro) -->
    <button type="button" id="tema-toggle" class="sec icon-btn" aria-label="Mudar para o tema escuro" title="Mudar para o tema escuro">
      <svg class="ic-lua" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      <svg class="ic-sol" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
    </button>

    <?php if ($admin): ?>
      <div class="perfil-menu">
        <button type="button" id="perfil-btn" class="avatar-btn img-padrao" aria-haspopup="true" aria-expanded="false" aria-controls="perfil-dd" aria-label="Menu do perfil">
          <?php if ($admin['foto']): ?><img src="uploads/<?= e($admin['foto']) ?>" alt=""><?php else: echo avatar_svg(); endif; ?>
        </button>
        <div id="perfil-dd" class="dropdown">
          <div class="dd-user"><strong><?= e($admin['usuario']) ?></strong><small>Administrador</small></div>
          <a href="perfil.php">Perfil</a>
          <a href="configuracoes.php">Configurações</a>
          <form method="post" action="logout.php">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <button type="submit">Sair</button>
          </form>
        </div>
      </div>
    <?php else: ?>
      <a class="btn-entrar" href="login.php">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><path d="M10 17l5-5-5-5"/><path d="M15 12H3"/></svg>
        <span>Entrar</span>
      </a>
    <?php endif; ?>
  </div>
</header>
<main class="container">
