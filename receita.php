<?php
/* Visualização completa de uma receita (receita.php?id=N). */
require_once 'includes/config.php';
require_once 'includes/icones.php';

/** Divide o texto dos ingredientes em grupos. Uma linha que começa com "-" (ex.: "- Recheio") é o título de um novo grupo. */
function ingredientes_agrupados(string $texto): array {
    $grupos = [['titulo' => '', 'itens' => []]];
    foreach (linhas($texto) as $linha) {
        if (preg_match('/^[-\x{2013}\x{2014}]\s*(.*)$/u', $linha, $m)) {   // traço (também aceita – e —) = título de grupo
            $titulo = trim(rtrim(trim($m[1]), ':'));                          // "- Recheio:" também vale; o ":" some
            if ($titulo === '') continue;
            $grupos[] = ['titulo' => $titulo, 'itens' => []];
        } else {
            $grupos[count($grupos) - 1]['itens'][] = $linha;                 // item do grupo atual (ou do grupo sem título, no início)
        }
    }
    return array_values(array_filter($grupos, fn($g) => $g['titulo'] !== '' || $g['itens']));
}

$stmt = db()->prepare('SELECT r.*, DATE_FORMAT(r.atualizado_em, \'%d/%m/%Y %H:%i\') AS atualizado_fmt, c.nome AS categoria, o.nome AS ocasiao, u.nome AS culinaria, k.nome AS custo
       FROM receitas r JOIN categorias c ON c.id = r.categoria_id
       LEFT JOIN ocasioes o ON o.id = r.ocasiao_id
       LEFT JOIN culinarias u ON u.id = r.culinaria_id
       LEFT JOIN custos k ON k.id = r.custo_id
      WHERE r.id = ?');
$stmt->execute([(int)($_GET['id'] ?? 0)]);
$r = $stmt->fetch();

if (!$r) {
    http_response_code(404);
    $titulo_pagina = 'Receita não encontrada';
    require 'includes/header.php';
    echo '<p class="vazio">Receita não encontrada. <a href="index.php">Voltar ao início</a>.</p>';
    require 'includes/footer.php';
    exit;
}

$titulo_pagina = $r['titulo'];
$video = youtube_embed($r['video_url']);
$ps = db()->prepare('SELECT titulo, instrucao, imagem FROM passos WHERE receita_id = ? ORDER BY ordem');
$ps->execute([$r['id']]);
$passos = $ps->fetchAll();
$ut = db()->prepare('SELECT ru.quantidade, u.nome FROM receita_utensilios ru JOIN utensilios u ON u.id = ru.utensilio_id WHERE ru.receita_id = ? ORDER BY ru.id');
$ut->execute([$r['id']]);
$utens = $ut->fetchAll();

// Layout dos ingredientes (escolhido em Configurações > Layout). Sem a tabela/valor, usa "etiqueta".
$layout_ing = 'etiqueta';
try {
    $v = db()->query("SELECT valor FROM ajustes WHERE chave = 'layout_ingredientes'")->fetchColumn();
    if (in_array($v, ['subtitulo', 'cartoes', 'etiqueta'], true)) $layout_ing = $v;
} catch (PDOException $ex) { /* tabela ainda não criada: mantém o padrão */ }
$grupos = ingredientes_agrupados($r['ingredientes']);
$ing_simples = count($grupos) <= 1 && ($grupos[0]['titulo'] ?? '') === '';   // sem títulos: lista simples, como antes
$ico = icones_secoes();                                                       // ícones dos títulos de seção (Configurações > Layout)
$ih  = [];                                                                    // HTML do ícone de cada seção ('' = sem ícone)
foreach (SECOES_ICONE as $k => $rot) $ih[$k] = icone_html($ico[$k]);
require 'includes/header.php';
?>
<article class="receita" style="--ico-tam:<?= (int)$ico['tamanho'] ?>px">
  <?php if (eh_admin()): ?>
    <!-- Ícones no canto superior direito (posição absoluta dentro do article) -->
    <div class="acoes">
      <a class="icon-btn sec" href="editar_receita.php?id=<?= (int)$r['id'] ?>" title="Editar" aria-label="Editar receita">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 3a2.83 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/></svg>
      </a>
      <form method="post" action="excluir_receita.php" onsubmit="return confirm('Excluir esta receita e todas as imagens dela? Não dá para desfazer.')">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
        <button type="submit" class="icon-btn sec exc" title="Excluir" aria-label="Excluir receita">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>
        </button>
      </form>
    </div>
  <?php endif; ?>

  <h1><?= e($r['titulo']) ?></h1>
  <div class="tags">
    <span class="tag"><?= e($r['categoria']) ?></span>
    <?php if ($r['ocasiao']): ?><span class="tag"><?= e($r['ocasiao']) ?></span><?php endif; ?>
    <?php if ($r['culinaria']): ?><span class="tag"><?= e($r['culinaria']) ?></span><?php endif; ?>
    <!-- Última atualização: mesmo estilo das tags, com ícone de relógio, à direita -->
    <span class="tag tag-data" title="Última atualização"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg><span class="sr">Última atualização: </span><?= e($r['atualizado_fmt']) ?></span>
  </div>

  <!-- Topo: capa (~80%) + informações rápidas empilhadas (~20%) -->
  <div class="receita-topo<?= $r['imagem_capa'] ? '' : ' sem-capa' ?>">
    <?php if ($r['imagem_capa']): ?>
      <div class="capa img-padrao"><img src="uploads/<?= e($r['imagem_capa']) ?>" alt="<?= e($r['titulo']) ?>"></div>
    <?php endif; ?>
    <div class="info">
      <div><small>Dificuldade</small><?= e($r['dificuldade']) ?></div>
      <div><small>Tempo de preparo</small><?= e(fmt_tempo((int)$r['tempo_preparo'])) ?></div>
      <div><small>Rendimento</small><?= e($r['rendimento']) ?></div>
      <?php if ($r['custo']): ?><div><small>Custo</small><?= e($r['custo']) ?></div><?php endif; ?>
    </div>
  </div>

  <?php if ($video): ?>
    <iframe class="video" src="<?= e($video) ?>" title="Vídeo da receita" allowfullscreen loading="lazy"></iframe>
  <?php endif; ?>

  <div class="ingr-utens<?= $utens ? '' : ' so-ingr' ?>">
    <section class="ing <?= $ing_simples ? 'ing-simples' : 'ing-' . e($layout_ing) ?>">
      <h2><?= $ih['ingredientes'] ?>Ingredientes</h2>
      <div class="ing-grupos<?= $ih['ingredientes'] !== '' ? ' recuo' : '' ?>">
        <?php foreach ($grupos as $g): ?>
          <div class="ing-grupo">
            <?php if ($g['titulo'] !== ''): ?><h3 class="ing-titulo<?= $layout_ing === 'etiqueta' ? ' tag' : '' ?>"><?= e($g['titulo']) ?></h3><?php endif; ?>
            <?php if ($g['itens']): ?><ul><?php foreach ($g['itens'] as $i): ?><li><?= e($i) ?></li><?php endforeach; ?></ul><?php endif; ?>
          </div>
        <?php endforeach; ?>
      </div>
    </section>
    <?php if ($utens): ?>   <!-- sem utensílios, a seção e o título não aparecem -->
      <section>
        <h2><?= $ih['utensilios'] ?>Utensílios</h2>
        <ul<?= $ih['utensilios'] !== '' ? ' class="recuo"' : '' ?>><?php foreach ($utens as $u): ?><li><?= (int)$u['quantidade'] ?>× <?= e($u['nome']) ?></li><?php endforeach; ?></ul>
      </section>
    <?php endif; ?>
  </div>

  <h2><?= $ih['preparo'] ?>Modo de preparo</h2>
  <ol<?= $ih['preparo'] !== '' ? ' class="recuo"' : '' ?>>
    <?php foreach ($passos as $p): ?>
      <li><?php if ($p['titulo']): ?><strong class="passo-titulo"><?= e($p['titulo']) ?></strong><?php endif; ?><?= nl2br(e($p['instrucao'])) ?>
        <?php if ($p['imagem']): ?><img class="passo-img" src="uploads/<?= e($p['imagem']) ?>" alt="Imagem do passo" loading="lazy"><?php endif; ?>
      </li>
    <?php endforeach; ?>
  </ol>
</article>
<?php require 'includes/footer.php'; ?>
