# CONTEXTO — Site de Receitas Pessoal

Documento de contexto do projeto: ponto de partida para cada conversa com o assistente. Não contém senhas nem dados pessoais.
Idioma do projeto: **português do Brasil** (interface, comentários, nomes de variáveis, colunas e funções).

---

## 0. Estado do repositório e decisões atuais (atualizado em 09/10/2026)

**Repositório GitHub: `guilhermesaud/site-receitas`** (https://github.com/guilhermesaud/site-receitas). O dono informou que o tornou **público** para que o assistente consiga ler os arquivos atuais; antes era privado.

**Decisões vigentes (não repropor sem o dono pedir):**
- **Tudo local por enquanto.** O site roda só no WampServer; a publicação foi adiada. O GitHub serve para versionamento e para o assistente ler o código.
- **Versionamento:** a tag `v1.0` (09/10/2026) marca o ponto de partida; novas etapas ganham tags `v1.1`, `v1.2`…
- **Estrutura de pastas atual está aprovada:** as páginas `.php` ficam na raiz, incluindo `excluir_receita.php`, `logout.php` e `perfil_foto.php`. A ideia de uma pasta `acoes/` foi **descartada**.
- Os scripts SQL ficam em `database/` (`database/database.sql`, `database/receitas_db.sql` e `database/atualizar/*.sql`). `includes/`, `assets/` e `uploads/` seguem como antes.

**Como o assistente deve ler o projeto:** abrir o repositório acima e ler os arquivos que a tarefa envolve **antes de editar** (a cópia anexada ao Projeto pode estar desatualizada). Se o repositório não abrir, pedir ao dono os arquivos envolvidos. O assistente não executa o código: o dono testa no WampServer.

### Pontos de atenção
1. **`database/receitas_db.sql` contém dados reais:** além da estrutura, o export traz a linha do usuário admin (login, hash de senha e caminho da foto). Como o repositório agora é público, **considerar o hash exposto**: trocar a senha do admin pela página Perfil (e em qualquer outro lugar onde ela seja usada), remover o arquivo do Git e adicioná-lo ao `.gitignore` (ou trocá-lo por um `schema.sql` só com a estrutura). Para sumir do histórico é preciso reescrevê-lo (`git filter-repo` ou BFG) ou recriar o repositório. Não reproduzir o conteúdo do hash em conversas.
2. **`database/.htaccess` está vazio:** não protege nada. Se a pasta for publicada no diretório público do servidor, os `.sql` podem ser baixados. Sugestão (Apache 2.4): `Require all denied`; ou não enviar a pasta `database/` ao servidor.
3. **`.gitignore` não ignora o conteúdo de `uploads/`:** as fotos enviadas vão para o Git (e, com o repositório público, ficam visíveis). Sugestão: `uploads/*`, `!uploads/.htaccess`, `!uploads/.gitkeep`. Também ignorar arquivos auxiliares locais (ex.: `lista_arquivos.bat`, `lista_arquivos.txt`).
4. **Helpers só no arquivo ignorado:** o `config.php` real fica fora do Git, então `includes/config.exemplo.php` é a única cópia versionada de `db()`, `e()`, `flash()`, `opcoes()`, helpers de CSRF etc. **Todo helper novo ou alterado precisa ser replicado nos dois arquivos** (`config.php` e `config.exemplo.php`). Ideia: deixar só as credenciais num arquivo ignorado e versionar o `config.php` com os helpers.
5. **`README.md` quase vazio:** falta o passo a passo de instalação (ordem dos SQL, criar o admin, copiar `config.exemplo.php` para `config.php`, permissão de escrita em `uploads/`).
6. **Fonte:** `assets/fonts/` contém hoje `HvDTrial_Brandon_Text_Regular.otf`, uma versão **trial** (o `header.php` usa o primeiro arquivo da pasta). Trial costuma não permitir uso em site publicado e pode ter caracteres faltando (acentos); conferir a licença antes de publicar.

---

## 1. Visão geral

Site de receitas **pessoal**, com **um único administrador** (o dono). Não existe página de cadastro de usuário.

- **Público (sem login):** vê a página inicial (grade de receitas), pesquisa por nome ou ingrediente e abre a página de cada receita.
- **Administrador (com login):** cadastra, edita e exclui receitas; gerencia listas de opções (categorias, ocasiões, culinárias, custo, utensílios); edita o próprio perfil (foto, login, senha); escolhe uma cor de destaque para o site.
- Tema claro/escuro para todos (preferência salva no navegador de cada visitante).

---

## 2. Tecnologias

| Camada | O que usamos |
|---|---|
| Back-end | PHP puro, sem framework (exige **PHP 7.4+**: `??=`, spread em array, arrow functions; extensões `pdo_mysql`, `fileinfo`, `mbstring`) |
| Banco | MySQL/MariaDB, InnoDB, `utf8mb4`, acesso via **PDO** com *prepared statements* |
| Front-end | HTML5 + CSS3 (uma única folha, variáveis CSS) + JavaScript puro (sem bibliotecas) |
| Ajax | Fetch API (somente foto de perfil) |
| Fonte | Carregada automaticamente de `assets/fonts/` (primeiro arquivo da pasta). O projeto foi pensado para a "Muro" (DaFont); hoje há uma fonte trial na pasta (ver seção 0, ponto 6) |
| Ícones | SVG inline (traço `currentColor`, estilo feather) |
| Ambiente de desenvolvimento | WampServer no Windows (PHP 8.3, MySQL 9.1) + phpMyAdmin; a pasta local é um clone do repositório |

---

## 3. Estrutura de pastas e função de cada arquivo

```
site-receitas/
├── .gitignore                 Ignora includes/config.php, .env, *.log, Thumbs.db
├── README.md                  Apresentação (ainda mínima)
├── CONTEXTO.md                Este documento (ponto de partida para cada conversa)
│
├── index.php                  Home: grade de receitas + busca (?q=) por título/ingrediente
├── receita.php                Página da receita (?id=): capa 80% + infos 20%, vídeo, ingredientes | utensílios, passos
├── nova_receita.php           Cadastro (admin)
├── editar_receita.php         Edição (admin), usa o mesmo formulário do cadastro
├── excluir_receita.php        Exclusão (POST, admin): apaga no banco e a pasta uploads/{ID}/ inteira
├── login.php / logout.php     Sessão do admin (logout só via POST)
├── perfil.php                 Perfil do admin: login, nova senha (com olho), foto (clique no avatar)
├── perfil_foto.php            Endpoint AJAX (JSON) da foto de perfil: acao=enviar | remover
├── configuracoes.php          Painel com menu lateral e abas (ver seção 5)
│
├── includes/
│   ├── config.exemplo.php     MODELO do config.php (credenciais vazias + todos os helpers); o config.php real não vai ao Git
│   ├── header.php             <head>, tema salvo, fonte automática, menu, busca, ícone de tema, avatar/dropdown ou "Entrar"
│   ├── footer.php             Rodapé, carrega app.js e imprime os toasts pendentes (flash da sessão)
│   ├── form_receita.php       Formulário compartilhado (cadastro/edição): capa, 7 campos, utensílios e passos dinâmicos
│   └── salvar_receita.php     Validação + gravação da receita; utilitários de upload e de apagar arquivos/pastas
│
├── assets/
│   ├── css/style.css          Toda a folha de estilo
│   ├── js/app.js              Toast global, tema, dropdown do perfil, áreas de imagem, utensílios e passos dinâmicos
│   ├── js/perfil.js           Foto por Fetch, olho da senha
│   ├── js/configuracoes.js    Abas sem recarregar, busca em tempo real na tabela de receitas, prévia da cor
│   └── fonts/                 Colocar UM arquivo de fonte (.woff2/.woff/.ttf/.otf); o header.php pega o primeiro
│
├── database/
│   ├── .htaccess              (vazio hoje; ver ponto de atenção 2)
│   ├── database.sql           Schema inicial (banco receitas_db + tabela receitas na forma ORIGINAL)
│   ├── receitas_db.sql        Export do phpMyAdmin do banco local (estrutura final + dados do admin; ver ponto de atenção 1)
│   └── atualizar/
│       ├── atualizar_banco.sql        usuarios, imagem_capa, tabela passos; migra o texto antigo do preparo
│       ├── atualizar_perfil.sql       usuarios.foto
│       ├── atualizar_cor.sql          usuarios.cor_destaque
│       ├── atualizar_categorias.sql   tabela categorias + receitas.categoria_id (FK)
│       ├── atualizar_campos.sql       ocasioes, culinarias, custos + FKs opcionais em receitas
│       ├── atualizar_passos.sql       passos.titulo (opcional)
│       ├── atualizar_utensilios.sql   utensilios + receita_utensilios
│       └── atualizar_datas.sql        receitas.atualizado_em (data da última atualização)
│
└── uploads/                   .htaccess (bloqueia execução de scripts) e .gitkeep; {ID da receita}/ e users/ criadas pelo PHP
```

**Helpers globais (`includes/config.php`):** `db()`, `e()` (escape HTML), `fmt_tempo()`, `linhas()`, `youtube_embed()`,
`eh_admin()`, `exige_admin()`, `csrf_token()`, `csrf_exigir()`, `flash()`, `avatar_svg()`, `categorias()`, `opcoes($tabela)`;
constantes `DIFICULDADES` e `CORES`.

**Funções de `includes/salvar_receita.php`:** `processar_receita($atual)`, `utensilios_do_post()`, `validar_imagem()`,
`guardar_imagem()` (faz `mkdir` e move), `receber_imagem()`, `apagar_imagem()`, `apagar_pasta()` (recursiva), `caminho_seguro()`, `arquivo()`.

---

## 4. Banco de dados (`receitas_db`)

Todas as tabelas InnoDB, `utf8mb4`. Ordem de execução em uma instalação nova:
`database/database.sql` → `database/atualizar/atualizar_banco.sql` → `…/atualizar_perfil.sql` → `…/atualizar_cor.sql` → `…/atualizar_categorias.sql` → `…/atualizar_campos.sql` → `…/atualizar_passos.sql` → `…/atualizar_utensilios.sql` → `…/atualizar_datas.sql`.
Atalho: `database/receitas_db.sql` recria a estrutura final de uma vez, mas traz dados reais (ver seção 0) — use apenas uma cópia só com a estrutura.
Os scripts `atualizar_*.sql` são "rode UMA vez" (não são idempotentes por causa dos `ALTER`).
O usuário admin é criado **manualmente** com `INSERT` (hash gerado por `password_hash`, nunca texto puro).

### `usuarios`
| Coluna | Tipo | Observação |
|---|---|---|
| id | INT UNSIGNED PK AI | |
| usuario | VARCHAR(50) UNIQUE NOT NULL | login |
| senha_hash | VARCHAR(255) NOT NULL | bcrypt |
| foto | VARCHAR(255) NULL | caminho relativo a `uploads/` (ex.: `users/<aleatório>.jpg`) |
| cor_destaque | VARCHAR(10) NOT NULL DEFAULT 'neutro' | neutro, vermelho, verde, azul, laranja, rosa, roxo, amarelo, cyan |
| criado_em | TIMESTAMP | |

### `receitas`
| Coluna | Tipo | Observação |
|---|---|---|
| id | INT UNSIGNED PK AI | |
| titulo | VARCHAR(150) NOT NULL | índice `idx_titulo` |
| categoria_id | INT UNSIGNED NOT NULL | FK → categorias(id) RESTRICT |
| ocasiao_id | INT UNSIGNED NULL | FK → ocasioes(id) RESTRICT |
| culinaria_id | INT UNSIGNED NULL | FK → culinarias(id) RESTRICT |
| custo_id | INT UNSIGNED NULL | FK → custos(id) RESTRICT |
| dificuldade | ENUM('Fácil','Médio','Difícil') NOT NULL DEFAULT 'Fácil' | |
| tempo_preparo | SMALLINT UNSIGNED NOT NULL | minutos |
| rendimento | VARCHAR(60) NOT NULL | texto livre |
| imagem_capa | VARCHAR(255) NULL | caminho relativo a `uploads/` (ex.: `7/<aleatório>.jpg`) |
| video_url | VARCHAR(255) NULL | só links do YouTube |
| ingredientes | TEXT NOT NULL | um por linha |
| criado_em | TIMESTAMP | preenchido sozinho ao cadastrar (`DEFAULT CURRENT_TIMESTAMP`) |
| atualizado_em | TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP | gravado com `NOW()` a cada salvamento do formulário (em `salvar_receita.php`) |

### `passos` (modo de preparo)
| Coluna | Tipo | Observação |
|---|---|---|
| id | INT UNSIGNED PK AI | |
| receita_id | INT UNSIGNED NOT NULL | FK → receitas(id) **ON DELETE CASCADE** |
| ordem | SMALLINT UNSIGNED NOT NULL | índice `idx_receita_ordem (receita_id, ordem)` |
| titulo | VARCHAR(100) NULL | opcional |
| instrucao | TEXT NOT NULL | |
| imagem | VARCHAR(255) NULL | caminho relativo a `uploads/` |

### Listas de opções (todas com a mesma forma)
`categorias`, `ocasioes`, `culinarias`, `custos`, `utensilios` → `id INT UNSIGNED PK AI`, `nome VARCHAR(40) NOT NULL UNIQUE`.

### `receita_utensilios` (vínculo N:N com quantidade)
| Coluna | Tipo | Observação |
|---|---|---|
| id | INT UNSIGNED PK AI | |
| receita_id | INT UNSIGNED NOT NULL | FK → receitas(id) **CASCADE** |
| utensilio_id | INT UNSIGNED NOT NULL | FK → utensilios(id) **RESTRICT** |
| quantidade | SMALLINT UNSIGNED NOT NULL DEFAULT 1 | 1 a 999 |
| | | UNIQUE (receita_id, utensilio_id): cada utensílio uma vez por receita |

---

## 5. Funcionalidades prontas

**Público**
- Home com grade de cartões (capa, título, categoria, dificuldade, tempo) e busca por título/ingrediente.
- Página da receita: título, etiquetas (categoria, ocasião, culinária), capa em proporção fixa (2:1, `object-fit: cover`) ocupando ~80% com as informações rápidas empilhadas (~20%: dificuldade, tempo, rendimento, custo), vídeo do YouTube incorporado, ingredientes com utensílios à direita (a seção some se não houver utensílios) e passos numerados (título opcional + imagem opcional). Na linha das tags, à direita, uma etiqueta no mesmo estilo com ícone de relógio mostra a data da última atualização.
- Tema claro/escuro (ícone lua/sol, `localStorage`, sem "flash" ao carregar), rodapé fixo no fim da tela, responsivo.

**Administrador**
- Login/logout por sessão; rotas administrativas protegidas por `exige_admin()`.
- Cadastro/edição de receita: título, capa (área clicável com preview e X para remover), categoria, ocasião, culinária, dificuldade, tempo, rendimento, custo, vídeo, ingredientes, **utensílios** (linhas quantidade + utensílio, dinâmicas) e **passos** dinâmicos (título opcional, foto clicável, texto, lixeira). Na edição, a linha do título mostra à direita, em fonte pequena e só com ícones, a data de criação (calendário) e a de atualização (relógio); no cadastro esse bloco não aparece.
- Exclusão de receita com limpeza recursiva de `uploads/{ID}/`.
- Perfil: foto por AJAX (clicar no avatar; X vermelho remove), login, nova senha (com confirmação e olho mostrar/ocultar).
- Configurações (`configuracoes.php`): menu lateral com abas trocadas por JS, sem recarregar — **Receitas** (tabela com capa, busca em tempo real, rolagem interna de ~10 linhas, ícones de editar/excluir), **Categorias**, **Ocasiões**, **Culinárias**, **Custo**, **Utensílios** (mesmo CRUD: ID, nome editável, receitas vinculadas, salvar/excluir; exclusão bloqueada se em uso), **Usuários** (somente leitura), **Cor**.
- Cor de destaque (9 opções) aplicada só para o admin logado; visitantes sempre veem o neutro.
- Notificações flutuantes (toast) para todos os avisos de sucesso/erro das telas de Configurações e Perfil.

---

## 6. Funcionalidades pendentes e ideias

Não foram pedidas ainda; foram sugestões feitas durante a conversa ou lacunas conhecidas:

- Converter para toast também os erros em lista dos formulários de receita e a mensagem de erro do login; mostrar "Receita salva." após cadastrar/editar.
- Script para migrar imagens antigas (soltas em `uploads/`) para `uploads/{ID}/`.
- Botões de editar/excluir nos cartões da Home (hoje só na página da receita e na aba Receitas).
- Paginação e filtro por categoria/ocasião/culinária na Home; busca também por categoria.
- Reordenar passos (arrastar) e remover só a imagem de um passo já salva sem apagar o passo (hoje: X marca a remoção).
- Segurança extra: *rate limit* de login, flag `Secure` no cookie de sessão sob HTTPS, voltar a exigir a senha atual no perfil.
- Aplicar a largura total (`.form-receita`) também à página de Perfil, se desejado (hoje ela mantém `max-width: 720px`).
- **Do repositório (seção 0):** tirar/ignorar `database/receitas_db.sql`, proteger `database/` com `.htaccess`, ignorar o conteúdo de `uploads/` no `.gitignore`, escrever o README de instalação e manter `config.exemplo.php` sincronizado.
- Consolidar o `style.css`, que acumulou regras sobrepostas (ver seção 9).

---

## 7. Decisões tomadas e o motivo

- **PHP puro + PDO, sem framework:** pedido original de usar a linguagem mais simples; poucos arquivos, fácil de hospedar.
- **Segurança por padrão:** *prepared statements*; `e()` em toda saída; token CSRF em todo POST; `session_regenerate_id` no login; `password_hash`; uploads validados pelo conteúdo real (`finfo`), até 2 MB, JPG/PNG/WEBP, nome aleatório; `.htaccess` em `uploads/` bloqueia scripts. Nomes de tabela/coluna em SQL dinâmico vêm sempre de *whitelist* no código.
- **Uploads por pasta de ID:** organização e exclusão recursiva sem lixo. Fluxo no cadastro: validar → `INSERT` (gera o ID) → `mkdir` → mover imagens → gravar caminhos, tudo em **transação**, com limpeza de arquivos se algo falhar. O banco guarda caminhos **relativos a `uploads/`**, o que mantém arquivos antigos (soltos) funcionando.
- **Chaves estrangeiras:** `RESTRICT` nas listas (não deixa excluir opção em uso); `CASCADE` em passos e vínculos de utensílios.
- **Ocasião, Culinária, Custo e Utensílios opcionais:** não quebram receitas antigas.
- **Listas de opções geradas pelo mesmo código** (`$listas` em `configuracoes.php`): garante "mesmo modelo" entre abas; a chave `de` indica a tabela que referencia a opção (padrão `receitas`; utensílios usam `receita_utensilios`).
- **Cor de destaque no banco** (`usuarios.cor_destaque`), aplicada como classe `acc-*` no `<html>` só com admin logado; paleta base neutra e a cor aparece apenas em detalhes (botões, hover, bordas ativas, ícones).
- **Tema:** classe `.dark-mode` no `<html>`; script no `<head>` aplica o tema salvo antes de pintar (evita flash); preferência do sistema como padrão.
- **Fonte automática:** CSS não lê diretórios, então o `header.php` usa `glob()` em `assets/fonts/` e injeta o `@font-face` com o primeiro arquivo encontrado.
- **Largura padronizada:** um único `.container` (`max-width: 1040px`); `body{overflow-y:scroll}` evita deslocamento por barra de rolagem; `fieldset{min-width:0}`.
- **Avisos como toast:** `flash()` guarda na sessão (formulários com redirect) e o rodapé dispara `mostrarNotificacao()` uma vez; no Fetch a função é chamada direto.
- **Avatar quadrado arredondado** com a classe utilitária `.img-padrao` (variável `--r-img`), igual às miniaturas de receita.
- **Ações só com ícone** (lápis, lixeira, disquete, "+") nas tabelas, para um visual limpo.
- **Imagens do formulário:** preview local imediato e envio ao salvar; a foto de perfil é a exceção (envio imediato via Fetch).
- **Remover imagem salva:** campos ocultos `capa_rm` e `passo_rm[]` sinalizam a remoção; o arquivo só é apagado depois do `commit`.
- **Escala base de 14px** (`html{font-size:87.5%}`) para o site parecer menos "grande".
- **"Custo" no singular** em toda a interface; a tabela interna segue `custos`.
- **Senha atual removida do perfil** a pedido do dono (troca de login/senha sem confirmar a senha antiga; é um *trade-off* de segurança consciente).
- **Datas da receita:** `criado_em` é automático; `atualizado_em` é gravado explicitamente com `NOW()` no `UPDATE` de `processar_receita()` (o `ON UPDATE CURRENT_TIMESTAMP` do MySQL não marcaria edições só de passos, utensílios ou imagens, pois a linha de `receitas` não mudaria). Datas formatadas no SQL com `DATE_FORMAT(..., '%d/%m/%Y %H:%i')`, sem helper novo no `config.php`.
- **Formulário de receita: Ingredientes e Utensílios lado a lado**, em blocos de mesma largura e altura fixa (`.bloco-form`, 20rem), com rolagem interna na caixa de texto e na grade de utensílios (cabeçalho fixo: Quantidade, Utensílio e lixeira; botão "+" no topo do bloco). A linha nova nasce com quantidade 1 e "Selecione". Abaixo de 700px os blocos empilham.

---

## 8. Padrões de código que seguimos

- **Idioma:** português em tudo (comentários, nomes, mensagens); `snake_case` em variáveis PHP e colunas; cada arquivo começa com um comentário dizendo o que faz.
- **Ordem nas páginas PHP:** processar POST/redirecionar **antes** de `require 'includes/header.php'` (cabeçalhos HTTP antes de qualquer saída); padrão PRG (POST → redirect → GET); `footer.php` por último.
- **Todo POST:** `csrf_exigir()`; páginas/endpoints do admin: `exige_admin()` (o endpoint AJAX responde JSON 401 em vez de redirecionar).
- **Saída sempre com `e()`**; SQL sempre preparado (única exceção: tabela/coluna vindas de *whitelist*).
- **Campos repetidos** usam arrays paralelos alinhados por índice: `passo_texto[]`, `passo_id[]`, `passo_titulo[]`, `passo_img[]`, `passo_rm[]`, `utensilio_qtd[]`, `utensilio_id[]`.
- **Mensagens:** `flash($mensagem, 'sucesso'|'erro')`; nunca texto estático de aviso na página.
- **CSS:** uma folha; cores em variáveis no `:root`, `.dark-mode` só redefine variáveis, `.acc-*` redefine a cor de destaque; classes utilitárias (`.container`, `.img-padrao`, `.icon-btn`, `.sec`, `.exc`, `.crud-header`, `.upload`, `.tabela`); ajustes novos costumam ser **anexados ao fim** do arquivo.
- **JS:** puro, em IIFE, com delegação de eventos (elementos criados dinamicamente funcionam sem religar); funções globais só quando necessário (`mostrarNotificacao`); `localStorage` apenas para o tema; `app.js` global e um arquivo por página quando específico.
- **Ícones:** SVG inline com `stroke="currentColor"` e `aria-hidden="true"`; botões só de ícone sempre com `aria-label` e `title`.
- **Banco:** uma mudança = um arquivo `atualizar_*.sql` comentado, com aviso "execute UMA vez".

---

## 9. Problemas conhecidos e bugs já corrigidos

**Corrigidos**
- *Fatal error: função `utensilios_do_post()` indefinida* ao salvar receita (a função era chamada mas faltava no arquivo) → definida em `includes/salvar_receita.php`.
- Desalinhamento de largura entre páginas (barra de rolagem aparecendo/sumindo, caixa da receita com largura própria, `fieldset` alargando o formulário) → `.container` único, `overflow-y:scroll`, `fieldset{min-width:0}`.
- Formulário de receita estreito (herdava `max-width: 720px`) → classe `.form-receita` sem limite.
- Botão "+" de categorias com ícone invisível ao passar o mouse → passou a usar o estilo com contorno (`.sec`).
- Mensagem de sucesso duplicada/estática no perfil e nas configurações → substituída por toasts.
- Texto "Nome da Custo" e "Custos" → ajustado para "Custo".
- Em **Configurações**, a aba Utensílios aparecia embaixo de todas as outras abas → o CSS `#utensilios{display:grid}` (lista do formulário de receita) também atingia a `<section id="utensilios">` da aba e anulava o atributo `hidden`. Corrigido renomeando a lista do formulário para `#lista-utensilios` (CSS, `form_receita.php` e `app.js`). **Regra:** não aplicar `display` por id em elementos cujo id coincide com a chave de uma aba (`receitas`, `categorias`, `ocasioes`, `culinarias`, `custo`, `utensilios`, `usuarios`, `cor`).

**Conhecidos / atenção**
- **Nada foi executado pelo assistente** que gerou o código (sem PHP no ambiente de geração); só houve conferências estáticas. Teste manual é indispensável após cada mudança.
- Se a validação do formulário falhar, os arquivos de imagem escolhidos precisam ser selecionados de novo (limitação dos navegadores).
- Se o upload exceder `post_max_size`/`upload_max_filesize` do `php.ini`, o formulário chega vazio e aparece "Requisição inválida" (falha do token CSRF).
- Imagens antigas soltas em `uploads/` continuam funcionando, mas só vão para `uploads/{ID}/` quando forem trocadas.
- `style.css` tem regras sobrepostas (ajustes anexados ao longo do tempo) e usa recursos modernos (`:has()`, `display: contents`, `aspect-ratio`); exige navegador atualizado.
- Os scripts `atualizar_*.sql` não são repetíveis; rodar de novo mostra avisos de "tabela já existe" (inofensivos) ou erros de `ALTER`.
- Troca de login/senha no perfil não pede a senha atual (decisão do dono).

---

## 10. Como trabalhamos (para o próximo assistente)

- **Leia este arquivo no início de cada conversa e atualize-o sempre que uma decisão mudar.**
- O dono escreve em português, em pedidos numerados e costuma anexar **prints** com setas/caixas vermelhas para indicar alinhamentos.
- Cada entrega vem como os arquivos alterados, completos (o dono copia por cima dos locais); quando há mudança de banco, avisar **antes** de copiar os arquivos qual `.sql` rodar (e que rode só uma vez). Se a mudança tocar um helper, lembrar de atualizar também o `includes/config.exemplo.php`.
- Antes de mexer num arquivo, ler a versão atual no repositório (ele foi alterado por vários ajustes em sequência) e reaproveitar os helpers existentes em vez de duplicar código.
- Explicar de forma curta o que mudou, o que ficou fora do pedido e o que precisa ser testado.

---

## 11. Publicação (adiada)

Por enquanto o site roda só localmente. Quando for publicar:

- **Hospedagem:** precisa de PHP 7.4+ e MySQL/MariaDB. O Cloudflare Pages (gratuito) só serve sites estáticos e **não roda PHP/MySQL**; o Cloudflare pode entrar apenas na frente (DNS/HTTPS). Opções: hospedagem compartilhada com PHP/MySQL, ou VPS.
- **Publicação manual** (upload dos arquivos), sem usar o GitHub para publicar.
- **Enviar:** as páginas `.php` da raiz e as pastas `includes/`, `assets/` e `uploads/` (com o `.htaccess` dela).
- **Não enviar:** `database/`, `CONTEXTO.md`, `README.md`, `.gitignore`, a pasta `.git` e arquivos auxiliares locais.
- **`includes/config.php`** vai com os dados do banco **da hospedagem** (editar uma cópia, sem alterar o arquivo local).
- Criar o banco novo importando uma cópia **só com a estrutura** (`schema.sql`) e criar o administrador à parte (`php -r "echo password_hash('SENHA', PASSWORD_DEFAULT);"` e `INSERT INTO usuarios`).
- Conferir a licença da fonte e ativar HTTPS (e a flag `Secure` no cookie de sessão).
