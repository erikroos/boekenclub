#!/usr/bin/env php
<?php
// CLI-script om een admin-gebruiker aan te maken of het wachtwoord te updaten.
// Gebruik: php create_admin.php <username>

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "Dit script mag alleen vanaf de commandline gebruikt worden.\n");
    exit(1);
}

require __DIR__ . '/includes/db.php';

$username = $argv[1] ?? null;
if ($username === null || $username === '') {
    fwrite(STDERR, "Gebruik: php create_admin.php <username>\n");
    exit(1);
}

$username = trim($username);
if (mb_strlen($username) < 2 || mb_strlen($username) > 50) {
    fwrite(STDERR, "Gebruikersnaam moet tussen 2 en 50 tekens zijn.\n");
    exit(1);
}

function read_hidden(string $prompt): string
{
    echo $prompt;
    // stty uitzetten zodat het wachtwoord niet op het scherm komt
    system('stty -echo');
    $line = fgets(STDIN);
    system('stty echo');
    echo "\n";
    return $line === false ? '' : rtrim($line, "\r\n");
}

$password = read_hidden("Wachtwoord voor '$username': ");
if (strlen($password) < 8) {
    fwrite(STDERR, "Wachtwoord moet minstens 8 tekens lang zijn.\n");
    exit(1);
}

$confirm = read_hidden('Bevestig wachtwoord: ');
if (!hash_equals($password, $confirm)) {
    fwrite(STDERR, "Wachtwoorden komen niet overeen.\n");
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
if ($hash === false) {
    fwrite(STDERR, "Kon wachtwoord niet hashen.\n");
    exit(1);
}

try {
    $pdo = get_db();
    // MySQL 8.0.20+ alias-syntax, ook correct op 9.x (VALUES() is deprecated).
    $stmt = $pdo->prepare(
        'INSERT INTO admins (username, password_hash)
         VALUES (:u, :p) AS new_row
         ON DUPLICATE KEY UPDATE password_hash = new_row.password_hash'
    );
    $stmt->execute([':u' => $username, ':p' => $hash]);
    echo "Admin '$username' opgeslagen.\n";
} catch (Throwable $ex) {
    fwrite(STDERR, 'Fout: ' . $ex->getMessage() . "\n");
    exit(1);
}
