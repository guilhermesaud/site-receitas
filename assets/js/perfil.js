/* Perfil: foto por AJAX (Fetch), olho da senha. */
(() => {
  const form = document.getElementById('form-perfil');
  if (!form) return;
  const csrf = form.querySelector('[name=csrf]').value;

  // ---------- Foto de perfil ----------
  const btnFoto = document.getElementById('avatar-btn');
  const btnRm   = document.getElementById('avatar-rm');
  const input   = document.getElementById('foto-input');
  const modelo  = document.getElementById('tpl-avatar');                   // avatar padrão (SVG)
  const alvos   = [btnFoto, document.getElementById('perfil-btn')].filter(Boolean);   // foto grande + miniatura do header

  // Troca a imagem na tela (url = null volta ao avatar padrão)
  const pintar = (url) => {
    alvos.forEach((a) => a.replaceChildren(url ? Object.assign(new Image(), { src: url, alt: '' }) : modelo.content.cloneNode(true)));
    btnRm.hidden = !url;
  };

  // Envia a requisição ao endpoint PHP e atualiza a tela se der certo
  const enviar = async (dados) => {
    dados.append('csrf', csrf);
    btnFoto.classList.add('carregando');
    try {
      const r = await fetch('perfil_foto.php', { method: 'POST', body: dados, credentials: 'same-origin' });
      const j = await r.json().catch(() => ({}));
      if (!j.ok) throw new Error(j.erro || 'Falha na requisição.');
      pintar(j.url);
      return true;
    } catch (e) {
      mostrarNotificacao(e.message, 'erro');
      return false;
    } finally {
      btnFoto.classList.remove('carregando');
    }
  };

  btnFoto.addEventListener('click', () => input.click());                  // clicar na foto abre o seletor

  input.addEventListener('change', async () => {                           // ao escolher, envia na hora
    const arq = input.files[0];
    if (!arq) return;
    if (arq.size > 2 * 1024 * 1024) { mostrarNotificacao('A imagem deve ter no máximo 2 MB.', 'erro'); input.value = ''; return; }
    const dados = new FormData();
    dados.append('acao', 'enviar');
    dados.append('foto', arq);
    if (await enviar(dados)) mostrarNotificacao('Foto atualizada.');
    input.value = '';
  });

  btnRm.addEventListener('click', async () => {                            // X vermelho: remove foto e arquivo
    const dados = new FormData();
    dados.append('acao', 'remover');
    if (await enviar(dados)) mostrarNotificacao('Foto removida.');
  });

  // ---------- Olho da senha: alterna password <-> text ----------
  document.querySelectorAll('.olho').forEach((btn) => {
    btn.addEventListener('click', () => {
      const campo = btn.parentElement.querySelector('input');
      const revelar = campo.type === 'password';
      campo.type = revelar ? 'text' : 'password';
      btn.setAttribute('aria-pressed', String(revelar));                   // o CSS troca olho aberto/riscado
      btn.setAttribute('aria-label', revelar ? 'Ocultar senha' : 'Mostrar senha');
    });
  });

})();
