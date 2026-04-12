<?php
declare(strict_types=1);

require __DIR__ . '/../includes/auth.php';

ensure_session();

// Self-disable: deze pagina werkt alleen zolang er nog geen admin-account bestaat.
// Zodra er een admin is aangemaakt geeft-ie een nette melding en verder niets meer.
try {
    $adminCount = (int) get_db()->query('SELECT COUNT(*) FROM admins')->fetchColumn();
} catch (Throwable $ex) {
    error_log('admin/setup: ' . $ex->getMessage());
    http_response_code(500);
    exit('Database-fout. Controleer of sql/migration1.sql is uitgevoerd en of includes/config.php klopt.');
}

$errors  = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($adminCount > 0) {
        $errors[] = 'Er bestaat al een admin. Deze setup-pagina is uitgeschakeld.';
    } elseif (!check_admin_csrf($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Sessie verlopen, probeer opnieuw.';
    } else {
        $username = trim((string) ($_POST['username'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirm  = (string) ($_POST['password_confirm'] ?? '');

        if (mb_strlen($username) < 2 || mb_strlen($username) > 50) {
            $errors[] = 'Gebruikersnaam moet tussen 2 en 50 tekens zijn.';
        }
        if (preg_match('/[\x00-\x1F\x7F]/', $username)) {
            $errors[] = 'Ongeldige tekens in gebruikersnaam.';
        }
        if (strlen($password) < 8) {
            $errors[] = 'Wachtwoord moet minstens 8 tekens zijn.';
        }
        if (!hash_equals($password, $confirm)) {
            $errors[] = 'Wachtwoorden komen niet overeen.';
        }

        if (empty($errors)) {
            // Kleine race-condition safeguard: nog eens checken direct voor de INSERT
            $stillEmpty = ((int) get_db()->query('SELECT COUNT(*) FROM admins')->fetchColumn()) === 0;
            if (!$stillEmpty) {
                $errors[] = 'Er bestaat inmiddels al een admin. Ga naar de loginpagina.';
                $adminCount = 1;
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                try {
                    $stmt = get_db()->prepare('INSERT INTO admins (username, password_hash) VALUES (?, ?)');
                    $stmt->execute([$username, $hash]);
                    $success    = true;
                    $adminCount = 1;
                } catch (PDOException $ex) {
                    error_log('admin/setup insert: ' . $ex->getMessage());
                    $errors[] = 'Opslaan mislukt.';
                }
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
    <title>Admin setup — HBO-ICT Boekenclub</title>
    <meta name="robots" content="noindex, nofollow">
</head>
<body>
    <div class="admin-wrap">
        <h1>Admin setup</h1>

        <?php if ($adminCount > 0 && !$success): ?>
            <div class="alert alert-error">
                <strong>Setup is al voltooid.</strong>
                Er bestaat al een admin-account. Deze pagina is uitgeschakeld en kan
                veilig verwijderd worden (of blijft vanzelf inactief).
            </div>
            <p><a href="login.php">&rarr; Naar de loginpagina</a></p>
        <?php elseif ($success): ?>
            <div class="alert alert-success">
                <strong>Gelukt!</strong> Je admin-account is aangemaakt. Je kunt nu inloggen.
                Verwijder dit bestand (<code>admin/setup.php</code>) voor de zekerheid van de
                server — al is het ook zelf-uitgeschakeld nu er een admin bestaat.
            </div>
            <p><a href="login.php" class="toggle-btn">Inloggen</a></p>
        <?php else: ?>
            <p>
                Dit is een éénmalige setup-pagina. Maak hier je eerste admin-account aan.
                Zodra er een admin bestaat schakelt deze pagina zichzelf uit.
            </p>

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

            <form method="post" class="pure-form pure-form-stacked admin-form" autocomplete="off">
                <input type="hidden" name="csrf_token" value="<?= e(admin_csrf_token()) ?>">

                <label for="username">Gebruikersnaam</label>
                <input type="text" id="username" name="username" required minlength="2" maxlength="50"
                       value="<?= e((string) ($_POST['username'] ?? '')) ?>" autofocus>

                <label for="password">Wachtwoord <span class="label-optional">(minstens 8 tekens)</span></label>
                <input type="password" id="password" name="password" required minlength="8">

                <label for="password_confirm">Wachtwoord bevestigen</label>
                <input type="password" id="password_confirm" name="password_confirm" required minlength="8">

                <button type="submit" class="toggle-btn">Admin aanmaken</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
