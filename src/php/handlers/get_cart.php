<?php
session_start();
require_once '../../../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

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
    // Убедись, что section — допустимая строка
    $allowed = ['men', 'women', 'kids', 'gear', 'sales'];
    if (!in_array($row['section'], $allowed)) {
        error_log("Недопустимый section в БД: " . $row['section']);
        continue;
    }

    $filepath = $_SERVER['DOCUMENT_ROOT'] . "/data/{$row['section']}.json";
    if (!file_exists($filepath)) {
        error_log("Файл не найден: $filepath");
        continue;
    }

    $products = json_decode(file_get_contents($filepath), true);
    if (!is_array($products)) continue;

    foreach ($products as $p) {
        if ((int)$p['id'] === (int)$row['product_id']) {
            $cart[] = [
                'id' => $p['id'],
                'title' => $p['title'],
                'brand' => $p['brand'],
                'price' => $p['price'],
                'images' => $p['images'],
                'article' => $p['article'] ?? 'N/A',
                'section' => $row['section'],
                'quantity' => $row['quantity'],
                'size' => $row['size'],
                'selectedSize' => $row['size']
            ];
            break;
        }
    }
}

echo json_encode($cart);
?>