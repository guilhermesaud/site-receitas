<?php
/* Configurações (painel com abas): Receitas, listas de opções, Usuários e Cor — somente administrador. */
require_once 'includes/config.php';
exige_admin();

$pdo  = db();
$erro = '';
$aba  = 'receitas';                          // aba aberta se a página recarregar com erro

// Listas de opções: todas usam o MESMO CRUD. Tabela e coluna vêm daqui, nunca do usuário.
$listas = [
    'categorias' => ['tabela' => 'categorias', 'coluna' => 'categoria_id', 'titulo' => 'Categorias', 'rotulo' => 'Categoria', 'fem' => true, 'cab' => 'Nome da Categoria'],
    'ocasioes'   => ['tabela' => 'ocasioes',   'coluna' => 'ocasiao_id',   'titulo' => 'Ocasiões',   'rotulo' => 'Ocasião',   'fem' => true, 'cab' => 'Nome da Ocasião'],
    'culinarias' => ['tabela' => 'culinarias', 'coluna' => 'culinaria_id', 'titulo' => 'Culinárias', 'rotulo' => 'Culinária', 'fem' => true, 'cab' => 'Nome da Culinária'],
    'custo'      => ['tabela' => 'custos',     'coluna' => 'custo_id',     'titulo' => 'Custo',      'rotulo' => 'Custo',     'fem' => false, 'cab' => 'Custo'],
    'utensilios' => ['tabela' => 'utensilios', 'de' => 'receita_utensilios', 'coluna' => 'utensilio_id', 'titulo' => 'Utensílios', 'rotulo' => 'Utensílio', 'fem' => false, 'cab' => 'Nome do Utensílio'],   // vínculo pela tabela receita_utensilios
];

// Layouts disponíveis para os ingredientes na página da receita: chave => [nome, descrição, miniatura]
$layouts = [
    'subtitulo' => ['Subtítulo com linha', 'Cada grupo ganha um título e uma linha fina; as listas ficam uma abaixo da outra.',
                    '<i class="tt"></i><i class="ln"></i><i class="b"></i><i class="c"></i><i class="tt"></i><i class="ln"></i><i class="b"></i>'],
    'cartoes'   => ['Cartões em colunas', 'Cada grupo é um cartão; os cartões ocupam duas colunas dentro de Ingredientes.',
                    '<span class="cols"><span class="cartao"><i class="tt"></i><i class="b"></i><i class="c"></i><i class="b"></i></span><span class="cartao"><i class="tt"></i><i class="c"></i><i class="b"></i></span></span>'],
    'etiqueta'  => ['Etiqueta (estilo das tags)', 'O título vira uma etiqueta, igual às tags da receita.',
                    '<i class="pl"></i><i class="b"></i><i class="c"></i><i class="b"></i><i class="pl"></i><i class="c"></i><i class="b"></i>'],
];

