<?php
/* Configurações, conexão com o MySQL (PDO) e funções auxiliares. */

const DB_HOST = ''; //IP
const DB_NAME = ''; // Nome Banco
const DB_USER = ''; // Usuário
const DB_PASS = ''; // Senha

const DIFICULDADES = ['Fácil', 'Médio', 'Difícil'];

/** Retorna a conexão PDO (criada uma única vez). */
function db(): PDO {
    static $pdo = null;
    if (!$pdo) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER, DB_PASS,
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }
    return $pdo;
}

/** Escapa texto para HTML (evita XSS). */
function e($texto): string {
    return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
}

/** 90 -> "1h 30min" | 45 -> "45 min" */
function fmt_tempo(int $min): string {
    if ($min < 60) return $min . ' min';
    return intdiv($min, 60) . 'h' . ($min % 60 ? ' ' . ($min % 60) . 'min' : '');
}

/** Quebra um texto em linhas não vazias (usado em ingredientes e preparo). */
function linhas(string $texto): array {
    return array_values(array_filter(array_map('trim', preg_split('/\R/', $texto))));
}

/** Converte um link do YouTube na URL de incorporação (ou null se inválido). */
function youtube_embed(?string $url): ?string {
    if ($url && preg_match('~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/))([\w-]{11})~', $url, $m)) {
        return 'https://www.youtube.com/embed/' . $m[1];
    }
    return null;
}

/* ---------- Sessão e autenticação ---------- */
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

function eh_admin(): bool {
    return !empty($_SESSION['admin_id']);
}

/** Barra visitantes nas páginas administrativas. */
function exige_admin(): void {
    if (!eh_admin()) { header('Location: login.php'); exit; }
}

/** Token anti-CSRF (vai em um campo hidden de todo formulário POST). */
function csrf_token(): string {
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
}

function csrf_exigir(): void {
    if (!hash_equals(csrf_token(), (string)($_POST['csrf'] ?? ''))) {
        http_response_code(400);
        exit('Requisição inválida. Volte e tente novamente.');
    }
}

/** Ícone genérico de avatar (usuário sem foto de perfil). */
function avatar_svg(): string {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/></svg>';
}

/** Cores de destaque disponíveis (a chave é o valor gravado em usuarios.cor_destaque). */
const CORES = ['neutro' => 'Padrão', 'vermelho' => 'Vermelho', 'verde' => 'Verde', 'azul' => 'Azul', 'laranja' => 'Laranja',
               'rosa' => 'Rosa', 'roxo' => 'Roxo', 'amarelo' => 'Amarelo', 'cyan' => 'Cyan'];

/** Categorias cadastradas no banco: [id => nome], em ordem alfabética. */
function categorias(): array {
    return db()->query('SELECT id, nome FROM categorias ORDER BY nome')->fetchAll(PDO::FETCH_KEY_PAIR);
}

/** Guarda uma notificação (toast). Aparece na próxima página carregada — ou nesta mesma, se não houver redirect. */
function flash(string $mensagem, string $tipo = 'sucesso'): void {   // tipo: sucesso | erro | info
    $_SESSION['flash'][] = ['msg' => $mensagem, 'tipo' => $tipo];
}

/** Opções cadastradas de uma lista (categorias, ocasioes, culinarias ou custos): [id => nome]. */
function opcoes(string $tabela): array {
    if (!in_array($tabela, ['categorias', 'ocasioes', 'culinarias', 'custos', 'utensilios'], true)) return [];
    return db()->query("SELECT id, nome FROM $tabela ORDER BY nome")->fetchAll(PDO::FETCH_KEY_PAIR);
}
