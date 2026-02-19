<?php
session_start();
require_once '../../../includes/config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode([]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];

$stmt = $mysqli->prepare("SELECT product_id, section FROM favorites WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$result = [];
foreach ($items as $item) {
    $section = $item['section'];
    $id = (int)$item['product_id'];

    $path = $_SERVER['DOCUMENT_ROOT'] . "/data/{$section}.json";
    if (!file_exists($path)) continue;

    $data = json_decode(file_get_contents($path), true);
    if (!is_array($data)) continue;

    foreach ($data as $prod) {
        if (isset($prod['id']) && (int)$prod['id'] === $id) {
            $prod['section'] = $section;
            $result[] = $prod;
            break;
        }
    }
}

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);