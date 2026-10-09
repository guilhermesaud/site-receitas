/* 1) Tema claro/escuro (localStorage)  2) Dropdown do perfil  3) Passos dinâmicos */
/* Notificação flutuante (toast): mostrarNotificacao('Mensagem', 'sucesso' | 'erro' | 'info') */
window.mostrarNotificacao = (mensagem, tipo = 'sucesso') => {
  let area = document.getElementById('toasts');
  if (!area) {
    area = document.createElement('div');
    area.id = 'toasts';
    area.className = 'toasts';
    document.body.append(area);
  }
  const toast = document.createElement('div');
  toast.className = 'toast toast-' + tipo;
  toast.setAttribute('role', tipo === 'erro' ? 'alert' : 'status');
  toast.textContent = mensagem;                              // textContent: nunca interpreta HTML
  area.append(toast);
  setTimeout(() => {                                         // some sozinho após 3,5 s (erros: 4 s)
    toast.classList.add('saindo');                           // animação de saída (desvanecer)
    setTimeout(() => toast.remove(), 350);
  }, tipo === 'erro' ? 4000 : 3500);
};

(() => {
  // ---- Tema: o ícone (lua/sol) segue a classe .dark-mode do <html> ----
  const raiz = document.documentElement;
  const btn  = document.getElementById('tema-toggle');
  const marcar = () => {
    if (!btn) return;
    const texto = raiz.classList.contains('dark-mode') ? 'Mudar para o tema claro' : 'Mudar para o tema escuro';
    btn.setAttribute('aria-label', texto);
    btn.title = texto;
  };
  marcar();
  btn?.addEventListener('click', () => {
    const escuro = raiz.classList.toggle('dark-mode');          // troca o ícone via CSS
    try { localStorage.setItem('tema', escuro ? 'dark' : 'light'); } catch (e) {}
    marcar();
  });

  // ---- Dropdown do perfil: abre/fecha no clique, fora dele ou com Esc ----
  const pbtn = document.getElementById('perfil-btn');
  const menu = document.getElementById('perfil-dd');
  if (pbtn && menu) {
    const definir = (abrir) => {
      menu.classList.toggle('aberto', abrir);
      pbtn.setAttribute('aria-expanded', String(abrir));
    };
    pbtn.addEventListener('click', () => definir(!menu.classList.contains('aberto')));
    document.addEventListener('click', (ev) => {
      if (!menu.contains(ev.target) && !pbtn.contains(ev.target)) definir(false);
    });
    document.addEventListener('keydown', (ev) => {
      if (ev.key === 'Escape' && menu.classList.contains('aberto')) { definir(false); pbtn.focus(); }
    });
  }

  // ---- Áreas de imagem (capa e passos): clique abre o seletor, preview imediato, X remove ----
  const preview = (box, url) => {
    const area = box.querySelector('.upload-area');
    area.querySelector('img')?.remove();
    if (url) { const img = new Image(); img.alt = ''; img.src = url; area.prepend(img); }
    box.querySelector('.upload-ph').hidden = !!url;
    box.querySelector('.upload-rm').hidden = !url;
  };
  document.addEventListener('click', (ev) => {
    const box = ev.target.closest('[data-upload]');
    if (!box) return;
    if (ev.target.closest('.upload-rm')) {                 // X: limpa a escolha e marca a imagem salva para remoção
      box.querySelector('input[type=file]').value = '';
      box.querySelector('input[type=hidden]').value = '1';
      preview(box, null);
    } else if (ev.target.closest('.upload-area')) {
      box.querySelector('input[type=file]').click();
    }
  });
  document.addEventListener('change', (ev) => {
    const box = ev.target.closest('[data-upload]');
    const arq = box && ev.target.type === 'file' ? ev.target.files[0] : null;
    if (!arq) return;
    if (arq.size > 2 * 1024 * 1024) { alert('A imagem deve ter no máximo 2 MB.'); ev.target.value = ''; return; }
    box.querySelector('input[type=hidden]').value = '0';
    preview(box, URL.createObjectURL(arq));
  });

  // ---- Utensílios: linhas (quantidade + utensílio) adicionadas/removidas dinamicamente ----
  const listaUt  = document.getElementById('utensilios');
  const modeloUt = document.getElementById('tpl-utensilio');
  if (listaUt && modeloUt) {
    document.getElementById('add-utensilio').addEventListener('click', () => {
      listaUt.append(modeloUt.content.cloneNode(true));
      listaUt.lastElementChild.querySelector('select').focus();
    });
    listaUt.addEventListener('click', (ev) => ev.target.closest('.rm-ut')?.closest('.utensilio').remove());
  }

  // ---- Passos do modo de preparo ----
  const lista  = document.getElementById('passos');
  const modelo = document.getElementById('tpl-passo');
  if (!lista || !modelo) return;

  document.getElementById('add-passo').addEventListener('click', () => {
    lista.append(modelo.content.cloneNode(true));
    lista.lastElementChild.querySelector('textarea').focus();
  });
  lista.addEventListener('click', (ev) => {
    const remover = ev.target.closest('.rm');
    if (remover && lista.children.length > 1) remover.closest('.passo').remove();
  });
})();
