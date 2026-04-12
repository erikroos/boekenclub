<?php
declare(strict_types=1);

require __DIR__ . '/../includes/auth.php';

require_login();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id <= 0) {
    header('Location: suggestions.php');
    exit;
}

$suggestion = [
    'id'             => 0,
    'submitter_name' => '',
    'title'          => '',
    'author'         => '',
    'url'            => '',
    'pages'          => '',
];

$errors = [];

// GET: haal bestaande voordracht op
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = get_db()->prepare('SELECT * FROM book_suggestions WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        echo 'Voordracht niet gevonden.';
        exit;
    }
    $suggestion = [
        'id'             => (int) $row['id'],
        'submitter_name' => (string) $row['submitter_name'],
        'title'          => (string) $row['title'],
        'author'         => (string) $row['author'],
        'url'            => (string) ($row['url'] ?? ''),
        'pages'          => $row['pages'] !== null ? (string) $row['pages'] : '',
    ];
}

// POST: valideer & sla op
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_admin_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sessie verlopen, probeer opnieuw.';
    }

    $suggestion['id']             = $id;
    $suggestion['submitter_name'] = trim((string) ($_POST['submitter_name'] ?? ''));
    $suggestion['title']          = trim((string) ($_POST['title']          ?? ''));
    $suggestion['author']         = trim((string) ($_POST['author']         ?? ''));
    $suggestion['url']            = trim((string) ($_POST['url']            ?? ''));
    $suggestion['pages']          = trim((string) ($_POST['pages']          ?? ''));

    if ($suggestion['submitter_name'] === '' || mb_strlen($suggestion['submitter_name']) > 100) {
        $errors[] = 'Naam is verplicht (max. 100 tekens).';
    }
    if ($suggestion['title'] === '' || mb_strlen($suggestion['title']) > 255) {
        $errors[] = 'Titel is verplicht (max. 255 tekens).';
    }
    if ($suggestion['author'] === '' || mb_strlen($suggestion['author']) > 255) {
        $errors[] = 'Auteur is verplicht (max. 255 tekens).';
    }

    // URL is optioneel
    if ($suggestion['url'] !== '') {
        if (mb_strlen($suggestion['url']) > 2048) {
            $errors[] = 'URL is te lang (max. 2048 tekens).';
        } elseif (!filter_var($suggestion['url'], FILTER_VALIDATE_URL)) {
            $errors[] = 'Ongeldige URL.';
        } else {
            $scheme = strtolower((string) parse_url($suggestion['url'], PHP_URL_SCHEME));
            if ($scheme !== 'http' && $scheme !== 'https') {
                $errors[] = 'URL moet beginnen met http:// of https://.';
            }
        }
    }

    // Aantal pagina's is optioneel
    $pagesValue = null;
    if ($suggestion['pages'] !== '') {
        if (!preg_match('/^\d+$/', $suggestion['pages'])) {
            $errors[] = 'Aantal pagina\'s moet een positief geheel getal zijn.';
        } else {
            $pagesInt = (int) $suggestion['pages'];
            if ($pagesInt < 1 || $pagesInt > 65535) {
                $errors[] = 'Aantal pagina\'s moet tussen 1 en 65535 liggen.';
            } else {
                $pagesValue = $pagesInt;
            }
        }
    }

    if (empty($errors)) {
        try {
            $stmt = get_db()->prepare(
                'UPDATE book_suggestions SET
                    submitter_name = :name,
                    title          = :title,
                    author         = :author,
                    url            = :url,
                    pages          = :pages
                 WHERE id = :id'
            );
            $stmt->execute([
                ':name'   => $suggestion['submitter_name'],
                ':title'  => $suggestion['title'],
                ':author' => $suggestion['author'],
                ':url'    => $suggestion['url'] !== '' ? $suggestion['url'] : null,
                ':pages'  => $pagesValue,
                ':id'     => $id,
            ]);

            header('Location: suggestions.php?saved=1');
            exit;
        } catch (PDOException $ex) {
            error_log('admin/suggestion_edit save: ' . $ex->getMessage());
            $errors[] = 'Opslaan mislukt.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/pure-min.css">
    <link rel="stylesheet" href="../styles.css">
    <title>Voordracht bewerken — Admin</title>
</head>
<body>
    <div class="admin-wrap">
        <header class="admin-header">
            <h1>Voordracht bewerken</h1>
            <p class="admin-meta">
                <a href="suggestions.php">&larr; Terug naar overzicht</a> ·
                Ingelogd als <strong><?= e(current_admin_username()) ?></strong>
            </p>
        </header>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-error">
                <strong>Er ging iets mis:</strong>
                <ul>
                    <?php foreach ($errors as $msg): ?>
                        <li><?= e($msg) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="post" class="pure-form pure-form-stacked admin-form" novalidate>
            <input type="hidden" name="csrf_token" value="<?= e(admin_csrf_token()) ?>">

            <label for="submitter_name">Voorgedragen door</label>
            <input type="text" id="submitter_name" name="submitter_name" required maxlength="100"
                   value="<?= e((string) $suggestion['submitter_name']) ?>">

            <label for="title">Titel van het boek</label>
            <input type="text" id="title" name="title" required maxlength="255"
                   value="<?= e((string) $suggestion['title']) ?>">

            <label for="author">Auteur</label>
            <input type="text" id="author" name="author" required maxlength="255"
                   value="<?= e((string) $suggestion['author']) ?>">

            <label for="url">URL <span class="label-optional">(optioneel)</span></label>
            <input type="url" id="url" name="url" maxlength="2048"
                   placeholder="https://..."
                   value="<?= e((string) $suggestion['url']) ?>">

            <label for="pages">Aantal pagina's <span class="label-optional">(optioneel)</span></label>
            <input type="number" id="pages" name="pages" min="1" max="65535" step="1"
                   inputmode="numeric"
                   value="<?= e((string) $suggestion['pages']) ?>">

            <div class="form-actions">
                <button type="submit" class="toggle-btn">Wijzigingen opslaan</button>
                <a href="suggestions.php">Annuleren</a>
            </div>
        </form>
    </div>
</body>
</html>
