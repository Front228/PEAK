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
$quantity = max(1, (int)($_POST['quantity'] ?? 1));
$size = trim($_POST['size'] ?? 'N/A');

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

// === ИСПРАВЛЕНО: 5 параметров + проверка выполнения ===
$stmt = $mysqli->prepare("
    INSERT INTO cart (user_id, product_id, section, quantity, size)
    VALUES (?, ?, ?, ?, ?)
    ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
");

if (!$stmt) {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка подготовки запроса: ' . $mysqli->error]);
    exit;
}

$stmt->bind_param("iisis", $user_id, $product_id, $section, $quantity, $size);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка выполнения: ' . $stmt->error]);
}
?>