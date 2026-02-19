<?php
require_once __DIR__ . '/../../includes/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

$user_id = $_SESSION['user_id'];
$product_id = intval($_POST['product_id'] ?? 0);
$section = trim($_POST['section'] ?? '');

$allowedSections = ['men', 'women', 'kids', 'gear', 'sales'];
if ($product_id <= 0 || !in_array($section, $allowedSections)) {
    echo json_encode(['error' => 'Неверные данные']);
    exit;
}

$stmt = $mysqli->prepare("DELETE FROM cart WHERE user_id = ? AND product_id = ? AND section = ?");
$stmt->bind_param("iis", $user_id, $product_id, $section);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Ошибка удаления']);
}

$stmt->close();
$mysqli->close();
?>