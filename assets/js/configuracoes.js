/* Configurações: abas sem recarregar, busca em tempo real na tabela de receitas e prévia da cor. */
(() => {
  const painel = document.querySelector('.painel');
  if (!painel) return;

  // ---------- Abas ----------
  const links = [...painel.querySelectorAll('.lateral a')];
  const abas  = [...painel.querySelectorAll('.aba')];
  const mostrar = (id) => {
    if (!abas.some((a) => a.id === id)) id = abas[0].id;           // id inválido -> primeira aba
    abas.forEach((a) => { a.hidden = a.id !== id; });
    links.forEach((l) => l.setAttribute('aria-current', String(l.dataset.aba === id)));
    return id;
  };
  mostrar(location.hash.slice(1) || painel.dataset.inicial);
  links.forEach((l) => l.addEventListener('click', (ev) => {
    ev.preventDefault();
    history.replaceState(null, '', '#' + mostrar(l.dataset.aba));  // guarda a aba na URL, sem recarregar
  }));
  window.addEventListener('hashchange', () => mostrar(location.hash.slice(1)));

  // ---------- Busca em tempo real (tabela de receitas) ----------
  const campo  = document.getElementById('busca-receitas');
  const linhas = [...document.querySelectorAll('#tabela-receitas tbody tr')];
  const nada   = document.getElementById('sem-resultado');
  const norm   = (t) => t.toLowerCase().normalize('NFD').replace(/\p{M}/gu, '');   // ignora maiúsculas e acentos
  campo?.addEventListener('input', () => {
    const q = norm(campo.value.trim());
    let achou = 0;
    linhas.forEach((tr) => {
      const ok = norm(tr.textContent).includes(q);
      tr.hidden = !ok;
      if (ok) achou++;
    });
    if (nada) nada.hidden = achou > 0;
  });

  // ---------- Prévia da cor de destaque (só é gravada ao salvar) ----------
  const raiz = document.documentElement;
  painel.querySelectorAll('input[name=cor_destaque]').forEach((r) => {
    r.addEventListener('change', () => {
      [...raiz.classList].filter((c) => c.startsWith('acc-')).forEach((c) => raiz.classList.remove(c));
      if (r.value !== 'neutro') raiz.classList.add('acc-' + r.value);
    });
  });
  // ---------- Ícones das seções: lista recolhível e prévia ao vivo (só vale ao salvar) ----------
  painel.querySelectorAll('.ico-sec').forEach((sec) => {
    const alvo  = sec.querySelector('.ico-prev-i');
    const nome  = sec.querySelector('.ico-nome');
    const grade = sec.querySelector('.ico-grade');
    const abrir = sec.querySelector('.ico-abrir');
    const arq   = sec.querySelector('input[type=file]');
    const cores = sec.querySelector('input[type=checkbox]');
    let urlNovo = null;                                              // endereço temporário do PNG escolhido no computador

    // PNG: recolorido pelo tema (máscara) ou com as cores originais, conforme a caixa marcada
    const criarPng = (url) => {
      if (cores.checked) { const i = new Image(); i.className = 'sec-ico'; i.alt = ''; i.src = url; return i; }
      const s = document.createElement('span');
      s.className = 'sec-ico sec-ico-m';
      s.setAttribute('aria-hidden', 'true');
      s.style.webkitMaskImage = s.style.maskImage = 'url("' + url + '")';
      return s;
    };
    const atualizar = () => {
      const marcado = sec.querySelector('input[type=radio]:checked');
      alvo.replaceChildren();
      if (arq.files[0]) { alvo.append(criarPng(urlNovo)); nome.textContent = 'PNG novo (ainda não salvo)'; return; }
      if (!marcado || marcado.value === 'none') { nome.textContent = 'Sem ícone'; return; }
      if (marcado.value === 'up') { alvo.append(criarPng(marcado.dataset.png)); nome.textContent = cores.checked ? 'Seu PNG (cores originais)' : 'Seu PNG'; return; }
      const svg = marcado.closest('label').querySelector('svg').cloneNode(true);   // ícone da biblioteca: copia o desenho do botão marcado
      svg.setAttribute('class', 'sec-ico');
      svg.removeAttribute('aria-label');
      svg.setAttribute('aria-hidden', 'true');
      alvo.append(svg);
      nome.textContent = marcado.closest('label').title;
    };

    abrir.addEventListener('click', () => {                          // abre/fecha a lista de ícones
      grade.hidden = !grade.hidden;
      abrir.setAttribute('aria-expanded', String(!grade.hidden));
    });
    grade.addEventListener('change', () => {                         // escolheu na lista: vale o ícone da lista, a lista recolhe
      arq.value = '';
      atualizar();
      grade.hidden = true;
      abrir.setAttribute('aria-expanded', 'false');
    });
    grade.addEventListener('click', (ev) => {                        // clicar no ícone que já estava marcado também recolhe a lista
      if (!ev.target.closest('.ico-op')) return;
      setTimeout(() => { grade.hidden = true; abrir.setAttribute('aria-expanded', 'false'); }, 0);
    });
    arq.addEventListener('change', () => {
      if (urlNovo) { URL.revokeObjectURL(urlNovo); urlNovo = null; }
      if (arq.files[0]) urlNovo = URL.createObjectURL(arq.files[0]);
      atualizar();
    });
    cores.addEventListener('change', atualizar);
  });
})();
