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
  // ---------- Ícones das seções: painel flutuante da lista e prévia ao vivo (só vale ao salvar) ----------
  const linhasIco = [...painel.querySelectorAll('.ico-linha')];
  const fecharLinha = (lin) => {
    lin.querySelector('.ico-grade').hidden = true;
    lin.querySelector('.ico-abrir').setAttribute('aria-expanded', 'false');
  };
  document.addEventListener('click', (ev) => {                       // clicar fora do painel fecha
    if (!ev.target.closest('.ico-escolha')) linhasIco.forEach(fecharLinha);
  });
  document.addEventListener('keydown', (ev) => {                     // Esc fecha e devolve o foco ao botão
    if (ev.key !== 'Escape') return;
    const aberta = linhasIco.find((l) => !l.querySelector('.ico-grade').hidden);
    if (aberta) { fecharLinha(aberta); aberta.querySelector('.ico-abrir').focus(); }
  });

  linhasIco.forEach((lin) => {
    const prev  = lin.querySelector('.ico-prev');
    const alvo  = lin.querySelector('.ico-prev-i');
    const nome  = lin.querySelector('.ico-nome');
    const grade = lin.querySelector('.ico-grade');
    const abrir = lin.querySelector('.ico-abrir');
    const arq   = lin.querySelector('input[type=file]');
    const cores = lin.querySelector('input[type=checkbox]');
    const rotCores = cores.closest('label');
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
      const marcado = lin.querySelector('input[type=radio]:checked');
      const novo = arq.files[0];                                     // PNG novo escolhido vale mais que a lista
      const ehPngAtual = !!marcado && marcado.value === 'up';
      rotCores.hidden = !(novo || ehPngAtual);                       // "Cores originais" só faz sentido com PNG
      alvo.replaceChildren();
      let texto = 'Sem ícone';
      if (novo) {
        alvo.append(criarPng(urlNovo));
        texto = 'PNG novo (ainda não salvo)';
      } else if (ehPngAtual) {
        alvo.append(criarPng(marcado.dataset.png));
        texto = cores.checked ? 'Seu PNG (cores originais)' : 'Seu PNG';
      } else if (marcado && marcado.value !== 'none') {              // ícone da biblioteca: copia o desenho do botão marcado
        const svg = marcado.closest('label').querySelector('svg').cloneNode(true);
        svg.setAttribute('class', 'sec-ico');
        svg.removeAttribute('aria-label');
        svg.setAttribute('aria-hidden', 'true');
        alvo.append(svg);
        texto = marcado.closest('label').title;
      }
      nome.textContent = texto;
      prev.title = texto;
    };

    abrir.addEventListener('click', () => {                          // abre/fecha o painel (um de cada vez)
      const abrindo = grade.hidden;
      linhasIco.forEach(fecharLinha);
      if (!abrindo) return;
      grade.hidden = false;
      abrir.setAttribute('aria-expanded', 'true');
      grade.style.left = '';                                         // se o painel passar da borda direita da tela, desloca para a esquerda
      const r = grade.getBoundingClientRect();
      const limite = document.documentElement.clientWidth - 8;
      if (r.right > limite) grade.style.left = -Math.min(r.right - limite, Math.max(0, r.left - 8)) + 'px';
    });
    grade.addEventListener('change', () => {                         // escolheu na lista: vale o ícone da lista, o painel fecha
      arq.value = '';
      atualizar();
      fecharLinha(lin);
    });
    grade.addEventListener('click', (ev) => {                        // clicar no ícone que já estava marcado também fecha
      if (!ev.target.closest('.ico-op')) return;
      setTimeout(() => fecharLinha(lin), 0);
    });
    arq.addEventListener('change', () => {
      if (urlNovo) { URL.revokeObjectURL(urlNovo); urlNovo = null; }
      if (arq.files[0]) urlNovo = URL.createObjectURL(arq.files[0]);
      atualizar();
    });
    cores.addEventListener('change', atualizar);
  });

  // ---------- Tamanho dos ícones: as prévias acompanham o número digitado (só vale ao salvar) ----------
  const campoTam = painel.querySelector('input[name=icone_tamanho]');
  campoTam?.addEventListener('input', () => {
    const v = parseInt(campoTam.value, 10);
    if (v >= +campoTam.min && v <= +campoTam.max) campoTam.closest('form').style.setProperty('--ico-tam', v + 'px');
  });
})();
