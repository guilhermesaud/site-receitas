<?php
/* Formulário de cadastro/edição. Espera: $d, $passos, $erros, $atual (ou null), $titulo_form. */

const SVG_X       = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" aria-hidden="true"><path d="M7 7l10 10M17 7L7 17"/></svg>';
const SVG_LIXEIRA = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6"/></svg>';
const SVG_IMG     = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><path d="M21 15l-5-5L5 21"/></svg>';

const SVG_MAIS = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>';

/** Área clicável de imagem (capa e passos): placeholder, preview e botão X. O app.js cuida dos cliques. */
function area_imagem(string $input, string $rm, ?string $img, bool $removida, string $rotulo, string $classe): string {
    if ($removida) $img = null;
    return '<div class="upload ' . $classe . '" data-upload>'
        . '<button type="button" class="upload-area" aria-label="' . e($rotulo) . ' (clique para escolher)">'
        . ($img ? '<img src="uploads/' . e($img) . '" alt="">' : '')
        . '<span class="upload-ph"' . ($img ? ' hidden' : '') . '>' . SVG_IMG . '<small>' . e($rotulo) . '</small></span></button>'
        . '<input type="file" name="' . $input . '" accept="image/jpeg,image/png,image/webp" hidden>'
        . '<input type="hidden" name="' . $rm . '" value="' . ($removida ? 1 : 0) . '">'      // 1 = remover a imagem salva
        . '<button type="button" class="avatar-rm upload-rm" title="Remover imagem" aria-label="Remover imagem"' . ($img ? '' : ' hidden') . '>' . SVG_X . '</button>'
        . '</div>';
}

/** HTML de uma linha de utensílio: quantidade + utensílio cadastrado (lista e <template> do JavaScript). */
function utensilio_html(array $u, array $lista): string {
    return '<div class="utensilio">'
        . '<label>Quantidade<input type="number" name="utensilio_qtd[]" min="1" max="999" value="' . (int)$u['quantidade'] . '"></label>'
        . '<label>Utensílio<select name="utensilio_id[]"><option value="">Selecione</option>' . opcoes_html($lista, $u['utensilio_id']) . '</select></label>'
        . '<button type="button" class="rm-ut icon-btn sec exc" title="Remover utensílio" aria-label="Remover utensílio">' . SVG_LIXEIRA . '</button>'
        . '</div>';
}

/** HTML de um passo: foto à esquerda, texto à direita (lista inicial e <template> do JavaScript). */
function passo_html(array $p): string {
    return '<div class="passo"><input type="hidden" name="passo_id[]" value="' . (int)$p['id'] . '">'
        . '<div class="passo-h"><span class="n"></span>'
        . '<input type="text" name="passo_titulo[]" maxlength="100" placeholder="Título do passo (opcional)" aria-label="Título do passo (opcional)" value="' . e($p['titulo'] ?? '') . '">'
        . '<button type="button" class="rm icon-btn sec exc" title="Remover passo" aria-label="Remover passo">' . SVG_LIXEIRA . '</button></div>'
        . '<div class="passo-corpo">'
        . area_imagem('passo_img[]', 'passo_rm[]', $p['imagem'], !empty($p['rm']), 'Foto do passo', 'upload-passo')
        . '<textarea name="passo_texto[]" rows="4" placeholder="Descreva este passo" required>' . e($p['instrucao']) . '</textarea>'
        . '</div></div>';
}

/** <option>s de uma lista [id => nome], marcando o valor atual. */
function opcoes_html(array $lista, $atual): string {
    $h = '';
    foreach ($lista as $id => $nome) $h .= '<option value="' . (int)$id . '"' . ((int)$atual === $id ? ' selected' : '') . '>' . e($nome) . '</option>';
    return $h;
}

$vazio = ['id' => 0, 'instrucao' => '', 'imagem' => null];
if (!$passos) $passos = [$vazio];
$capaRm = ($_POST['capa_rm'] ?? '0') === '1';
$listaUt = opcoes('utensilios');
$utens   = ($_SERVER['REQUEST_METHOD'] === 'POST') ? utensilios_do_post() : ($utens ?? []);   // edição: vem do banco
?>
<h1><?= e($titulo_form) ?></h1>

