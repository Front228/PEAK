<?php
session_start();
require_once '../../../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

// Получаем товары из БД
$stmt = $mysqli->prepare("
    SELECT product_id, section, quantity, size 
    FROM cart 
    WHERE user_id = ?
");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$cart = [];
while ($row = $result->fetch_assoc()) {
    // Читаем товар из JSON
    $filepath = $_SERVER['DOCUMENT_ROOT'] . "/data/{$row['section']}.json";
    if (!file_exists($filepath)) continue;

    $products = json_decode(file_get_contents($filepath), true);
    if (!is_array($products)) continue;

    // Находим товар
    foreach ($products as $p) {
        if ((int)$p['id'] === (int)$row['product_id']) {
            // Создаём объект для корзины
            $cartItem = [
                'id' => $p['id'],
                'title' => $p['title'],
                'brand' => $p['brand'],
                'price' => $p['price'],
                'images' => $p['images'],
                'article' => $p['article'] ?? 'N/A',
                'section' => $row['section'],
                'quantity' => $row['quantity'],
                'size' => $row['size'], // ← ИСПОЛЬЗУЕМ РАЗМЕР ИЗ БД!
                'selectedSize' => $row['size'] // ← ДОБАВЛЕНО!
            ];
            $cart[] = $cartItem;
            break;
        }
    }
}

echo json_encode($cart);
?>