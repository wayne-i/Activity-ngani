<?php
require_once __DIR__ . '/bago.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($id && $id > 0) {
    $statement = $conn->prepare('DELETE FROM users WHERE id = ?');
    $statement->bind_param('i', $id);
    $statement->execute();
    $statement->close();

    $remainingUsers = $conn->query('SELECT COUNT(*) AS total FROM users')->fetch_assoc();
    if ((int) $remainingUsers['total'] === 0) {
        $conn->query('ALTER TABLE users AUTO_INCREMENT = 1');
    }
}

header('Location: index.php?msg=deleted');
exit;
