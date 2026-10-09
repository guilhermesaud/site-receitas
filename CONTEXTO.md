# CONTEXTO DO PROJETO — Site de Receitas

> Leia este arquivo no início de cada conversa com a IA. Atualize-o sempre que uma decisão mudar.

## Visão geral
Site pessoal de receitas. Visitantes podem ver e pesquisar receitas; existe **um único administrador** (sem página de cadastro) que cadastra e edita tudo.

- **Tecnologias:** HTML, CSS, JavaScript puro, PHP (sem framework) e MySQL (PDO). Sem bibliotecas externas.
- **Ambiente local:** WampServer. Banco: `receitas_db`.
- **Pasta local:** `E:\Github\site-receitas`
- **Idioma:** nomes de variáveis, tabelas, comentários e textos em português.

## Estrutura de arquivos
**Páginas (raiz)**
- `index.php` — grid de receitas, com busca por título/ingrediente (`?q=`).
- `receita.php` — visualização completa de uma receita (`?id=`).
- `nova_receita.php` / `editar_receita.php` — formulário de cadastro/edição (admin).
- `excluir_receita.php` — exclui a receita e a pasta `uploads/{ID}/` (POST, admin).
- `login.php` / `logout.php` — autenticação do administrador.
- `perfil.php` — login e senha do admin; `perfil_foto.php` — endpoint AJAX da foto de perfil (JSON).
- `configuracoes.php` — painel com abas: Receitas, Categorias, Ocasiões, Culinárias, Custo, Utensílios, Usuários e Cor.

**includes/**
- `config.php` — conexão com o banco e funções auxiliares. **Não vai para o GitHub** (está no `.gitignore`). Use `config.exemplo.php` como modelo.
- `header.php` / `footer.php` — layout global; o footer exibe as notificações (toasts) pendentes.
- `form_receita.php` — formulário de receita (HTML e templates JS).
- `salvar_receita.php` — validação, gravação e utilitários de upload.

**assets/**
- `css/style.css` — um único CSS. Os ajustes foram acrescentados em blocos no final; blocos posteriores sobrescrevem os anteriores.
- `js/app.js` — tema, dropdown do perfil, áreas de imagem, passos e utensílios dinâmicos, função `mostrarNotificacao()`.
- `js/configuracoes.js` — abas, busca em tempo real, prévia da cor.
- `js/perfil.js` — foto por AJAX e olho da senha.
- `fonts/` — a fonte "Muro" (o `header.php` usa automaticamente o primeiro arquivo de fonte da pasta).

**uploads/** — imagens enviadas. `uploads/.htaccess` bloqueia execução de scripts.

## Banco de dados
Tabelas: `receitas`, `categorias`, `ocasioes`, `culinarias`, `custos`, `utensilios`, `receita_utensilios`, `passos`, `usuarios`.

- Opções (categoria, ocasião, culinária, custo): `ON DELETE RESTRICT` — não se exclui opção em uso.
- `passos` e `receita_utensilios`: `ON DELETE CASCADE` — apagar a receita apaga os vínculos.
- `usuarios`: `senha_hash` (bcrypt), `foto`, `cor_destaque`.
- Caminhos de imagem no banco são relativos a `uploads/` (ex.: `7/a1b2c3.jpg`, `users/e5f6.png`).

**Scripts SQL** (cada um roda UMA vez). Ordem que respeita as dependências entre eles:
1. `database.sql`
2. `atualizar_banco.sql` (usuários, passos, `imagem_capa`)
3. `atualizar_categorias.sql`
4. `atualizar_campos.sql` (ocasião, culinária, custo)
5. `atualizar_perfil.sql` (foto)
6. `atualizar_cor.sql`
7. `atualizar_passos.sql` (título do passo)
8. `atualizar_utensilios.sql`

> Atenção: a ordem real em que foram aplicados no banco local pode ter sido diferente. Antes de publicar o site, exportar o banco pelo phpMyAdmin (só a estrutura) e salvar como `schema.sql`.

## Funcionalidades prontas
- Receita: título, categoria, ocasião, culinária, custo, dificuldade, tempo, rendimento, capa, vídeo do YouTube, ingredientes (um por linha), utensílios (quantidade + utensílio, opcionais) e passos (título opcional, texto e imagem opcional).
- Uploads em `uploads/{ID da receita}/` e `uploads/users/`; excluir a receita apaga a pasta inteira.
- Painel de configurações com CRUD genérico de listas (array `$listas` em `configuracoes.php`); a mesma lógica serve para todas as listas.
- Tema claro/escuro (classe `.dark-mode` no `<html>`, preferência no `localStorage`); cor de destaque (`.acc-*`) vale só para o admin logado.
- Perfil: foto por AJAX, trocar login e senha (sem exigir a senha atual).
- Notificações flutuantes (toasts): `flash()` no PHP e `mostrarNotificacao()` no JS.

## Decisões de design (manter)
- Escala reduzida (base 14px), paleta neutra, rodapé fixo, `.container` de 1040px em todas as páginas.
- Imagens (capa e passos) seguem a UI do avatar: clique para enviar, preview imediato, X redondo para remover. Raio padronizado na classe `.img-padrao`.
- Ações de tabela só com ícones SVG (lápis, lixeira, disquete).
- Cabeçalho padrão dos CRUDs: `.crud-header`.
- Página da receita: capa ~80% + infos rápidas ~20% à direita; ocasião/culinária sob o título; custo nas infos rápidas; utensílios à direita dos ingredientes (seção só aparece se houver).
- Formulário de receita com a mesma largura do `.container`; botão "+" só com ícone; "Salvar receita" no canto inferior direito.
- Títulos de até 150 caracteres devem quebrar de linha sem vazar.
- A lista chama-se "Custo" (singular) em todo o sistema.

## Segurança (não remover)
- Consultas com PDO e prepared statements; saída sempre com `e()`.
- Token CSRF em todo formulário POST (`csrf_exigir()`); páginas administrativas com `exige_admin()`.
- Upload: confere o tipo real (finfo), só JPG/PNG/WEBP, máximo 2 MB.
- Senha com `password_hash`; `session_regenerate_id` no login.
- `config.php` nunca vai para o Git.

## Pendências e ideias
- (preencher)

## Como trabalhar
1. Testar local no WampServer.
2. Commit pequeno e com mensagem clara a cada ajuste.
3. Atualizar este arquivo quando algo relevante mudar.
