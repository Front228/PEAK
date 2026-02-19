<?php
session_start();
require_once '../../../includes/config.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    // Валидация логина
    if (empty($username)) {
        $errors['username'] = "Логин обязателен.";
    } elseif (strlen($username) < 3 || strlen($username) > 50) {
        $errors['username'] = "Логин должен быть от 3 до 50 символов.";
    }

    // Валидация email
    if (empty($email)) {
        $errors['email'] = "Email обязателен.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Некорректный email.";
    }

    // Валидация телефона (простая проверка на цифры и длину)
    if (empty($phone)) {
        $errors['phone'] = "Телефон обязателен.";
    } elseif (!preg_match('/^\+?\d{10,15}$/', $phone)) {
        $errors['phone'] = "Некорректный формат телефона (пример: +79991234567).";
    }

    // Валидация пароля
    if (empty($password)) {
        $errors['password'] = "Пароль обязателен.";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Пароль должен быть не менее 6 символов.";
    } elseif ($password !== $password_confirm) {
        $errors['password_confirm'] = "Пароли не совпадают.";
    }

    // Проверка уникальности email и логина
    if (empty($errors)) {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->bind_param("ss", $email, $username);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            // Можно уточнить, что именно занято
            $row = $mysqli->query("SELECT username, email FROM users WHERE email = '$email' OR username = '$username'")->fetch_assoc();
            if ($row && $row['email'] === $email) {
                $errors['email'] = "Email уже зарегистрирован.";
            }
            if ($row && $row['username'] === $username) {
                $errors['username'] = "Логин уже занят.";
            }
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare("INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $username, $email, $phone, $hashed_password);
        if ($stmt->execute()) {
            $success = true;
            $_SESSION['message'] = "Регистрация прошла успешно!";
            header("Location: login.php");
            exit();
        } else {
            $errors['general'] = "Ошибка при регистрации. Попробуйте позже.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Регистрация</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/public/icon/icon32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/public/icon/icon16x16.png">
</head>
<body>
    <div class="btn-home">
        <a href="../../../index.php">
            <img src="../../../public/icon/left-arrow.png" alt="Главная" class="home" >
        </a>
    </div>
    <div class="main_block-register">
        <div class="image_block">
            <img src="../../../public/image/1303124c-ecf3-47e1-8d68-40392d0bb87a.jpg" alt="" class="image">
        </div>
        <div class="block-register">
            <h2 class="h2_reg">Регистрация</h2>
            <?php if ($success): ?>

                <p class="success">Регистрация прошла успешно! <a href="login.php">Войти!</a></p>
            <?php else: ?>
                <form method="POST">
                    <!-- Логин -->
                    <div class="input-group">
                        <input type="text" name="username" id="username" placeholder="" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" class="<?= !empty($errors['username']) ? 'input-error' : '' ?>">
                        <label for="username">Логин</label>
                        <?php if (!empty($errors['username'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['username']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Email -->
                    <div class="input-group">
                        <input type="email" name="email" id="email" placeholder="" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" class="<?= !empty($errors['email']) ? 'input-error' : '' ?>">
                        <label for="email">Email</label>
                        <?php if (!empty($errors['email'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Телефон -->
                    <div class="input-group">
                        <input type="text" name="phone" id="phone" placeholder=""  value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>" class="<?= !empty($errors['phone']) ? 'input-error' : '' ?>">
                        <label for="phone">Телефон</label>
                        <?php if (!empty($errors['phone'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['phone']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Пароль -->
                    <div class="input-group">
                        <input type="password" name="password" id="password" placeholder="" class="<?= !empty($errors['password']) ? 'input-error' : '' ?>">
                        <label for="password">Пароль</label>
                        <?php if (!empty($errors['password'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Подтверждение пароля -->
                    <div class="input-group">
                        <input type="password" name="password_confirm" id="password_confirm" placeholder="" class="<?= !empty($errors['password_confirm']) ? 'input-error' : '' ?>">
                        <label for="password_confirm">Повторите пароль</label>
                        <?php if (!empty($errors['password_confirm'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['password_confirm']) ?></div>
                        <?php endif; ?>
                    </div>

                    <button class="btn-reg" type="submit">Зарегистрироваться</button>
                    </form>
                <a href="login.php">Уже есть аккаунт? Войти</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>