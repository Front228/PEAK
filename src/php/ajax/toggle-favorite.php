<?php
require_once '/../../includes/config.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Авторизуйтесь']);
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

// Проверка существования товара (аналогично cart)

// Проверяем, есть ли уже в избранном
$check = $mysqli->prepare("SELECT id FROM favorites WHERE user_id = ? AND product_id = ? AND section = ?");
$check->bind_param("iis", $user_id, $product_id, $section);
$check->execute();
$exists = $check->get_result()->num_rows > 0;
$check->close();

if ($exists) {
    // Удаляем
    $del = $mysqli->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ? AND section = ?");
    $del->bind_param("iis", $user_id, $product_id, $section);
    $del->execute();
    $del->close();
    echo json_encode(['success' => 'Удалено из избранного']);
} else {
    // Добавляем
    $ins = $mysqli->prepare("INSERT INTO favorites (user_id, product_id, section) VALUES (?, ?, ?)");
    $ins->bind_param("iis", $user_id, $product_id, $section);
    $ins->execute();
    $ins->close();
    echo json_encode(['success' => 'Добавлено в избранное']);
}

$mysqli->close();
?>