foreach ($listas as &$cfg) $cfg += ['de' => 'receitas'];     // tabela que referencia a opção (padrão: receitas)
unset($cfg);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_exigir();
    $acao  = $_POST['acao'] ?? '';
    $lista = $_POST['lista'] ?? '';
    $id    = (int)($_POST['id'] ?? 0);
    $nome  = trim($_POST['nome'] ?? '');
    $ok    = '';
    $aba   = in_array($acao, ['cor', 'layout'], true) ? 'layout' : (isset($listas[$lista]) ? $lista : 'receitas');   // cor e layout ficam na mesma aba
    try {
        if ($acao === 'cor') {
            $cor = (string)($_POST['cor_destaque'] ?? '');
            if (!isset(CORES[$cor])) {
                $erro = 'Escolha uma cor válida.';
            } else {
                $pdo->prepare('UPDATE usuarios SET cor_destaque = ? WHERE id = ?')->execute([$cor, $_SESSION['admin_id']]);
                $ok = 'Cor atualizada.';
            }
        } elseif ($acao === 'layout') {
            $lay = (string)($_POST['layout_ingredientes'] ?? '');
            if (!isset($layouts[$lay])) {
                $erro = 'Escolha um layout válido.';
            } else {
                $pdo->prepare("INSERT INTO ajustes (chave, valor) VALUES ('layout_ingredientes', ?) ON DUPLICATE KEY UPDATE valor = VALUES(valor)")->execute([$lay]);
                $ok = 'Layout atualizado.';
            }
        } elseif (!isset($listas[$lista])) {
            $erro = 'Ação inválida.';
        } else {
            ['tabela' => $t, 'de' => $de, 'coluna' => $col, 'rotulo' => $rot, 'fem' => $fem] = $listas[$lista];
            if ($acao === 'excluir') {
                $uso = $pdo->prepare("SELECT COUNT(*) FROM $de WHERE $col = ?");
                $uso->execute([$id]);
                if ($uso->fetchColumn() > 0) {
                    $erro = 'Esse item está em uso por receitas. Altere as receitas antes de excluí-lo.';
                } else {
                    $pdo->prepare("DELETE FROM $t WHERE id = ?")->execute([$id]);
                    $ok = $rot . ($fem ? ' excluída.' : ' excluído.');
                }
            } elseif ($nome === '' || mb_strlen($nome) > 40) {
                $erro = 'Informe um nome de até 40 caracteres.';
            } elseif ($acao === 'criar') {
                $pdo->prepare("INSERT INTO $t (nome) VALUES (?)")->execute([$nome]);
                $ok = $rot . ($fem ? ' cadastrada.' : ' cadastrado.');
            } elseif ($acao === 'editar') {
                $pdo->prepare("UPDATE $t SET nome = ? WHERE id = ?")->execute([$nome, $id]);
                $ok = $rot . ($fem ? ' atualizada.' : ' atualizado.');
            }
        }
    } catch (PDOException $ex) {
        $erro = $acao === 'excluir' ? 'Não foi possível excluir (item em uso?).'
              : ($ex->getCode() === '23000' ? 'Já existe um item com esse nome.' : 'Não foi possível salvar.');
    }
    if ($ok) { flash($ok); header('Location: configuracoes.php#' . $aba); exit; }   // toast na próxima página (evita reenvio ao atualizar)
    if ($erro) flash($erro, 'erro');                                              // erro: toast nesta mesma página
}

// --- Dados de cada aba ---
$receitas = $pdo->query(
    'SELECT r.id, r.titulo, r.imagem_capa, c.nome AS categoria, r.dificuldade, r.tempo_preparo, r.rendimento
       FROM receitas r JOIN categorias c ON c.id = r.categoria_id
   ORDER BY r.id ASC'
)->fetchAll();

$dados = [];                                  // COUNT de receitas vinculadas a cada opção
foreach ($listas as $k => $cfg) {
    $dados[$k] = $pdo->query(
        "SELECT t.id, t.nome, COUNT(r.id) AS total
           FROM {$cfg['tabela']} t LEFT JOIN {$cfg['de']} r ON r.{$cfg['coluna']} = t.id
       GROUP BY t.id, t.nome ORDER BY t.id"
    )->fetchAll();
}

$usuarios = $pdo->query('SELECT id, usuario, foto FROM usuarios ORDER BY id')->fetchAll();

$st = $pdo->prepare('SELECT cor_destaque FROM usuarios WHERE id = ?');
$st->execute([$_SESSION['admin_id']]);
$corAtual = $st->fetchColumn() ?: 'neutro';

$layoutAtual = 'etiqueta';                                   // padrão, se a tabela ajustes ainda não existir
try {
    $v = $pdo->query("SELECT valor FROM ajustes WHERE chave = 'layout_ingredientes'")->fetchColumn();
    if (is_string($v) && isset($layouts[$v])) $layoutAtual = $v;
} catch (PDOException $ex) { /* tabela ainda não criada */ }

$svg = fn(string $d) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $d . '</svg>';
$svgLapis     = $svg('<path d="M17 3a2.83 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>');
$svgLixeira   = $svg('<path d="M3 6h18M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/>');
$svgDisquete  = $svg('<path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><path d="M17 21v-8H7v8M7 3v5h8"/>');
$svgMais      = $svg('<path d="M12 5v14M5 12h14"/>');

$titulo_pagina = 'Configurações';
require 'includes/header.php';
?>
<h1>Configurações</h1>

