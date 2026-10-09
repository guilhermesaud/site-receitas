<?php
/* Edição de receita (editar_receita.php?id=N) — somente administrador. */
require_once 'includes/salvar_receita.php';
exige_admin();

$st = db()->prepare('SELECT * FROM receitas WHERE id = ?');
$st->execute([(int)($_GET['id'] ?? 0)]);
$atual = $st->fetch();

if (!$atual) {
    http_response_code(404);
    $titulo_pagina = 'Receita não encontrada';
    require 'includes/header.php';
    echo '<p class="vazio">Receita não encontrada. <a href="index.php">Voltar ao início</a>.</p>';
    require 'includes/footer.php';
    exit;
}

$erros = [];
$d = array_intersect_key($atual, array_flip(CAMPOS));        // dados já preenchidos
$st = db()->prepare('SELECT id, titulo, instrucao, imagem FROM passos WHERE receita_id = ? ORDER BY ordem');
$st->execute([$atual['id']]);
$passos = $st->fetchAll();
$st = db()->prepare('SELECT utensilio_id, quantidade FROM receita_utensilios WHERE receita_id = ? ORDER BY id');
$st->execute([$atual['id']]);
$utens = $st->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_exigir();
    [$erros, $id, $d, $passos] = processar_receita($atual);
    if ($id) { header('Location: receita.php?id=' . $id); exit; }
}

$titulo_pagina = $titulo_form = 'Editar receita';
require 'includes/header.php';
require 'includes/form_receita.php';
require 'includes/footer.php';
