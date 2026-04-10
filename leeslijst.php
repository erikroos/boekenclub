<?php
declare(strict_types=1);

session_start();
require __DIR__ . '/includes/db.php';

// --- CSRF-token en captcha voorbereiden -------------------------------------
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
if (!isset($_SESSION['captcha_a'], $_SESSION['captcha_b'])) {
    $_SESSION['captcha_a'] = random_int(1, 9);
    $_SESSION['captcha_b'] = random_int(1, 9);
}

$errors  = [];
$success = false;
$old     = ['name' => '', 'title' => '', 'author' => '', 'url' => ''];

// --- Formulier verwerken ----------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1. Rate limiting per sessie (voorkomt snel achter elkaar posten)
    $now = time();
    if (!empty($_SESSION['last_submit']) && ($now - (int) $_SESSION['last_submit']) < 10) {
        $errors[] = 'Rustig aan! Wacht even voordat u nog een boek indient.';
    }

    // 2. CSRF-controle
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'], $token)) {
        $errors[] = 'De sessie is verlopen. Ververs de pagina en probeer het opnieuw.';
    }

    // 3. Honeypot — bots vullen vaak alle velden in
    if (!empty($_POST['website'])) {
        // Doe alsof het goed ging, maar sla niets op.
        header('Location: leeslijst.php?ok=1');
        exit;
    }

    // 4. Captcha (eenvoudige rekensom, geen externe dienst nodig)
    $captchaInput = $_POST['captcha'] ?? '';
    $expected     = (int) $_SESSION['captcha_a'] + (int) $_SESSION['captcha_b'];
    if (!is_numeric($captchaInput) || (int) $captchaInput !== $expected) {
        $errors[] = 'De uitkomst van de rekensom klopt niet.';
    }

    // 5. Invoer opschonen en valideren
    $name   = trim((string) ($_POST['name']   ?? ''));
    $title  = trim((string) ($_POST['title']  ?? ''));
    $author = trim((string) ($_POST['author'] ?? ''));
    $url    = trim((string) ($_POST['url']    ?? ''));
    $old    = compact('name', 'title', 'author', 'url');

    // Alleen zichtbare tekens toestaan (geen control chars)
    foreach (['name' => $name, 'title' => $title, 'author' => $author, 'url' => $url] as $key => $val) {
        if ($val !== '' && preg_match('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', $val)) {
            $errors[] = 'Ongeldige tekens in het veld "' . $key . '".';
        }
    }

    if ($name === '') {
        $errors[] = 'Vul uw naam in.';
    } elseif (mb_strlen($name) > 100) {
        $errors[] = 'Naam is te lang (max. 100 tekens).';
    } elseif (mb_strlen($name) < 2) {
        $errors[] = 'Naam is te kort (minstens 2 tekens).';
    }

    if ($title === '') {
        $errors[] = 'Vul de titel van het boek in.';
    } elseif (mb_strlen($title) > 255) {
        $errors[] = 'Titel is te lang (max. 255 tekens).';
    }

    if ($author === '') {
        $errors[] = 'Vul de auteur van het boek in.';
    } elseif (mb_strlen($author) > 255) {
        $errors[] = 'Auteur is te lang (max. 255 tekens).';
    }

    // URL is optioneel, maar als ingevuld moet het een geldige http(s)-URL zijn
    if ($url !== '') {
        if (mb_strlen($url) > 2048) {
            $errors[] = 'URL is te lang (max. 2048 tekens).';
        } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
            $errors[] = 'Ongeldige URL.';
        } else {
            $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
            if ($scheme !== 'http' && $scheme !== 'https') {
                $errors[] = 'URL moet beginnen met http:// of https://.';
            }
        }
    }

    // 6. Opslaan
    if (empty($errors)) {
        try {
            $stmt = get_db()->prepare(
                'INSERT INTO book_suggestions (submitter_name, title, author, url)
                 VALUES (:name, :title, :author, :url)'
            );
            $stmt->execute([
                ':name'   => $name,
                ':title'  => $title,
                ':author' => $author,
                ':url'    => $url !== '' ? $url : null,
            ]);

            $_SESSION['last_submit'] = $now;
            // Nieuwe captcha genereren
            $_SESSION['captcha_a'] = random_int(1, 9);
            $_SESSION['captcha_b'] = random_int(1, 9);

            // POST/Redirect/GET voorkomt dubbel insturen bij refresh
            header('Location: leeslijst.php?ok=1');
            exit;
        } catch (PDOException $ex) {
            error_log('Boekenclub DB insert: ' . $ex->getMessage());
            $errors[] = 'Er ging iets mis bij het opslaan. Probeer het later nog eens.';
        }
    }
}

if (isset($_GET['ok'])) {
    $success = true;
}

