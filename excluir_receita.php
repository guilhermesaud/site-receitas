<?php
/* Exclui a receita e a pasta uploads/{ID}/ inteira (capa + passos). */
require_once 'includes/salvar_receita.php';
exige_admin();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: index.php'); exit; }
csrf_exigir();

$id  = (int)($_POST['id'] ?? 0);
$pdo = db();
$st  = $pdo->prepare('SELECT imagem_capa FROM receitas WHERE id = ?');
$st->execute([$id]);
$capa = $st->fetchColumn();

if ($capa !== false) {
    $st = $pdo->prepare('SELECT imagem FROM passos WHERE receita_id = ?');
    $st->execute([$id]);
    $arquivos = $st->fetchAll(PDO::FETCH_COLUMN);
    $arquivos[] = $capa;

    $pdo->prepare('DELETE FROM receitas WHERE id = ?')->execute([$id]);   // passos saem junto (ON DELETE CASCADE)
    apagar_pasta(UPLOAD_DIR . $id);                                       // apaga os arquivos de dentro e depois a pasta
    array_map('apagar_imagem', $arquivos);                                // sobras de receitas antigas (arquivos soltos em uploads/)
}
if (($_POST['volta'] ?? '') === 'config') {                    // excluído pelo painel: volta para a aba Receitas
    flash('Receita excluída.');
    header('Location: configuracoes.php#receitas');
    exit;
}
header('Location: index.php');
exit;
