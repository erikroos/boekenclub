<?php
declare(strict_types=1);

require __DIR__ . '/../includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Method not allowed');
}

if (!check_admin_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(400);
    exit('CSRF check mislukt.');
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: index.php');
    exit;
}

try {
    $stmt = get_db()->prepare('DELETE FROM reviews WHERE id = ?');
    $stmt->execute([$id]);
} catch (Throwable $ex) {
    error_log('admin/delete: ' . $ex->getMessage());
}

header('Location: index.php?deleted=1');
exit;
