<?php
declare(strict_types=1);

/**
 * Levert een gedeelde PDO-verbinding naar de boekenclub-database.
 * Gooit een PDOException als de verbinding mislukt.
 */
function get_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $configPath = __DIR__ . '/config.php';
    if (!is_file($configPath)) {
        throw new RuntimeException(
            'Config ontbreekt: kopieer includes/config.example.php naar includes/config.php.'
        );
    }
    $config = require $configPath;

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=%s',
        $config['db_host'],
        $config['db_port'] ?? '3306',
        $config['db_name'],
        $config['db_charset'] ?? 'utf8mb4'
    );

    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $pdo;
}

/**
 * Kleine helper voor veilige HTML-output.
 */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Formatteert een datum/tijd-string als "5 februari 2026" (Nederlandse maandnaam).
 */
function format_date_nl(string $datetime): string
{
    static $months = [
        'januari', 'februari', 'maart', 'april', 'mei', 'juni',
        'juli', 'augustus', 'september', 'oktober', 'november', 'december',
    ];
    $ts = strtotime($datetime);
    if ($ts === false) {
        return '';
    }
    return (int) date('j', $ts) . ' ' . $months[(int) date('n', $ts) - 1] . ' ' . date('Y', $ts);
}
