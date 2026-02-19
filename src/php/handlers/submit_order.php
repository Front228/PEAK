<?php
session_start();
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// ПОЛНЫЙ ПУТЬ К CONFIG — проверь!
$root = $_SERVER['DOCUMENT_ROOT'];
require_once $root . '/includes/config.php';

// Проверка подключения
if (!isset($mysqli) || !$mysqli || $mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB not connected']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$errors = [];
$surname = trim($data['surname'] ?? '');
$name = trim($data['name'] ?? '');
$phone = trim($data['phone'] ?? '');
$email = trim($data['email'] ?? '');
$comment = trim($data['comment'] ?? '');

if (!preg_match('/^[\p{Cyrillic}\p{L} \-\']+$/u', $surname)) {
    $errors[] = 'Фамилия может содержать только буквы (кириллица/латиница), пробелы, дефисы и апострофы';
}
if (!preg_match('/^[\p{Cyrillic}\p{L} \-\']+$/u', $name)) {
    $errors[] = 'Имя может содержать только буквы (кириллица/латиница), пробелы, дефисы и апострофы';
}

// 2. Телефон — +7 или 8, 10 цифр
if (!preg_match('/^(\+7|8)[0-9]{10}$/', $phone)) {
    $errors[] = 'Номер телефона должен начинаться с +7 или 8 и содержать 10 цифр';
}

// 3. Email — стандартный валидатор + запрет кириллицы в домене
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Email должен быть в формате example@example.com';
} else {
    // Запрещаем кириллицу в доменной части
    $parts = explode('@', $email);
    if (count($parts) === 2 && preg_match('/[\x80-\xff]/', $parts[1])) {
        $errors[] = 'В домене email не должно быть кириллицы';
    }
}

// 4. Соглашения
if (empty($data['agreement1']) || empty($data['agreement2'])) {
    $errors[] = 'Примите условия соглашений';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'errors' => $errors]);
    exit;
}

$user_id = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;

$stmt = $mysqli->prepare("INSERT INTO orders (user_id, surname, name, phone, email, comment) VALUES (?, ?, ?, ?, ?, ?)");
if (!$stmt) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Prepare failed']);
    exit;
}

$stmt->bind_param("isssss", $user_id, $surname, $name, $phone, $email, $comment);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Insert failed: ' . $stmt->error]);
}
?>