<?php if ($erros): ?>
  <div class="erro" role="alert"><ul><?php foreach ($erros as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul></div>
<?php endif; ?>

<form class="form form-receita" method="post" enctype="multipart/form-data">
  <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

  <!-- Topo: título (termina no fim da 1ª coluna de campos) + capa à esquerda, alinhada em altura com os 7 campos -->
  <div class="form-topo">
    <label class="ft-titulo">Título
      <input type="text" name="titulo" maxlength="150" required value="<?= e($d['titulo']) ?>">
    </label>

    <?= area_imagem('capa', 'capa_rm', $atual['imagem_capa'] ?? null, $capaRm, 'Foto de capa', 'upload-capa ft-capa') ?>

    <label class="ft-r2">Categoria
      <select name="categoria_id" required><option value="">Selecione</option><?= opcoes_html(categorias(), $d['categoria_id']) ?></select>
    </label>
    <label class="ft-r2">Ocasião
      <select name="ocasiao_id"><option value="">Selecione</option><?= opcoes_html(opcoes('ocasioes'), $d['ocasiao_id']) ?></select>
    </label>
    <label class="ft-r2">Culinária
      <select name="culinaria_id"><option value="">Selecione</option><?= opcoes_html(opcoes('culinarias'), $d['culinaria_id']) ?></select>
    </label>

    <label class="ft-r3">Dificuldade
      <select name="dificuldade" required>
        <option value="">Selecione</option>
        <?php foreach (DIFICULDADES as $n): ?><option <?= $d['dificuldade'] === $n ? 'selected' : '' ?>><?= e($n) ?></option><?php endforeach; ?>
      </select>
    </label>
    <label class="ft-r3">Tempo de preparo (minutos)
      <input type="number" name="tempo_preparo" min="1" required value="<?= e($d['tempo_preparo']) ?>">
    </label>
    <label class="ft-r3">Rendimento
      <input type="text" name="rendimento" maxlength="60" placeholder="Ex.: 4 porções" required value="<?= e($d['rendimento']) ?>">
    </label>

    <label class="ft-r4">Custo
      <select name="custo_id"><option value="">Selecione</option><?= opcoes_html(opcoes('custos'), $d['custo_id']) ?></select>
    </label>
  </div>

  <label>Link do vídeo no YouTube
    <input type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=..." value="<?= e($d['video_url']) ?>">
  </label>

  <label>Ingredientes (um por linha)
    <textarea name="ingredientes" required><?= e($d['ingredientes']) ?></textarea>
  </label>

  <!-- Utensílios (opcional): vários por receita, cada linha com quantidade + utensílio cadastrado -->
  <fieldset>
    <legend>Utensílios</legend>
    <div id="utensilios"><?php foreach ($utens as $u) echo utensilio_html($u, $listaUt); ?></div>
    <template id="tpl-utensilio"><?= utensilio_html(['utensilio_id' => 0, 'quantidade' => 1], $listaUt) ?></template>
    <?php if (!$listaUt): ?><small>Nenhum utensílio cadastrado. Cadastre em Configurações &gt; Utensílios.</small><?php endif; ?>
    <button type="button" id="add-utensilio" class="icon-btn sec" title="Adicionar utensílio" aria-label="Adicionar utensílio"><?= SVG_MAIS ?></button>
  </fieldset>

  <fieldset>
    <legend>Modo de preparo</legend>
    <div id="passos"><?php foreach ($passos as $p) echo passo_html($p); ?></div>
    <template id="tpl-passo"><?= passo_html($vazio) ?></template>
    <button type="button" id="add-passo" class="icon-btn sec" title="Adicionar passo" aria-label="Adicionar mais um passo"><?= SVG_MAIS ?></button>
  </fieldset>

  <button type="submit">Salvar receita</button>
</form>
