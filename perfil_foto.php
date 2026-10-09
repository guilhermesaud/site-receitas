<?php
/* Endpoint AJAX da foto de perfil. POST: acao=enviar (campo "foto") | acao=remover. Responde JSON. */
require_once 'includes/salvar_receita.php';
header('Content-Type: application/json; charset=utf-8');

function resp(array $dados, int $codigo = 200): void {
    http_response_code($codigo);
    echo json_encode($dados);
    exit;
}

if (!eh_admin()) resp(['ok' => false, 'erro' => 'Sessão expirada. Entre novamente.'], 401);
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals(csrf_token(), (string)($_POST['csrf'] ?? '')))
    resp(['ok' => false, 'erro' => 'Requisição inválida.'], 400);

$pdo = db();
$id  = (int)$_SESSION['admin_id'];
$st  = $pdo->prepare('SELECT foto FROM usuarios WHERE id = ?');
$st->execute([$id]);
$antiga = $st->fetchColumn();                       // false = usuário não existe; null = sem foto
if ($antiga === false) resp(['ok' => false, 'erro' => 'Usuário não encontrado.'], 404);

$acao = $_POST['acao'] ?? '';

if ($acao === 'enviar') {
    [$nova, $erro] = receber_imagem($_FILES['foto'] ?? null, 'Foto de perfil', 'users');   // grava em uploads/users/
    if (!$nova) resp(['ok' => false, 'erro' => $erro ?? 'Nenhum arquivo enviado.'], 422);
    try {
        $pdo->prepare('UPDATE usuarios SET foto = ? WHERE id = ?')->execute([$nova, $id]);
    } catch (PDOException $e) {
        apagar_imagem($nova);
        resp(['ok' => false, 'erro' => 'Não foi possível salvar a foto.'], 500);
    }
    apagar_imagem($antiga);                         // remove a foto anterior do servidor
    resp(['ok' => true, 'url' => 'uploads/' . $nova]);
}

if ($acao === 'remover') {
    $pdo->prepare('UPDATE usuarios SET foto = NULL WHERE id = ?')->execute([$id]);   // apaga o caminho no banco
    apagar_imagem($antiga);                                                          // e o arquivo no servidor
    resp(['ok' => true, 'url' => null]);
}

resp(['ok' => false, 'erro' => 'Ação inválida.'], 400);
