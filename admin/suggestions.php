<?php
declare(strict_types=1);

require __DIR__ . '/../includes/auth.php';

require_login();

try {
    $suggestions = get_db()
        ->query('SELECT id, submitter_name, title, author, url, pages, created_at
                 FROM book_suggestions
                 ORDER BY created_at DESC')
        ->fetchAll();
} catch (Throwable $ex) {
    error_log('admin/suggestions fetch: ' . $ex->getMessage());
    $suggestions = [];
}

$flash = null;
if (isset($_GET['saved']))   $flash = 'Voordracht opgeslagen.';
if (isset($_GET['deleted'])) $flash = 'Voordracht verwijderd.';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/pure-min.css">
    <link rel="stylesheet" href="../styles.css">
    <title>Admin — Voordrachten beheren</title>
</head>
<body>
    <div class="admin-wrap">
        <header class="admin-header">
            <h1>Voordrachten beheren</h1>
            <p class="admin-meta">
                Ingelogd als <strong><?= e(current_admin_username()) ?></strong> ·
                <a href="index.php">recensies</a> ·
                <a href="logout.php">uitloggen</a> ·
                <a href="../index.php">naar de site</a>
            </p>
        </header>

        <?php if ($flash !== null): ?>
            <div class="alert alert-success"><?= e($flash) ?></div>
        <?php endif; ?>

        <?php if (empty($suggestions)): ?>
            <p>Er zijn nog geen voordrachten ingediend.</p>
        <?php else: ?>
            <table class="pure-table pure-table-horizontal admin-table">
                <thead>
                    <tr>
                        <th>Titel</th>
                        <th>Auteur</th>
                        <th>Pagina's</th>
                        <th>Voorgedragen door</th>
                        <th>Datum</th>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($suggestions as $s): ?>
                        <tr>
                            <td>
                                <?php if (!empty($s['url'])): ?>
                                    <a href="<?= e($s['url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($s['title']) ?></a>
                                <?php else: ?>
                                    <?= e($s['title']) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= e($s['author']) ?></td>
                            <td><?= $s['pages'] !== null ? (int) $s['pages'] : '' ?></td>
                            <td><?= e($s['submitter_name']) ?></td>
                            <td><?= e(format_date_nl((string) $s['created_at'])) ?></td>
                            <td class="admin-actions">
                                <a href="suggestion_edit.php?id=<?= (int) $s['id'] ?>">bewerken</a>
                                <form method="post" action="suggestion_delete.php" class="inline-form"
                                      onsubmit="return confirm('Voordracht verwijderen? Dit kan niet ongedaan worden gemaakt.');">
                                    <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
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
