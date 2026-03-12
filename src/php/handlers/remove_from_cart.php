<?php
session_start();
require_once '../../../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

$product_id = (int)($_POST['product_id'] ?? 0);
$section = trim($_POST['section'] ?? '');
$size = trim($_POST['size'] ?? '');

if (!$product_id || !$section || !$size) {
    echo json_encode(['error' => 'Недостаточно данных']);
    exit;
}

$stmt = $mysqli->prepare("
    DELETE FROM cart 
    WHERE user_id = ? AND product_id = ? AND section = ? AND size = ?
");
// 4 параметра → 4 типа: i (user_id), i (product_id), s (section), s (size)
$stmt->bind_param("iiss", $_SESSION['user_id'], $product_id, $section, $size);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Ошибка удаления: ' . $stmt->error]);
}
?>