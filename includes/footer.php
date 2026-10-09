</main>
<footer><div class="container">Meu caderno de receitas</div></footer>
<script src="assets/js/app.js" defer></script>
<?php
// Notificações pendentes (flash na sessão): mostradas uma única vez e removidas da sessão
$avisos = is_array($_SESSION['flash'] ?? null) ? $_SESSION['flash'] : [];
unset($_SESSION['flash']);
if ($avisos): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
<?php foreach ($avisos as $a): ?>
  mostrarNotificacao(<?= json_encode($a['msg'], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>, <?= json_encode($a['tipo']) ?>);
<?php endforeach; ?>
});
</script>
<?php endif; ?>
</body>
</html>
