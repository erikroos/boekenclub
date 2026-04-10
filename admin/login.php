<?php
declare(strict_types=1);

require __DIR__ . '/../includes/auth.php';

ensure_session();

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!check_admin_csrf($_POST['csrf_token'] ?? null)) {
        $error = 'Sessie verlopen, probeer opnieuw.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $error = 'Vul beide velden in.';
        } elseif (attempt_login($username, $password)) {
            header('Location: index.php');
            exit;
        } else {
            $error = 'Onjuiste gebruikersnaam of wachtwoord.';
            // kleine vertraging om brute force te ontmoedigen
            usleep(300000);
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
    <title>Admin login — HBO-ICT Boekenclub</title>
</head>
<body>
    <div class="admin-wrap">
        <h1>Admin login</h1>

        <?php if ($error !== null): ?>
            <div class="alert alert-error"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" class="pure-form pure-form-stacked admin-form">
            <input type="hidden" name="csrf_token" value="<?= e(admin_csrf_token()) ?>">

            <label for="username">Gebruikersnaam</label>
            <input type="text" id="username" name="username" required autocomplete="username" autofocus>

            <label for="password">Wachtwoord</label>
            <input type="password" id="password" name="password" required autocomplete="current-password">

            <button type="submit" class="toggle-btn">Inloggen</button>
        </form>

        <p class="admin-meta"><a href="../index.php">&larr; Terug naar de site</a></p>
    </div>
</body>
</html>
