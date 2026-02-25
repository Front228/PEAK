<?php
session_start();
require_once __DIR__ . '/../../../includes/config.php';

$errors = [];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $errors['email'] = "Email обязателен.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Некорректный email.";
    }

    if (empty($password)) {
        $errors['password'] = "Пароль обязателен.";
    }

    // Если нет ошибок — проверяем учётные данные
    if (empty($errors)) {
        // Запрашиваем id, username, password И role
        $stmt = $mysqli->prepare("SELECT id, username, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($user = $result->fetch_assoc()) {
            if (password_verify($password, $user['password'])) {
                // Сохраняем данные в сессию
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_role'] = $user['role']; // ← КЛЮЧЕВАЯ СТРОКА

                // Редирект в зависимости от роли
                if ($user['role'] === 'admin') {
                    header("Location: ../../../admin/manage-products.php");
                } else {
                    header("Location: ../../../index.php");
                }
                exit;
            } else {
                $errors['password'] = "Неверный email или пароль.";
            }
        } else {
            $errors['email'] = "Неверный email или пароль.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Вход</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="icon" type="image/png" sizes="32x32" href="/public/icon/icon32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/public/icon/icon16x16.png">
</head>
<body>
    <div class="btn-home">
        <a href="../../../index.php">
            <img src="../../../public/icon/left-arrow.png" alt="Главная" class="home">
        </a>
    </div>

    <div class="main_block-register">
        <div class="image_block">
            <img src="../../../public/image/1303124c-ecf3-47e1-8d68-40392d0bb87a.jpg" alt="" class="image">
        </div>
        <div class="block-register">
            <h2 class="h2_reg">Вход</h2>

            <form method="POST">
                <!-- Email -->
                <div class="input-group">
                    <input type="email" name="email" id="email" placeholder="" 
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" 
                        class="<?= !empty($errors['email']) ? 'input-error' : '' ?>">
                    <label for="email">Email</label>
                    <?php if (!empty($errors['email'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <!-- Пароль -->
                <div class="input-group">
                    <input type="password" name="password" id="password" placeholder="" 
                        class="<?= !empty($errors['password']) ? 'input-error' : '' ?>">
                    <label for="password">Пароль</label>
                    <?php if (!empty($errors['password'])): ?>
                        <div class="error-message"><?= htmlspecialchars($errors['password']) ?></div>
                    <?php endif; ?>
                </div>

                <button class="btn-reg" type="submit">Войти</button>
            </form>

            <a href="forgot-password.php" class="auth-link">Забыли пароль?</a>
            <a href="register.php" class="auth-link">Нет аккаунта? Зарегистрироваться</a>
        </div>
    </div>
</body>
</html>