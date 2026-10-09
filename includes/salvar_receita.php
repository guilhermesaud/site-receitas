<?php
/* Salvar receita (cadastro e edição) + utilitários de upload.
   Estrutura no disco:  uploads/{ID da receita}/arquivo.jpg   |   uploads/users/arquivo.jpg
   No banco ficam os caminhos relativos a uploads/ (ex.: "7/a1b2c3d4.jpg", "users/e5f6.png"). */
require_once __DIR__ . '/config.php';

const UPLOAD_DIR = __DIR__ . '/../uploads/';
const CAMPOS = ['titulo', 'categoria_id', 'dificuldade', 'tempo_preparo', 'rendimento', 'video_url', 'ingredientes', 'ocasiao_id', 'culinaria_id', 'custo_id'];

/** Converte "7/a.jpg" em caminho absoluto, só se o arquivo estiver DENTRO de uploads/. */
function caminho_seguro(string $rel): ?string {
    $base = realpath(UPLOAD_DIR);
    $abs  = realpath(UPLOAD_DIR . $rel);
    return ($base && $abs && strpos($abs, $base . DIRECTORY_SEPARATOR) === 0) ? $abs : null;
}

/** Apaga um arquivo de imagem (caminho relativo a uploads/). Ignora se não existir. */
function apagar_imagem(?string $rel): void {
    $abs = $rel ? caminho_seguro($rel) : null;
    if ($abs && is_file($abs)) unlink($abs);
}

/** Apaga uma pasta recursivamente: primeiro os arquivos de dentro, depois a pasta. */
function apagar_pasta(string $dir): void {
    if (!is_dir($dir) || is_link($dir)) return;
    foreach (scandir($dir) as $item) {
        if ($item === '.' || $item === '..') continue;
        $caminho = $dir . DIRECTORY_SEPARATOR . $item;
        if (is_dir($caminho) && !is_link($caminho)) apagar_pasta($caminho);
        else unlink($caminho);
    }
    rmdir($dir);
}

/** Valida um upload (tipo real e tamanho) SEM gravar. Retorna [extensão|null, erro|null]. */
function validar_imagem(?array $f, string $rotulo): array {
    if (!$f || $f['error'] === UPLOAD_ERR_NO_FILE) return [null, null];
    if ($f['error'] !== UPLOAD_ERR_OK || $f['size'] > 2 * 1024 * 1024)
        return [null, "$rotulo: imagem inválida ou maior que 2 MB."];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);     // confere o conteúdo real
    $ext  = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'][$mime] ?? null;
    return $ext ? [$ext, null] : [null, "$rotulo: use uma imagem JPG, PNG ou WEBP."];
}

/** Cria uploads/{subpasta}/ com mkdir() se preciso e move o arquivo para lá. Retorna "subpasta/nome.ext" ou null. */
function guardar_imagem(array $f, string $ext, string $subpasta): ?string {
    $dir = UPLOAD_DIR . $subpasta;
    if (!is_dir($dir) && !mkdir($dir, 0755, true) && !is_dir($dir)) return null;
    $nome = bin2hex(random_bytes(8)) . '.' . $ext;
    return move_uploaded_file($f['tmp_name'], "$dir/$nome") ? "$subpasta/$nome" : null;
}

/** Valida + grava em uma etapa (usado no perfil). Retorna [caminho|null, erro|null]. */
function receber_imagem(?array $f, string $rotulo, string $subpasta): array {
    [$ext, $erro] = validar_imagem($f, $rotulo);
    if (!$ext) return [null, $erro];
    $rel = guardar_imagem($f, $ext, $subpasta);
    return $rel ? [$rel, null] : [null, "$rotulo: falha ao salvar. Verifique a permissão da pasta uploads/."];
}

