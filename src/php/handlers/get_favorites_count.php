<?php
session_start();
require_once '../../../includes/config.php';

if (!isset($mysqli) || !$mysqli instanceof mysqli) {
    header('Content-Type: application/json');
    echo json_encode(['count' => 0]);
    exit;
}

if (!isset($_SESSION['user_id']) || !is_numeric($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['count' => 0]);
    exit;
}

$user_id = (int)$_SESSION['user_id'];
$stmt = $mysqli->prepare("SELECT COUNT(*) FROM favorites WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$count = (int)$stmt->get_result()->fetch_row()[0];

header('Content-Type: application/json');
echo json_encode(['count' => $count]);