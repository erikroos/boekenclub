<?php
declare(strict_types=1);

require __DIR__ . '/../includes/auth.php';

require_login();

$search = trim((string) ($_GET['q'] ?? ''));
$tab    = ($_GET['tab'] ?? 'read') === 'to_read' ? 'to_read' : 'read';

try {
    if ($search !== '' && $tab === 'read') {
        $stmt = get_db()->prepare(
            'SELECT * FROM personal_books
             WHERE status = \'read\'
               AND (title LIKE :q OR author LIKE :q OR comment LIKE :q)
             ORDER BY updated_at DESC'
        );
        $stmt->execute([':q' => '%' . $search . '%']);
    } else {
        $stmt = get_db()->prepare(
            'SELECT * FROM personal_books WHERE status = :status ORDER BY updated_at DESC'
        );
        $stmt->execute([':status' => $tab]);
    }
    $books = $stmt->fetchAll();
} catch (Throwable $ex) {
    error_log('admin/books fetch: ' . $ex->getMessage());
    $books = [];
}

$flash = null;
if (isset($_GET['saved']))   $flash = 'Boek opgeslagen.';
if (isset($_GET['deleted'])) $flash = 'Boek verwijderd.';
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/pure-min.css">
    <link rel="stylesheet" href="../styles.css">
    <title>Admin — Mijn boeken</title>
</head>
<body>
    <div class="admin-wrap">
        <header class="admin-header">
            <h1>Mijn boeken</h1>
            <p class="admin-meta">
                Ingelogd als <strong><?= e(current_admin_username()) ?></strong> ·
                <a href="index.php">recensies</a> ·
                <a href="suggestions.php">voordrachten</a> ·
                <a href="logout.php">uitloggen</a> ·
                <a href="../index.php">naar de site</a>
            </p>
        </header>

        <?php if ($flash !== null): ?>
            <div class="alert alert-success"><?= e($flash) ?></div>
        <?php endif; ?>

        <div class="tabs">
            <a href="books.php?tab=read" class="toggle-btn<?= $tab === 'read' ? ' tab-active' : '' ?>">Gelezen</a>
            <a href="books.php?tab=to_read" class="toggle-btn<?= $tab === 'to_read' ? ' tab-active' : '' ?>">Te lezen</a>
        </div>

        <?php if ($tab === 'read'): ?>
            <form method="get" action="books.php" class="pure-form book-search-form">
                <input type="hidden" name="tab" value="read">
                <input type="text" name="q" placeholder="Zoek in gelezen boeken…"
                       value="<?= e($search) ?>">
                <button type="submit" class="toggle-btn">Zoeken</button>
                <?php if ($search !== ''): ?>
                    <a href="books.php?tab=read">Wis zoekopdracht</a>
                <?php endif; ?>
            </form>
        <?php endif; ?>

        <p>
            <a href="book_edit.php?status=<?= e($tab) ?>" class="toggle-btn">
                + <?= $tab === 'read' ? 'Gelezen boek toevoegen' : 'Boek toevoegen aan leeslijst' ?>
            </a>
        </p>

        <?php if ($search !== '' && $tab === 'read'): ?>
            <p><?= count($books) ?> resultaat<?= count($books) === 1 ? '' : 'aten' ?> voor "<?= e($search) ?>".</p>
        <?php endif; ?>

        <?php if (empty($books)): ?>
            <p><?= $tab === 'read' ? 'Nog geen gelezen boeken.' : 'De leeslijst is leeg.' ?></p>
        <?php else: ?>
            <table class="pure-table pure-table-horizontal admin-table">
                <thead>
                    <tr>
                        <th>Titel</th>
                        <th>Auteur</th>
                        <?php if ($tab === 'read'): ?>
                            <th>Beoordeling</th>
                            <th>Uitgelezen op</th>
                            <th>Commentaar</th>
                        <?php endif; ?>
                        <th>Acties</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($books as $b): ?>
                        <tr>
                            <td><?= e($b['title']) ?></td>
                            <td><?= e($b['author']) ?></td>
                            <?php if ($tab === 'read'): ?>
                                <td><?= $b['rating'] !== null ? str_repeat('★', (int) $b['rating']) . str_repeat('☆', 5 - (int) $b['rating']) : '' ?></td>
                                <td><?= !empty($b['date_finished']) ? e(format_date_nl((string) $b['date_finished'])) : '' ?></td>
                                <td><?= e((string) ($b['comment'] ?? '')) ?></td>
                            <?php endif; ?>
                            <td class="admin-actions">
                                <a href="book_edit.php?id=<?= (int) $b['id'] ?>">bewerken</a>
                                <form method="post" action="book_delete.php" class="inline-form"
                                      onsubmit="return confirm('Boek verwijderen?');">
                                    <input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                                    <input type="hidden" name="tab" value="<?= e($tab) ?>">
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