/** Pega o i-ésimo arquivo de um campo múltiplo (name="campo[]"). */
function arquivo(string $campo, int $i): ?array {
    $f = $_FILES[$campo] ?? null;
    if (!$f || !isset($f['name'][$i])) return null;
    return ['name' => $f['name'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
}

/**
 * Valida $_POST/$_FILES e grava a receita. $atual = linha do banco (edição) ou null (cadastro).
 * Retorna [erros, id|null, dados, passos] — dados e passos servem para reexibir o formulário.
 */
function processar_receita(?array $atual): array {
    $pdo = db();
    $d = [];
    foreach (CAMPOS as $c) $d[$c] = trim((string)($_POST[$c] ?? ''));

    // Passos já existentes (edição)
    $existentes = [];
    if ($atual) {
        $st = $pdo->prepare('SELECT * FROM passos WHERE receita_id = ?');
        $st->execute([$atual['id']]);
        foreach ($st as $row) $existentes[(int)$row['id']] = $row;
    }

    // Passos enviados: texto, id e arquivo compartilham o mesmo índice
    $ids = (array)($_POST['passo_id'] ?? []);
    $rms = (array)($_POST['passo_rm'] ?? []);
    $tits = (array)($_POST['passo_titulo'] ?? []);   // título opcional de cada passo      // 1 = remover a imagem salva do passo
    $passos = [];
    foreach ((array)($_POST['passo_texto'] ?? []) as $i => $txt) {
        $txt = trim((string)$txt);
        if ($txt === '') continue;
        $pid = (int)($ids[$i] ?? 0);
        if (!isset($existentes[$pid])) $pid = 0;
        $passos[] = ['id' => $pid, 'instrucao' => $txt, 'imagem' => $existentes[$pid]['imagem'] ?? null, 'i' => $i, 'rm' => (($rms[$i] ?? '0') === '1'),
                    'titulo' => mb_substr(trim(is_string($tits[$i] ?? null) ? $tits[$i] : ''), 0, 100)];
    }

    // --- Validação dos campos ---
    $erros = [];
    if ($d['titulo'] === '')                              $erros[] = 'Informe o título.';
    if (!isset(categorias()[(int)$d['categoria_id']]))     $erros[] = 'Escolha uma categoria.';
    if (!in_array($d['dificuldade'], DIFICULDADES, true)) $erros[] = 'Escolha a dificuldade.';
    if (!ctype_digit($d['tempo_preparo']) || (int)$d['tempo_preparo'] < 1)
                                                          $erros[] = 'Informe o tempo de preparo em minutos.';
    if ($d['rendimento'] === '')                          $erros[] = 'Informe o rendimento.';
    if ($d['ingredientes'] === '')                        $erros[] = 'Informe os ingredientes.';
    if ($d['video_url'] !== '' && !youtube_embed($d['video_url']))
                                                          $erros[] = 'O link do vídeo precisa ser do YouTube.';
    if (!$passos)                                         $erros[] = 'Adicione ao menos um passo no modo de preparo.';
    // Utensílios (opcional): só os cadastrados, cada um uma única vez
    $utens = utensilios_do_post();
    $validos = opcoes('utensilios');
    $vistos = [];
    foreach ($utens as $u) {
        if (!isset($validos[$u['utensilio_id']])) $erros[] = 'Utensílio inválido.';
        elseif (isset($vistos[$u['utensilio_id']])) $erros[] = 'Utensílio repetido: ' . $validos[$u['utensilio_id']] . '.';
        $vistos[$u['utensilio_id']] = true;
    }
    foreach (['ocasiao_id' => ['ocasioes', 'Ocasião'], 'culinaria_id' => ['culinarias', 'Culinária'], 'custo_id' => ['custos', 'Custo']] as $campo => [$tabela, $rotulo])
        if ($d[$campo] !== '' && !isset(opcoes($tabela)[(int)$d[$campo]])) $erros[] = "$rotulo: escolha uma opção válida.";
    if ($erros) return [$erros, null, $d, $passos];

    // --- Validação dos uploads (nada é gravado ainda) ---
    [$extCapa, $e] = validar_imagem($_FILES['capa'] ?? null, 'Foto de capa');
    if ($e) $erros[] = $e;
    foreach ($passos as $k => &$p) {
        [$p['ext'], $e] = validar_imagem(arquivo('passo_img', $p['i']), 'Passo ' . ($k + 1));
        if ($e) $erros[] = $e;
    }
    unset($p);
    if ($erros) return [$erros, null, $d, $passos];

    // --- Gravação: INSERT/UPDATE -> mkdir -> mover imagens -> UPDATE com os caminhos finais ---
    $id = $atual ? (int)$atual['id'] : 0;
    $novas = []; $antigos = [];
    $falha = 'Falha ao salvar a imagem. Verifique a permissão da pasta uploads/.';
    try {
        $pdo->beginTransaction();
        $campos = [$d['titulo'], (int)$d['categoria_id'], $d['dificuldade'], (int)$d['tempo_preparo'], $d['rendimento'], $d['video_url'] ?: null, $d['ingredientes'],
                   $d['ocasiao_id'] === '' ? null : (int)$d['ocasiao_id'], $d['culinaria_id'] === '' ? null : (int)$d['culinaria_id'], $d['custo_id'] === '' ? null : (int)$d['custo_id']];
        if ($atual) {
            $pdo->prepare('UPDATE receitas SET titulo=?, categoria_id=?, dificuldade=?, tempo_preparo=?, rendimento=?, video_url=?, ingredientes=?, ocasiao_id=?, culinaria_id=?, custo_id=? WHERE id=?')
                ->execute([...$campos, $id]);
        } else {
            // 1) INSERT primeiro: o ID gerado dá nome à pasta das imagens
            $pdo->prepare('INSERT INTO receitas (titulo, categoria_id, dificuldade, tempo_preparo, rendimento, video_url, ingredientes, ocasiao_id, culinaria_id, custo_id) VALUES (?,?,?,?,?,?,?,?,?,?)')
                ->execute($campos);
            $id = (int)$pdo->lastInsertId();
        }
        $pasta = (string)$id;                                    // uploads/{ID}/ (criada por guardar_imagem via mkdir)

        // 2) Capa -> uploads/{ID}/ e UPDATE com o caminho final
        if ($extCapa) {
            $capa = guardar_imagem($_FILES['capa'], $extCapa, $pasta);
            if (!$capa) throw new DomainException($falha);
            $novas[] = $capa;
            if ($atual && $atual['imagem_capa']) $antigos[] = $atual['imagem_capa'];
            $pdo->prepare('UPDATE receitas SET imagem_capa = ? WHERE id = ?')->execute([$capa, $id]);
        } elseif ($atual && $atual['imagem_capa'] && ($_POST['capa_rm'] ?? '0') === '1') {   // X da capa
            $antigos[] = $atual['imagem_capa'];
            $pdo->prepare('UPDATE receitas SET imagem_capa = NULL WHERE id = ?')->execute([$id]);
        }

        // 3) Passos -> imagens também em uploads/{ID}/
        $manter = [];
        foreach ($passos as $ordem => $p) {
            $img = $p['imagem'];
            if ($p['ext']) {
                $img = guardar_imagem(arquivo('passo_img', $p['i']), $p['ext'], $pasta);
                if (!$img) throw new DomainException($falha);
                $novas[] = $img;
                if ($p['imagem']) $antigos[] = $p['imagem'];
            } elseif ($p['rm'] && $p['imagem']) {                  // X da imagem do passo
                $antigos[] = $p['imagem'];
                $img = null;
            }
            if ($p['id']) {
                $pdo->prepare('UPDATE passos SET ordem=?, titulo=?, instrucao=?, imagem=? WHERE id=? AND receita_id=?')
                    ->execute([$ordem + 1, $p['titulo'] ?: null, $p['instrucao'], $img, $p['id'], $id]);
                $manter[] = $p['id'];
            } else {
                $pdo->prepare('INSERT INTO passos (receita_id, ordem, titulo, instrucao, imagem) VALUES (?,?,?,?,?)')
                    ->execute([$id, $ordem + 1, $p['titulo'] ?: null, $p['instrucao'], $img]);
            }
        }
        foreach (array_diff(array_keys($existentes), $manter) as $pid) {      // passos apagados no formulário
            $pdo->prepare('DELETE FROM passos WHERE id = ?')->execute([$pid]);
            if ($existentes[$pid]['imagem']) $antigos[] = $existentes[$pid]['imagem'];
        }
        // Utensílios: apaga os vínculos antigos e grava os atuais (na ordem do formulário)
        $pdo->prepare('DELETE FROM receita_utensilios WHERE receita_id = ?')->execute([$id]);
        foreach ($utens as $u) {
            $pdo->prepare('INSERT INTO receita_utensilios (receita_id, utensilio_id, quantidade) VALUES (?,?,?)')
                ->execute([$id, $u['utensilio_id'], $u['quantidade']]);
        }
        $pdo->commit();
    } catch (Throwable $ex) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        array_map('apagar_imagem', $novas);
        if (!$atual && $id) apagar_pasta(UPLOAD_DIR . $id);      // cadastro que falhou: não deixa a pasta órfã
        $msg = $ex instanceof DomainException ? $ex->getMessage() : 'Erro ao salvar no banco de dados.';
        return [[$msg], null, $d, $passos];
    }

    array_map('apagar_imagem', $antigos);                        // só após o commit: apaga imagens substituídas/removidas
    return [[], $id, $d, $passos];
}

/** Utensílios enviados no formulário: [['utensilio_id' => int, 'quantidade' => int], ...]. Linhas sem utensílio escolhido são ignoradas. */
function utensilios_do_post(): array {
    $ids  = (array)($_POST['utensilio_id'] ?? []);
    $qtds = (array)($_POST['utensilio_qtd'] ?? []);
    $lista = [];
    foreach ($ids as $i => $uid) {
        if (!is_string($uid) || $uid === '') continue;
        $q = is_string($qtds[$i] ?? null) ? (int)$qtds[$i] : 1;
        $lista[] = ['utensilio_id' => (int)$uid, 'quantidade' => max(1, min(999, $q))];
    }
    return $lista;
}
