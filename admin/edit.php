<?php
declare(strict_types=1);

require __DIR__ . '/../includes/auth.php';

require_login();

$id     = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$isEdit = $id > 0;

$review = [
    'id'              => 0,
    'sequence_number' => '',
    'book_title'      => '',
    'book_author'     => '',
    'meeting_date'    => '',
    'host_name'       => '',
    'host_location'   => '',
    'attendees'       => '',
    'verdict'         => '',
    'preview'         => '',
    'full_html'       => '',
];

$errors = [];

// Bij GET & edit: haal bestaande recensie op
if ($isEdit && $_SERVER['REQUEST_METHOD'] === 'GET') {
    $stmt = get_db()->prepare('SELECT * FROM reviews WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    if (!$row) {
        http_response_code(404);
        echo 'Recensie niet gevonden.';
        exit;
    }
    $review = $row;
}

// POST: valideer & sla op
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_admin_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sessie verlopen, probeer opnieuw.';
    }

    // Haal invoer op (behoud het in $review zodat het formulier opnieuw gevuld wordt)
    $review['sequence_number'] = trim((string) ($_POST['sequence_number'] ?? ''));
    $review['book_title']      = trim((string) ($_POST['book_title']      ?? ''));
    $review['book_author']     = trim((string) ($_POST['book_author']     ?? ''));
    $review['meeting_date']    = trim((string) ($_POST['meeting_date']    ?? ''));
    $review['host_name']       = trim((string) ($_POST['host_name']       ?? ''));
    $review['host_location']   = trim((string) ($_POST['host_location']   ?? ''));
    $review['attendees']       = trim((string) ($_POST['attendees']       ?? ''));
    $review['verdict']         = trim((string) ($_POST['verdict']         ?? ''));
    $review['preview']         = trim((string) ($_POST['preview']         ?? ''));
    // full_html niet trimmen: bewuste whitespace in HTML behouden
    $review['full_html']       = (string) ($_POST['full_html'] ?? '');

    if (!ctype_digit((string) $review['sequence_number']) || (int) $review['sequence_number'] < 1) {
        $errors[] = 'Volgnummer moet een positief geheel getal zijn.';
    }
    if ($review['book_title'] === '' || mb_strlen($review['book_title']) > 255) {
        $errors[] = 'Titel is verplicht (max. 255 tekens).';
    }
    if ($review['book_author'] === '' || mb_strlen($review['book_author']) > 255) {
        $errors[] = 'Auteur is verplicht (max. 255 tekens).';
    }
    $dt = DateTime::createFromFormat('Y-m-d', $review['meeting_date']);
    if (!$dt || $dt->format('Y-m-d') !== $review['meeting_date']) {
        $errors[] = 'Datum is verplicht en moet formaat jjjj-mm-dd hebben.';
    }
    if ($review['host_name'] === '' || mb_strlen($review['host_name']) > 100) {
        $errors[] = 'Gastheer/-vrouw is verplicht (max. 100 tekens).';
    }
    if ($review['host_location'] === '' || mb_strlen($review['host_location']) > 100) {
        $errors[] = 'Locatie is verplicht (max. 100 tekens).';
    }
    if (mb_strlen($review['attendees']) > 500) {
        $errors[] = 'Aanwezigen te lang (max. 500 tekens).';
    }
    if (mb_strlen($review['verdict']) > 100) {
        $errors[] = 'Oordeel te lang (max. 100 tekens).';
    }

    if (empty($errors)) {
        try {
            $params = [
                ':seq'       => (int) $review['sequence_number'],
                ':title'     => $review['book_title'],
                ':author'    => $review['book_author'],
                ':date'      => $review['meeting_date'],
                ':host'      => $review['host_name'],
                ':location'  => $review['host_location'],
                ':attendees' => $review['attendees'] !== '' ? $review['attendees'] : null,
                ':verdict'   => $review['verdict']   !== '' ? $review['verdict']   : null,
                ':preview'   => $review['preview']   !== '' ? $review['preview']   : null,
                ':full_html' => $review['full_html'] !== '' ? $review['full_html'] : null,
            ];

            if ($isEdit) {
                $stmt = get_db()->prepare(
                    'UPDATE reviews SET
                        sequence_number = :seq,
                        book_title      = :title,
                        book_author     = :author,
                        meeting_date    = :date,
                        host_name       = :host,
                        host_location   = :location,
                        attendees       = :attendees,
                        verdict         = :verdict,
                        preview         = :preview,
                        full_html       = :full_html
                     WHERE id = :id'
                );
                $params[':id'] = $id;
                $stmt->execute($params);
            } else {
                $stmt = get_db()->prepare(
                    'INSERT INTO reviews
                        (sequence_number, book_title, book_author, meeting_date,
                         host_name, host_location, attendees, verdict, preview, full_html)
                     VALUES
                        (:seq, :title, :author, :date,
                         :host, :location, :attendees, :verdict, :preview, :full_html)'
                );
                $stmt->execute($params);
            }

            header('Location: index.php?saved=1');
            exit;
        } catch (PDOException $ex) {
            if ($ex->getCode() === '23000') {
                $errors[] = 'Er bestaat al een recensie met dit volgnummer.';
            } else {
                error_log('admin/edit save: ' . $ex->getMessage());
                $errors[] = 'Opslaan mislukt.';
            }
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
    <title><?= $isEdit ? 'Recensie bewerken' : 'Nieuwe recensie' ?> — Admin</title>
</head>
<body>
    <div class="admin-wrap">
        <header class="admin-header">
            <h1><?= $isEdit ? 'Recensie bewerken' : 'Nieuwe recensie' ?></h1>
            <p class="admin-meta">
                <a href="index.php">&larr; Terug naar overzicht</a> ·
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

            <label for="sequence_number">Volgnummer (#)</label>
            <input type="number" id="sequence_number" name="sequence_number" min="1" required
                   value="<?= e((string) $review['sequence_number']) ?>">

            <label for="book_title">Titel van het boek</label>
            <input type="text" id="book_title" name="book_title" required maxlength="255"
                   value="<?= e((string) $review['book_title']) ?>">

            <label for="book_author">Auteur</label>
            <input type="text" id="book_author" name="book_author" required maxlength="255"
                   value="<?= e((string) $review['book_author']) ?>">

            <label for="meeting_date">Datum bijeenkomst (jjjj-mm-dd)</label>
            <input type="date" id="meeting_date" name="meeting_date" required
                   value="<?= e((string) $review['meeting_date']) ?>">

            <label for="host_name">Gastheer/-vrouw</label>
            <input type="text" id="host_name" name="host_name" required maxlength="100"
                   value="<?= e((string) $review['host_name']) ?>">

            <label for="host_location">Locatie</label>
            <input type="text" id="host_location" name="host_location" required maxlength="100"
                   value="<?= e((string) $review['host_location']) ?>">

            <label for="attendees">Aanwezigen <span class="label-optional">(optioneel)</span></label>
            <input type="text" id="attendees" name="attendees" maxlength="500"
                   value="<?= e((string) $review['attendees']) ?>">

            <label for="verdict">Oordeel <span class="label-optional">(optioneel, bijv. "positief")</span></label>
            <input type="text" id="verdict" name="verdict" maxlength="100"
                   value="<?= e((string) $review['verdict']) ?>">

            <label for="preview">Preview <span class="label-optional">(eerste paar zinnen, plain text)</span></label>
            <textarea id="preview" name="preview" rows="3"><?= e((string) $review['preview']) ?></textarea>

            <label for="full_html">Volledige tekst <span class="label-optional">(HTML toegestaan: &lt;p&gt;, &lt;a&gt;, &lt;b&gt;, &lt;i&gt;, &hellip;)</span></label>
            <textarea id="full_html" name="full_html" rows="15" class="html-editor"><?= e((string) $review['full_html']) ?></textarea>

            <div class="form-actions">
                <button type="submit" class="toggle-btn"><?= $isEdit ? 'Wijzigingen opslaan' : 'Aanmaken' ?></button>
                <a href="index.php">Annuleren</a>
            </div>
        </form>
    </div>
</body>
</html>
