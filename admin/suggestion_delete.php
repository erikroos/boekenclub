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
    header('Location: suggestions.php');
    exit;
}

try {
    $stmt = get_db()->prepare('DELETE FROM book_suggestions WHERE id = ?');
    $stmt->execute([$id]);
} catch (Throwable $ex) {
    error_log('admin/suggestion_delete: ' . $ex->getMessage());
}

header('Location: suggestions.php?deleted=1');
exit;
