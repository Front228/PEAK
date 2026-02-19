<?php
// ИСПРАВЛЕННЫЙ ФАЙЛ
require_once __DIR__ . '/../../includes/config.php'; // ✅
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Требуется авторизация']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$product_id = (int)($input['product_id'] ?? 0);
$section = trim($input['section'] ?? '');

// Валидация
$allowedSections = ['men', 'women', 'kids', 'gear', 'sales'];
if ($product_id <= 0 || !in_array($section, $allowedSections)) {
    echo json_encode(['error' => 'Неверные данные товара']);
    exit;
}

// Проверка существования товара
$jsonFile = __DIR__ . "/../../data/{$section}.json";
if (!file_exists($jsonFile)) {
    echo json_encode(['error' => 'Товар не найден']);
    exit;
}

$products = json_decode(file_get_contents($jsonFile), true);
if (!is_array($products)) {
    echo json_encode(['error' => 'Ошибка чтения товаров']);
    exit;
}

$productExists = false;
foreach ($products as $p) {
    if (isset($p['id']) && $p['id'] === $product_id) {
        $productExists = true;
        break;
    }
}
if (!$productExists) {
    echo json_encode(['error' => 'Товар не найден']);
    exit;
}

// Добавление в корзину
$stmt = $mysqli->prepare("INSERT INTO cart (user_id, product_id, section, quantity) VALUES (?, ?, ?, 1) ON DUPLICATE KEY UPDATE quantity = quantity + 1");
$stmt->bind_param("iis", $user_id, $product_id, $section);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['error' => 'Ошибка базы данных: ' . $mysqli->error]);
}

$stmt->close();
$mysqli->close();
?>