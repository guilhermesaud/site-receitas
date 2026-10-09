<?php
/* Cadastro de receita (somente administrador). */
require_once 'includes/salvar_receita.php';
exige_admin();

$erros = []; $atual = null; $passos = [];
$d = array_fill_keys(CAMPOS, '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_exigir();
    [$erros, $id, $d, $passos] = processar_receita(null);
    if ($id) { header('Location: receita.php?id=' . $id); exit; }
}

$titulo_pagina = $titulo_form = 'Nova receita';
require 'includes/header.php';
require 'includes/form_receita.php';
require 'includes/footer.php';