<div class="painel" data-inicial="<?= e($aba) ?>">
  <!-- Menu lateral: o configuracoes.js alterna as abas sem recarregar -->
  <nav class="lateral" aria-label="Seções das configurações">
    <a href="#receitas" data-aba="receitas">Receitas</a>
    <?php foreach ($listas as $k => $cfg): ?><a href="#<?= e($k) ?>" data-aba="<?= e($k) ?>"><?= e($cfg['titulo']) ?></a><?php endforeach; ?>
    <a href="#usuarios" data-aba="usuarios">Usuários</a>
    <a href="#layout" data-aba="layout">Layout</a>
  </nav>

  <div class="conteudo">
    <!-- ===== Receitas ===== -->
    <section id="receitas" class="aba">
      <div class="crud-header">
        <h2>Receitas</h2>
        <input type="search" id="busca-receitas" placeholder="Filtrar receitas" aria-label="Filtrar receitas na tabela">
      </div>
      <?php if (!$receitas): ?>
        <p class="vazio">Nenhuma receita cadastrada.</p>
      <?php else: ?>
        <div class="tabela-wrap">
          <table class="tabela" id="tabela-receitas">
            <thead><tr><th>ID</th><th>Capa</th><th>Nome</th><th>Categoria</th><th>Dificuldade</th><th>Tempo de preparo</th><th>Rendimento</th><th><span class="sr">Ações</span></th></tr></thead>
            <tbody>
              <?php foreach ($receitas as $r): ?>
                <tr>
                  <td class="num"><?= (int)$r['id'] ?></td>
                  <td><?php if ($r['imagem_capa']): ?><img class="mini-thumb img-padrao" src="uploads/<?= e($r['imagem_capa']) ?>" alt="" loading="lazy"><?php else: ?><span class="mini-thumb img-padrao sem"></span><?php endif; ?></td>
                  <td class="nome"><a href="receita.php?id=<?= (int)$r['id'] ?>"><?= e($r['titulo']) ?></a></td>
                  <td><?= e($r['categoria']) ?></td>
                  <td><?= e($r['dificuldade']) ?></td>
                  <td><?= e(fmt_tempo((int)$r['tempo_preparo'])) ?></td>
                  <td class="nome"><?= e($r['rendimento']) ?></td>
                  <td>
                    <div class="acoes-linha">
                      <a class="icon-btn sec" href="editar_receita.php?id=<?= (int)$r['id'] ?>" title="Editar" aria-label="Editar <?= e($r['titulo']) ?>"><?= $svgLapis ?></a>
                      <form method="post" action="excluir_receita.php" onsubmit="return confirm('Excluir esta receita e todas as imagens dela? Não dá para desfazer.')">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <input type="hidden" name="volta" value="config">
                        <button type="submit" class="icon-btn sec exc" title="Excluir" aria-label="Excluir <?= e($r['titulo']) ?>"><?= $svgLixeira ?></button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <p id="sem-resultado" class="vazio" hidden>Nenhuma receita encontrada.</p>
      <?php endif; ?>
    </section>

    <!-- ===== Listas de opções: Categorias, Ocasiões, Culinárias e Custo (mesmo modelo) ===== -->
    <?php foreach ($listas as $k => $cfg): ?>
      <section id="<?= e($k) ?>" class="aba" hidden>
        <div class="crud-header">
          <h2><?= e($cfg['titulo']) ?></h2>
          <form class="cat-nova" method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="lista" value="<?= e($k) ?>">
            <input type="hidden" name="acao" value="criar">
            <input type="text" name="nome" maxlength="40" required aria-label="Novo item de <?= e($cfg['titulo']) ?>">
            <button type="submit" class="icon-btn sec" title="Adicionar" aria-label="Adicionar em <?= e($cfg['titulo']) ?>"><?= $svgMais ?></button>
          </form>
        </div>
        <div class="tabela-wrap">
          <table class="tabela">
            <thead><tr><th>ID</th><th><?= e($cfg['cab']) ?></th><th>Receitas Vinculadas</th><th aria-label="Ações"></th></tr></thead>
            <tbody>
              <?php foreach ($dados[$k] as $c): $fid = 'f-edit-' . $k . '-' . (int)$c['id']; ?>
                <tr>
                  <td class="num"><?= (int)$c['id'] ?></td>
                  <!-- O campo do nome fica nesta coluna, mas pertence ao formulário de salvar (atributo form) da coluna Ações -->
                  <td class="cat-nome"><input type="text" name="nome" form="<?= e($fid) ?>" maxlength="40" required value="<?= e($c['nome']) ?>" aria-label="Nome"></td>
                  <td><?= (int)$c['total'] ?></td>
                  <td>
                    <div class="acoes-linha">
                      <form id="<?= e($fid) ?>" method="post">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="lista" value="<?= e($k) ?>">
                        <input type="hidden" name="acao" value="editar">
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <button type="submit" class="icon-btn sec" title="Salvar nome" aria-label="Salvar nome"><?= $svgDisquete ?></button>
                      </form>
                      <form method="post" onsubmit="return confirm('Excluir &quot;<?= e($c['nome']) ?>&quot;?')">
                        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
                        <input type="hidden" name="lista" value="<?= e($k) ?>">
                        <input type="hidden" name="acao" value="excluir">
                        <input type="hidden" name="id" value="<?= (int)$c['id'] ?>">
                        <button type="submit" class="icon-btn sec exc"<?= $c['total'] ? ' disabled' : '' ?> title="<?= $c['total'] ? 'Em uso por receitas' : 'Excluir' ?>" aria-label="Excluir"><?= $svgLixeira ?></button>
                      </form>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
              <?php if (!$dados[$k]): ?><tr><td colspan="4" class="vazio">Nada cadastrado ainda.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </section>
    <?php endforeach; ?>

    <!-- ===== Usuários (somente visualização) ===== -->
    <section id="usuarios" class="aba" hidden>
      <div class="crud-header"><h2>Usuários</h2></div>
      <div class="tabela-wrap">
        <table class="tabela">
          <thead><tr><th>ID</th><th>Foto de perfil</th><th>Login</th></tr></thead>
          <tbody>
            <?php foreach ($usuarios as $u): ?>
              <tr>
                <td class="num"><?= (int)$u['id'] ?></td>
                <td><?php if ($u['foto']): ?><img class="mini-thumb img-padrao" src="uploads/<?= e($u['foto']) ?>" alt=""><?php else: ?><span class="mini-thumb img-padrao sem"><?= avatar_svg() ?></span><?php endif; ?></td>
                <td><?= e($u['usuario']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- ===== Layout (pai): aparência do site =====
         Níveis:  Layout (pai)  >  Cores e Página da receita (filhos)  >  Ingredientes (neto).
         Novo ajuste: copie um bloco .cfg-sec (filho) ou .cfg-sub (neto) e adapte o formulário. -->
    <section id="layout" class="aba" hidden>
      <div class="crud-header"><h2>Layout</h2></div>

      <!-- Filho: Cores (vale só para o administrador logado) -->
      <section class="cfg-sec" aria-labelledby="lay-cores">
        <h3 id="lay-cores" class="cfg-t2">Cores</h3>
        <form class="form-cor" method="post">
          <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
          <input type="hidden" name="acao" value="cor">
          <div class="cores-lista">
            <?php foreach (CORES as $chave => $nome): ?>
              <label class="cor">
                <input type="radio" name="cor_destaque" value="<?= e($chave) ?>" <?= $corAtual === $chave ? 'checked' : '' ?>>
                <span class="amostra <?= e($chave) ?>"></span><?= e($nome) ?>
              </label>
            <?php endforeach; ?>
          </div>
          <button type="submit">Salvar cor</button>
        </form>
      </section>

      <!-- Filho: Página da receita (vale para todos os visitantes) -->
      <section class="cfg-sec" aria-labelledby="lay-receita">
        <h3 id="lay-receita" class="cfg-t2">Página da receita</h3>

        <!-- Neto: Ingredientes -->
        <section class="cfg-sub" aria-labelledby="lay-ingredientes">
          <h4 id="lay-ingredientes" class="cfg-t3">Ingredientes</h4>
          <form class="form-cor" method="post">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <input type="hidden" name="acao" value="layout">
            <fieldset>
              <legend class="sr">Layout dos ingredientes</legend>
              <div class="layouts-lista">
                <?php foreach ($layouts as $chave => [$nome, $desc, $mini]): ?>
                  <label class="layout-op">
                    <input type="radio" name="layout_ingredientes" value="<?= e($chave) ?>" <?= $layoutAtual === $chave ? 'checked' : '' ?>>
                    <span class="layout-prev" aria-hidden="true"><?= $mini ?></span>
                    <strong><?= e($nome) ?></strong>
                    <small><?= e($desc) ?></small>
                  </label>
                <?php endforeach; ?>
              </div>
            </fieldset>
            <button type="submit">Salvar layout</button>
          </form>
        </section>
        <!-- novos netos da Página da receita entram aqui -->
      </section>
      <!-- novos filhos do Layout entram aqui -->
    </section>
  </div>
</div>
<script src="assets/js/configuracoes.js?v=<?= (int)@filemtime(__DIR__ . '/assets/js/configuracoes.js') ?>" defer></script>
<?php require 'includes/footer.php'; ?>
