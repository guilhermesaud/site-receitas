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
})();
