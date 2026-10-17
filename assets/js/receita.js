/* Página da receita: clicar na foto de um passo abre a foto ampliada (Esc, clique ou o X fecham). */
(() => {
  const fotos = [...document.querySelectorAll('a[data-ampliar]')];
  if (!fotos.length) return;

  // Camada da foto ampliada (criada uma vez, escondida)
  const camada = document.createElement('div');
  camada.className = 'lightbox';
  camada.hidden = true;
  camada.setAttribute('role', 'dialog');
  camada.setAttribute('aria-modal', 'true');
  camada.setAttribute('aria-label', 'Foto ampliada');
  const img = new Image();
  img.className = 'lightbox-img';
  img.alt = '';
  const fechar = document.createElement('button');
  fechar.type = 'button';
  fechar.className = 'lightbox-fechar';
  fechar.setAttribute('aria-label', 'Fechar foto');
  fechar.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>';
  camada.append(img, fechar);
  document.body.append(camada);

  let origem = null;                                           // link que abriu a foto (o foco volta para ele)
  const abrir = (link) => {
    origem = link;
    img.src = link.href;
    img.alt = link.querySelector('img')?.alt || '';
    camada.hidden = false;
    fechar.focus();
  };
  const fecharFoto = () => {
    if (camada.hidden) return;
    camada.hidden = true;
    img.removeAttribute('src');
    origem?.focus();
  };

  fotos.forEach((a) => a.addEventListener('click', (ev) => {
    if (ev.ctrlKey || ev.metaKey || ev.shiftKey || ev.button) return;   // Ctrl/Shift+clique mantém o comportamento normal do link
    ev.preventDefault();
    abrir(a);
  }));
  camada.addEventListener('click', fecharFoto);                // clicar em qualquer lugar fecha
  document.addEventListener('keydown', (ev) => {
    if (camada.hidden) return;
    if (ev.key === 'Escape') fecharFoto();
    if (ev.key === 'Tab') { ev.preventDefault(); fechar.focus(); }   // o foco fica preso no botão enquanto a foto está aberta
  });
  camada.addEventListener('wheel', (ev) => ev.preventDefault(), { passive: false });   // a página de trás não rola
})();
