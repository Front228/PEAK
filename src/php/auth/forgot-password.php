<?php
session_start();
require_once __DIR__ . '/../../../includes/config.php';
require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../includes/functions.php';

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $errors['email'] = "Email обязателен.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Некорректный email.";
    } else {
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            // Безопасность: не раскрываем существование
            $message = "Если этот email зарегистрирован, вы получите инструкции.";
        } else {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $update = $mysqli->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
            $update->bind_param("sss", $token, $expires, $email);
            $update->execute();
            $update->close();

            $reset_link = "http://$_SERVER[HTTP_HOST]/src/php/auth/reset_password.php?token=" . urlencode($token);
            header("Location: reset_password.php?token=" . urlencode($token));

            if (sendPasswordResetEmail($email, $reset_link)) {
                $message = "На ваш email отправлена ссылка для восстановления пароля.";
            } else {
                $message = "Ошибка при отправке письма. Попробуйте позже.";
            }
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
    <title>Забыли пароль?</title>
    <link rel="stylesheet" href="../../css/style.css">
    <link rel="stylesheet" href="../../css/auth.css">
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
            <h2 class="h2_reg">Восстановление пароля</h2>

            <?php if (!empty($message)): ?>
                <p class="success"><?= htmlspecialchars($message) ?></p>
                <a href="login.php" class="auth-link">← Вернуться ко входу</a>
            <?php else: ?>
                <form method="POST">
                    <div class="input-group">
                        <input type="email" name="email" id="email" placeholder=""
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            class="<?= !empty($errors['email']) ? 'input-error' : '' ?>">
                        <label for="email">Email</label>
                        <?php if (!empty($errors['email'])): ?>
                            <div class="error-message"><?= htmlspecialchars($errors['email']) ?></div>
                        <?php endif; ?>
                    </div>

                    <button class="btn-reg" type="submit">Отправить ссылку</button>
                </form>
                <a href="login.php" class="auth-link">← Назад к входу</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>