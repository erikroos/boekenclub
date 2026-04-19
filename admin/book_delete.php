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

$id  = (int) ($_POST['id'] ?? 0);
$tab = ($_POST['tab'] ?? 'read') === 'to_read' ? 'to_read' : 'read';

if ($id <= 0) {
    header('Location: books.php?tab=' . $tab);
    exit;
}

try {
    $stmt = get_db()->prepare('DELETE FROM personal_books WHERE id = ?');
    $stmt->execute([$id]);
} catch (Throwable $ex) {
    error_log('admin/book_delete: ' . $ex->getMessage());
}

header('Location: books.php?tab=' . $tab . '&deleted=1');
exit;
