<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

/**
 * Start (als dat nog niet gebeurd is) de sessie. Idempotent.
 */
function ensure_session(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Is de huidige bezoeker ingelogd als admin?
 */
function is_logged_in(): bool
{
    ensure_session();
    return !empty($_SESSION['admin_id']);
}

function current_admin_username(): ?string
{
    ensure_session();
    return $_SESSION['admin_username'] ?? null;
}

/**
 * Redirect naar de loginpagina als er niemand is ingelogd.
 * Gebruik bovenaan elke admin-pagina.
 */
function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Probeert in te loggen met (username, password). Geeft true/false terug.
 * Beschermt tegen timing-attacks door bij een niet-bestaande user ook
 * een password_verify-call te doen.
 */
function attempt_login(string $username, string $password): bool
{
    $stmt = get_db()->prepare(
        'SELECT id, username, password_hash FROM admins WHERE username = ? LIMIT 1'
    );
    $stmt->execute([$username]);
    $row = $stmt->fetch();

    if (!$row) {
        // dummy-verify zodat de responstijd vergelijkbaar is met een echte login
        password_verify($password, '$2y$12$abcdefghijklmnopqrstuuvOa/W0JtIEiCZPlpqrM1OvB0Q/TZQgQnS');
        return false;
    }

    if (!password_verify($password, $row['password_hash'])) {
        return false;
    }

    ensure_session();
    session_regenerate_id(true); // voorkomt session fixation
    $_SESSION['admin_id']       = (int) $row['id'];
    $_SESSION['admin_username'] = (string) $row['username'];

    // Als de hash ouder is dan de huidige default, meteen rehashen.
    if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
        $new = password_hash($password, PASSWORD_DEFAULT);
        $upd = get_db()->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
        $upd->execute([$new, (int) $row['id']]);
    }

    return true;
}

/**
 * Logt de huidige gebruiker uit en gooit de sessie weg.
 */
function logout(): void
{
    ensure_session();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params['path'],
            $params['domain'],
            $params['secure'],
            $params['httponly']
        );
    }
    session_destroy();
}

/**
 * CSRF-token voor admin-forms. Aparte sleutel, zodat het niet botst
 * met de CSRF van het publieke leeslijst-formulier.
 */
function admin_csrf_token(): string
{
    ensure_session();
    if (empty($_SESSION['admin_csrf_token'])) {
        $_SESSION['admin_csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['admin_csrf_token'];
}

function check_admin_csrf(?string $token): bool
{
    ensure_session();
    return is_string($token)
        && !empty($_SESSION['admin_csrf_token'])
        && hash_equals($_SESSION['admin_csrf_token'], $token);
}
