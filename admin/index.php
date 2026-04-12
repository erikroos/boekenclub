<?php
declare(strict_types=1);

// Zorg dat de URL altijd eindigt op "/admin/" (met trailing slash).
// Zonder slash zou de browser een relatieve link als "logout.php"
// oplossen tegen de site-root en eindigen op /logout.php i.p.v.
// /admin/logout.php. Apache doet dit meestal automatisch; php -S niet.
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
$scriptDir   = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
if ($requestPath === $scriptDir && $scriptDir !== '') {
    $qs = !empty($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : '';
    header('Location: ' . $requestPath . '/' . $qs, true, 301);
    exit;
}

require __DIR__ . '/../includes/auth.php';

require_login();

try {
    $reviews = get_db()
        ->query('SELECT id, sequence_number, book_title, book_author, meeting_date
                 FROM reviews
                 ORDER BY sequence_number DESC')
        ->fetchAll();
} catch (Throwable $ex) {
    error_log('admin/index fetch: ' . $ex->getMessage());
    $reviews = [];
}

$flash = null;
if (isset($_GET['saved']))   $flash = 'Recensie opgeslagen.';
if (isset($_GET['deleted'])) $flash = 'Recensie verwijderd.';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/pure-min.css">
    <link rel="stylesheet" href="../styles.css">
    <title>Admin — Recensies beheren</title>
</head>
<body>
    <div class="admin-wrap">
        <header class="admin-header">
            <h1>Recensies beheren</h1>
            <p class="admin-meta">
                Ingelogd als <strong><?= e(current_admin_username()) ?></strong> ·
                <a href="suggestions.php">voordrachten</a> ·
                <a href="logout.php">uitloggen</a> ·
                <a href="../index.php">naar de site</a>
            </p>
        </header>

        <?php if ($flash !== null): ?>
            <div class="alert alert-success"><?= e($flash) ?></div>
        <?php endif; ?>

        <p><a href="edit.php" class="toggle-btn">+ Nieuwe recensie</a></p>

        <?php if (empty($reviews)): ?>
            <p>Nog geen recensies. Klik op "Nieuwe recensie" om er een toe te voegen.</p>
        <?php else: ?>
            <table class="pure-table pure-table-horizontal admin-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Titel</th>
                        <th>Auteur</th>
                        <th>Datum</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reviews as $r): ?>
                        <tr>
                            <td><?= (int) $r['sequence_number'] ?></td>
                            <td><?= e($r['book_title']) ?></td>
                            <td><?= e($r['book_author']) ?></td>
                            <td><?= e(format_date_nl((string) $r['meeting_date'])) ?></td>
                            <td class="admin-actions">
                                <a href="edit.php?id=<?= (int) $r['id'] ?>">bewerken</a>
                                <form method="post" action="delete.php" class="inline-form"
                                      onsubmit="return confirm('Recensie verwijderen? Dit kan niet ongedaan worden gemaakt.');">
                                    <input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= e(admin_csrf_token()) ?>">
                                    <button type="submit" class="link-button">verwijderen</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>