// --- Lijst ophalen ----------------------------------------------------------
$suggestions = [];
$dbError     = null;
try {
    $stmt = get_db()->query(
        'SELECT submitter_name, title, author, url, created_at
         FROM book_suggestions
         ORDER BY created_at DESC'
    );
    $suggestions = $stmt->fetchAll();
} catch (Throwable $ex) {
    error_log('Boekenclub DB fetch: ' . $ex->getMessage());
    $dbError = 'De leeslijst kan op dit moment niet geladen worden.';
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/pure-min.css" integrity="sha384-X38yfunGUhNzHpBaEBsWLO+A0HDYOQi8ufWDkZ0k9e0eXz/tH3II7uKZ9msv++Ls" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/purecss@3.0.0/build/grids-responsive-min.css">
    <link rel="stylesheet" href="styles.css">
    <title>Leeslijst — HBO-ICT Boekenclub</title>
    <link rel="icon" type="image/x-icon" href="/images/open-book-icon.jpg">
</head>
<body>
    <div id="layout" class="pure-g">
        <!-- Sidebar -->
        <div class="sidebar pure-u-1 pure-u-md-1-5">
            <div class="header">
                <h1 class="brand-title">HBO-ICT Boekenclub</h1>
                <h2 class="brand-tagline">Suggesties voor onze leeslijst zijn van harte welkom. Gebruik het formulier hiernaast om een boek voor te dragen.</h2>

                <nav class="nav">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="index.php" class="pure-button">&larr; Terug naar de bijeenkomsten</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>

        <!-- Main Content -->
        <div class="content pure-u-1 pure-u-md-4-5">
            <div class="posts">

                <section class="post">
                    <header class="post-header">
                        <h3 class="post-title">Draag een boek voor</h3>
                        <p class="post-meta">Vul uw naam, de boektitel en de auteur in. U hoeft niet ingelogd te zijn.</p>
                    </header>

                    <div class="post-description">
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                Bedankt! Uw suggestie staat in de lijst.
                            </div>
                        <?php endif; ?>

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

                        <form method="post" action="leeslijst.php" class="pure-form pure-form-stacked suggest-form" novalidate>
                            <input type="hidden" name="csrf_token" value="<?= e($_SESSION['csrf_token']) ?>">

                            <!-- Honeypot: mag niet ingevuld worden, verborgen voor mensen -->
                            <div class="hp-field" aria-hidden="true">
                                <label for="website">Website (laat leeg)</label>
                                <input type="text" id="website" name="website" tabindex="-1" autocomplete="off">
                            </div>

                            <label for="name">Uw naam</label>
                            <input type="text" id="name" name="name" required maxlength="100"
                                   value="<?= e($old['name']) ?>">

                            <label for="title">Titel van het boek</label>
                            <input type="text" id="title" name="title" required maxlength="255"
                                   value="<?= e($old['title']) ?>">

                            <label for="author">Auteur</label>
                            <input type="text" id="author" name="author" required maxlength="255"
                                   value="<?= e($old['author']) ?>">

                            <label for="url">URL <span class="label-optional">(optioneel, bijv. een Wikipedia-pagina)</span></label>
                            <input type="url" id="url" name="url" maxlength="2048"
                                   placeholder="https://..."
                                   value="<?= e($old['url']) ?>">

                            <label for="captcha">
                                Even checken dat u geen robot bent:
                                hoeveel is <?= (int) $_SESSION['captcha_a'] ?> + <?= (int) $_SESSION['captcha_b'] ?>?
                            </label>
                            <input type="text" id="captcha" name="captcha" required inputmode="numeric"
                                   pattern="[0-9]+" maxlength="3" autocomplete="off">

                            <button type="submit" class="toggle-btn">Verstuur suggestie</button>
                        </form>
                    </div>
                </section>

                <section class="post">
                    <header class="post-header">
                        <h3 class="post-title">De leeslijst</h3>
                        <p class="post-meta">
                            <?= count($suggestions) ?> suggestie<?= count($suggestions) === 1 ? '' : 's' ?>.
                        </p>
                    </header>

                    <div class="post-description">
                        <?php if ($dbError !== null): ?>
                            <div class="alert alert-error"><?= e($dbError) ?></div>
                        <?php elseif (empty($suggestions)): ?>
                            <p>Nog geen suggesties. Wees de eerste!</p>
                        <?php else: ?>
                            <table class="pure-table pure-table-horizontal suggest-table">
                                <thead>
                                    <tr>
                                        <th>Titel</th>
                                        <th>Auteur</th>
                                        <th>Voorgedragen door</th>
                                        <th>Datum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($suggestions as $row): ?>
                                        <tr>
                                            <td>
                                                <?php if (!empty($row['url'])): ?>
                                                    <a href="<?= e($row['url']) ?>" target="_blank" rel="noopener noreferrer"><?= e($row['title']) ?></a>
                                                <?php else: ?>
                                                    <?= e($row['title']) ?>
                                                <?php endif; ?>
                                            </td>
                                            <td><?= e($row['author']) ?></td>
                                            <td><?= e($row['submitter_name']) ?></td>
                                            <td><?= e(format_date_nl((string) $row['created_at'])) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </section>
            </div>

            <footer></footer>
        </div>
    </div>
</body>
</html>
