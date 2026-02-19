<?php
session_start();
require_once '../../../includes/config.php';

if (!isset($_SESSION['user_id'])) exit;

$user_id = (int)$_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];
$section = $_POST['section'];

$stmt = $mysqli->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ? AND section = ?");
$stmt->bind_param("iis", $user_id, $product_id, $section);
$stmt->execute();

echo json_encode(['success' => true]);