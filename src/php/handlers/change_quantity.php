<?php
session_start();
require_once '../../../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Не авторизован']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$product_id = (int)($_POST['product_id'] ?? 0);
$section = trim($_POST['section'] ?? '');
$size = trim($_POST['size'] ?? '');
$delta = (int)($_POST['delta'] ?? 0);

if (!$product_id || !$section || !$size) {
    echo json_encode(['success' => false, 'error' => 'Недостаточно данных']);
    exit;
}

// Обновляем только ту запись, где совпадает size
$stmt = $mysqli->prepare("
    UPDATE cart 
    SET quantity = GREATEST(1, quantity + ?)
    WHERE user_id = ? AND product_id = ? AND section = ? AND size = ?
");
$stmt->bind_param("iiiss", $delta, $user_id, $product_id, $section, $size);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Ошибка обновления']);
}
?>