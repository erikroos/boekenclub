<?php
declare(strict_types=1);

require __DIR__ . '/../includes/auth.php';

require_login();

$id     = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;

$book = [
    'id'      => 0,
    'title'   => '',
    'author'  => '',
    'status'  => ($_GET['status'] ?? 'to_read') === 'read' ? 'read' : 'to_read',
    'rating'        => '',
    'date_finished' => '',
    'comment'       => '',
];

$errors = [];

// GET & edit: haal bestaand boek op
if ($isEdit && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = get_db()->prepare('SELECT * FROM personal_books WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        echo 'Boek niet gevonden.';
        exit;
    }
    $book = [
        'id'      => (int) $row['id'],
        'title'   => (string) $row['title'],
        'author'  => (string) $row['author'],
        'status'  => (string) $row['status'],
        'rating'        => $row['rating'] !== null ? (string) $row['rating'] : '',
        'date_finished' => (string) ($row['date_finished'] ?? ''),
        'comment'       => (string) ($row['comment'] ?? ''),
    ];
}

// POST: valideer & sla op
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_admin_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sessie verlopen, probeer opnieuw.';
    }

    $book['title']   = trim((string) ($_POST['title']   ?? ''));
    $book['author']  = trim((string) ($_POST['author']  ?? ''));
    $book['status']  = ($_POST['status'] ?? 'to_read') === 'read' ? 'read' : 'to_read';
    $book['rating']        = trim((string) ($_POST['rating']        ?? ''));
    $book['date_finished'] = trim((string) ($_POST['date_finished'] ?? ''));
    $book['comment']       = trim((string) ($_POST['comment']       ?? ''));

    if ($book['title'] === '' || mb_strlen($book['title']) > 255) {
        $errors[] = 'Titel is verplicht (max. 255 tekens).';
    }
    if ($book['author'] === '' || mb_strlen($book['author']) > 255) {
        $errors[] = 'Auteur is verplicht (max. 255 tekens).';
    }

    $ratingValue    = null;
    $dateFinished   = null;
    if ($book['status'] === 'read') {
        if ($book['rating'] === '') {
            $errors[] = 'Beoordeling is verplicht voor gelezen boeken.';
        } elseif (!preg_match('/^[1-5]$/', $book['rating'])) {
            $errors[] = 'Beoordeling moet een waarde van 1 t/m 5 zijn.';
        } else {
            $ratingValue = (int) $book['rating'];
        }

        if ($book['date_finished'] === '') {
            $errors[] = 'Datum uitgelezen is verplicht voor gelezen boeken.';
        } else {
            $dt = DateTime::createFromFormat('Y-m-d', $book['date_finished']);
            if (!$dt || $dt->format('Y-m-d') !== $book['date_finished']) {
                $errors[] = 'Datum uitgelezen moet formaat jjjj-mm-dd hebben.';
            } else {
                $dateFinished = $book['date_finished'];
            }
        }
    }

    if (mb_strlen($book['comment']) > 10000) {
        $errors[] = 'Commentaar is te lang (max. 10.000 tekens).';
    }

    if (empty($errors)) {
        try {
            $params = [
                ':title'          => $book['title'],
                ':author'         => $book['author'],
                ':status'         => $book['status'],
                ':rating'         => $ratingValue,
                ':date_finished'  => $dateFinished,
                ':comment'        => $book['comment'] !== '' ? $book['comment'] : null,
            ];

            if ($isEdit) {
                $stmt = get_db()->prepare(
                    'UPDATE personal_books SET
                        title          = :title,
                        author         = :author,
                        status         = :status,
                        rating         = :rating,
                        date_finished  = :date_finished,
                        comment        = :comment
                     WHERE id = :id'
                );
                $params[':id'] = $id;
                $stmt->execute($params);
            } else {
                $stmt = get_db()->prepare(
                    'INSERT INTO personal_books (title, author, status, rating, date_finished, comment)
                     VALUES (:title, :author, :status, :rating, :date_finished, :comment)'
                );
                $stmt->execute($params);
            }

            header('Location: books.php?tab=' . $book['status'] . '&saved=1');
            exit;
        } catch (PDOException $ex) {
            error_log('admin/book_edit save: ' . $ex->getMessage());
            $errors[] = 'Opslaan mislukt.';
        }
    }
}

$isRead = $book['status'] === 'read';
$pageTitle = $isEdit
    ? 'Boek bewerken'
    : ($isRead ? 'Gelezen boek toevoegen' : 'Boek toevoegen aan leeslijst');
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/pure-min.css">
    <link rel="stylesheet" href="../styles.css">
    <title><?= e($pageTitle) ?> — Admin</title>
</head>
<body>
    <div class="admin-wrap">
        <header class="admin-header">
            <h1><?= e($pageTitle) ?></h1>
            <p class="admin-meta">
                <a href="books.php?tab=<?= e($book['status']) ?>">&larr; Terug naar overzicht</a> ·
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

        <form method="post" class="pure-form pure-form-stacked admin-form" novalidate
              id="bookForm">
            <input type="hidden" name="csrf_token" value="<?= e(admin_csrf_token()) ?>">

            <label for="title">Titel</label>
            <input type="text" id="title" name="title" required maxlength="255"
                   value="<?= e($book['title']) ?>">

            <label for="author">Auteur</label>
            <input type="text" id="author" name="author" required maxlength="255"
                   value="<?= e($book['author']) ?>">

            <label for="status">Status</label>
            <select id="status" name="status" onchange="toggleReadFields()">
                <option value="to_read" <?= $book['status'] === 'to_read' ? 'selected' : '' ?>>Te lezen</option>
                <option value="read" <?= $book['status'] === 'read' ? 'selected' : '' ?>>Gelezen</option>
            </select>

            <div id="read-fields" style="<?= $book['status'] !== 'read' ? 'display:none' : '' ?>">
                <label for="rating">Beoordeling (1–5)</label>
                <select id="rating" name="rating">
                    <option value="">— Kies —</option>
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <option value="<?= $i ?>" <?= $book['rating'] === (string) $i ? 'selected' : '' ?>>
                            <?= str_repeat('★', $i) . str_repeat('☆', 5 - $i) ?>
                        </option>
                    <?php endfor; ?>
                </select>

                <label for="date_finished">Uitgelezen op (jjjj-mm-dd)</label>
                <input type="date" id="date_finished" name="date_finished"
                       value="<?= e($book['date_finished']) ?>">

                <label for="comment">Commentaar <span class="label-optional">(optioneel)</span></label>
                <textarea id="comment" name="comment" rows="4"><?= e($book['comment']) ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="toggle-btn"><?= $isEdit ? 'Wijzigingen opslaan' : 'Toevoegen' ?></button>
                <a href="books.php?tab=<?= e($book['status']) ?>">Annuleren</a>
            </div>
        </form>
    </div>

    <script>
    function toggleReadFields() {
        var status = document.getElementById('status').value;
        document.getElementById('read-fields').style.display = status === 'read' ? '' : 'none';
    }
    </script>
</body>
</html>
