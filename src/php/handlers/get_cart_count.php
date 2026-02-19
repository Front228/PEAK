<?php
session_start();

// Путь к config.php: из /src/php/handlers → вверх на 3 уровня → корень → /includes/config.php
require_once '../../../includes/config.php';

// Проверяем, что $mysqli создан в config.php
if (!isset($mysqli) || !$mysqli instanceof mysqli) {
    error_log("Ошибка: \$mysqli не определён в config.php");
    header('Content-Type: application/json');
    echo json_encode(['count' => 0]);
    exit;
}

// Авторизация
if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['count' => 0]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$count = (int)$stmt->get_result()->fetch_row()[0];

header('Content-Type: application/json');
echo json_encode(['count' => $count]);