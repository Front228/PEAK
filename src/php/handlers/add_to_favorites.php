<?php
session_start();
require_once '../../../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Авторизуйтесь']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$product_id = (int)($_POST['product_id'] ?? 0);
$section = trim($_POST['section'] ?? '');

$allowed = ['men', 'women', 'kids', 'gear', 'sales'];
if (!in_array($section, $allowed)) {
    echo json_encode(['error' => 'Неверная категория']);
    exit;
}

$filepath = $_SERVER['DOCUMENT_ROOT'] . "/data/{$section}.json";
if (!file_exists($filepath)) {
    echo json_encode(['error' => 'Категория не найдена']);
    exit;
}

$products = json_decode(file_get_contents($filepath), true);
if (!is_array($products)) {
    echo json_encode(['error' => 'Ошибка данных']);
    exit;
}

$found = false;
foreach ($products as $p) {
    if (isset($p['id']) && (int)$p['id'] === $product_id) {
        $found = true;
        break;
    }
}

if (!$found) {
    echo json_encode(['error' => 'Товар не найден']);
    exit;
}

$stmt = $mysqli->prepare("
    INSERT INTO favorites (user_id, product_id, section)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE added_at = CURRENT_TIMESTAMP
");
$stmt->bind_param("iis", $user_id, $product_id, $section);
$stmt->execute();

echo json_encode(['success' => true]);
