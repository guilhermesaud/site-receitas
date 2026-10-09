<?php
/* Página inicial: grid de receitas (com filtro de busca opcional). */
$titulo_pagina = 'Início';
require 'includes/header.php';

$sql = 'SELECT r.id, r.titulo, c.nome AS categoria, r.dificuldade, r.tempo_preparo, r.imagem_capa FROM receitas r JOIN categorias c ON c.id = r.categoria_id';
$params = [];
if ($q !== '') {
    $sql .= ' WHERE r.titulo LIKE :q1 OR r.ingredientes LIKE :q2';
    $params = [':q1' => "%$q%", ':q2' => "%$q%"];
}
$stmt = db()->prepare($sql . ' ORDER BY r.criado_em DESC');
$stmt->execute($params);
$receitas = $stmt->fetchAll();
?>
<h1><?= $q !== '' ? 'Resultados para “' . e($q) . '”' : 'Receitas' ?></h1>

<?php if (!$receitas): ?>
  <p class="vazio">
    <?= $q !== '' ? 'Nenhuma receita encontrada.' : 'Você ainda não tem receitas.' ?>
    <a href="nova_receita.php">Cadastre a primeira receita</a>.
  </p>
<?php else: ?>
  <div class="grid">
    <?php foreach ($receitas as $r): ?>
      <a class="card" href="receita.php?id=<?= (int)$r['id'] ?>">
        <?php if ($r['imagem_capa']): ?>
          <img class="thumb" src="uploads/<?= e($r['imagem_capa']) ?>" alt="<?= e($r['titulo']) ?>" loading="lazy">
        <?php else: ?>
          <div class="thumb sem-foto">Sem foto</div>
        <?php endif; ?>
        <div class="card-b">
          <h2><?= e($r['titulo']) ?></h2>
          <span class="tag"><?= e($r['categoria']) ?></span>
          <div class="meta">
            <span>Dificuldade: <?= e($r['dificuldade']) ?></span>
            <span>Preparo: <?= e(fmt_tempo((int)$r['tempo_preparo'])) ?></span>
          </div>
        </div>
      </a>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
<?php require 'includes/footer.php'; ?>
