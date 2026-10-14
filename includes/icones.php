<?php
/* Ícones das seções da página da receita (Ingredientes, Utensílios, Modo de preparo).
   - Biblioteca: ícones SVG de traço (currentColor) definidos em ICONES, escolhidos em Configurações > Layout.
   - PNG próprio: enviado pelo administrador para uploads/layout/ (PNG com fundo transparente; vira silhueta na cor do tema).
   Os valores ficam na tabela ajustes (chaves icone_ingredientes, icone_utensilios, icone_preparo):
     ''                    sem ícone
     'lib:chef-hat'        ícone da biblioteca
     'up:layout/xxxx.png'  PNG recolorido pela cor do tema (máscara)
     'img:layout/xxxx.png' PNG com as cores originais */
require_once __DIR__ . '/config.php';

/** Seções que aceitam ícone: chave => título. */
const SECOES_ICONE = ['ingredientes' => 'Ingredientes', 'utensilios' => 'Utensílios', 'preparo' => 'Modo de preparo'];

/** Biblioteca de ícones: nome => [rótulo, conteúdo do <svg> (viewBox 24x24, traço 2)]. */
const ICONES = [
    'chef-hat'       => ['Chapéu de chef',   '<path d="M17 21a1 1 0 0 0 1-1v-5.35c0-.457.316-.844.727-1.041a4 4 0 0 0-2.134-7.589 5 5 0 0 0-9.186 0 4 4 0 0 0-2.134 7.588c.411.198.727.585.727 1.041V20a1 1 0 0 0 1 1Z"/><path d="M6 17h12"/>'],
    'utensils'       => ['Garfo e faca',     '<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>'],
    'cooking-pot'    => ['Panela',           '<path d="M4 11h16v7a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3Z"/><path d="M2 11h20"/><path d="M4 14H2M22 14h-2"/><path d="M9 7c0-1.5 1.5-1.5 1.5-3M14 7c0-1.5 1.5-1.5 1.5-3"/>'],
    'soup'           => ['Tigela com sopa',  '<path d="M12 21a9 9 0 0 0 9-9H3a9 9 0 0 0 9 9Z"/><path d="M7 21h10"/><path d="M9 8c0-1.5 1.5-1.5 1.5-3M14 8c0-1.5 1.5-1.5 1.5-3"/>'],
    'flame'          => ['Chama',            '<path d="M8.5 14.5A2.5 2.5 0 0 0 11 12c0-1.38-.5-2-1-3-1.072-2.143-.224-4.054 2-6 .5 2.5 2 4.9 4 6.5 2 1.6 3 3.5 3 5.5a7 7 0 1 1-14 0c0-1.153.433-2.294 1-3a2.5 2.5 0 0 0 2.5 2.5z"/>'],
    'timer'          => ['Cronômetro',       '<path d="M10 2h4"/><path d="M12 14l3-3"/><circle cx="12" cy="14" r="8"/>'],
    'clock'          => ['Relógio',          '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>'],
    'thermometer'    => ['Termômetro',       '<path d="M14 4v10.54a4 4 0 1 1-4 0V4a2 2 0 0 1 4 0Z"/>'],
    'egg'            => ['Ovo',              '<path d="M12 2C8 2 4 8 4 14a8 8 0 0 0 16 0c0-6-4-12-8-12Z"/>'],
    'leaf'           => ['Folha',            '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.48 19 2c1 2 2 4.18 2 8 0 5.5-4.78 10-10 10Z"/><path d="M2 21c0-3 1.85-5.36 5.08-6C9.5 14.52 12 13 13 12"/>'],
    'basket'         => ['Cesta',            '<path d="m15 11-1 9"/><path d="m19 11-4-7"/><path d="M2 11h20"/><path d="m3.5 11 1.6 7.4a2 2 0 0 0 2 1.6h9.8c.9 0 1.8-.7 2-1.6l1.7-7.4"/><path d="m4.5 11 4-7"/><path d="m9 11 1 9"/>'],
    'list'           => ['Lista',            '<path d="M8 6h13M8 12h13M8 18h13M3 6h.01M3 12h.01M3 18h.01"/>'],
    'clipboard-list' => ['Prancheta',        '<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4M12 16h4M8 11h.01M8 16h.01"/>'],
    'book-open'      => ['Livro aberto',     '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>'],
    'star'           => ['Estrela',          '<path d="m12 2 3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>'],
    'heart'          => ['Coração',          '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/>'],
];

/** Valores gravados dos ícones: [seção => valor]. Sem a tabela ajustes (ou sem valor), a seção fica sem ícone. */
function icones_secoes(): array {
    $r = array_fill_keys(array_keys(SECOES_ICONE), '');
    try {
        $chaves = array_map(fn($s) => 'icone_' . $s, array_keys(SECOES_ICONE));
        $st = db()->prepare('SELECT chave, valor FROM ajustes WHERE chave IN (' . implode(',', array_fill(0, count($chaves), '?')) . ')');
        $st->execute($chaves);
        foreach ($st->fetchAll(PDO::FETCH_KEY_PAIR) as $chave => $valor) {
            $sec = substr($chave, 6);                               // tira o prefixo "icone_"
            if (isset($r[$sec])) $r[$sec] = (string)$valor;
        }
    } catch (PDOException $ex) { /* tabela ainda não criada: sem ícones */ }
    return $r;
}

/** Caminho de PNG válido gravado em uploads/layout/ (ou null). */
function icone_arquivo(string $valor): ?string {
    return preg_match('~^(?:up|img):(layout/[a-f0-9]{16}\.png)$~', $valor, $m) ? $m[1] : null;
}

/** HTML do ícone de uma seção (string vazia se não houver). */
function icone_html(string $valor): string {
    if (strncmp($valor, 'lib:', 4) === 0) {
        $nome = substr($valor, 4);
        if (!isset(ICONES[$nome])) return '';
        return '<svg class="sec-ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ICONES[$nome][1] . '</svg>';
    }
    $arq = icone_arquivo($valor);
    if (!$arq) return '';
    $url = 'uploads/' . $arq;
    if (strncmp($valor, 'img:', 4) === 0) return '<img class="sec-ico" src="' . e($url) . '" alt="">';
    return '<span class="sec-ico sec-ico-m" style="-webkit-mask-image:url(\'' . e($url) . '\');mask-image:url(\'' . e($url) . '\')" aria-hidden="true"></span>';   // a imagem vai inline: um url() relativo dentro de variável CSS seria resolvido a partir do style.css
}

/** Confere se um PNG tem transparência e tamanho razoável. Retorna mensagem de erro ou null. */
function png_icone_erro(string $caminho, int $bytes): ?string {
    if ($bytes > 200 * 1024) return 'o arquivo deve ter no máximo 200 KB.';
    $d = @file_get_contents($caminho, false, null, 0, 4096);
    if ($d === false || strlen($d) < 33) return 'não foi possível ler o PNG.';
    $largura = unpack('N', substr($d, 16, 4))[1];
    $altura  = unpack('N', substr($d, 20, 4))[1];
    if ($largura > 1024 || $altura > 1024) return 'use um PNG de até 1024 × 1024 pixels.';
    $tipo = ord($d[25]);                                            // 4 e 6 = com canal alfa; 3 (paleta) pode ter tRNS
    if ($tipo !== 4 && $tipo !== 6 && strpos($d, 'tRNS') === false) return 'o PNG precisa ter fundo transparente.';
    return null;
}
