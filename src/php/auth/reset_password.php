<?php
session_start();
require_once __DIR__ . '/../../../includes/config.php';

$error = '';
$success = false;
$token = $_GET['token'] ?? '';

if (!$token) {
    die("Недействительная ссылка.");
}

$stmt = $mysqli->prepare("SELECT id, reset_expires FROM users WHERE reset_token = ?");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    die("Недействительная или просроченная ссылка.");
}

$expires = new DateTime($user['reset_expires']);
$now = new DateTime();

if ($now > $expires) {
    die("Ссылка для восстановления пароля устарела. Попробуйте снова.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($password) || empty($password_confirm)) {
        $errors['password'] = "Заполните оба поля пароля.";
    } elseif (strlen($password) < 6) {
        $errors['password'] = "Пароль должен быть не менее 6 символов.";
    } elseif ($password !== $password_confirm) {
        $errors['password_confirm'] = "Пароли не совпадают.";
    } else {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $update = $mysqli->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?");
        $update->bind_param("ss", $hashed, $token);
        if ($update->execute()) {
            $success = true;
        } else {
            $errors['general'] = "Ошибка при обновлении пароля.";
        }
        $update->close();
    }
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Новый пароль</title>
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
            <h2 class="h2_reg">Новый пароль</h2>

            <?php if ($success): ?>
                <p class="success">Пароль успешно изменён!</p>
                <a href="login.php" class="auth-link">← Войти</a>
            <?php else: ?>
                <form method="POST">
                    <!-- Новый пароль -->
                    <div class="input-group">
                        <input type="password" name="password" id="password" placeholder=""
                            class="<?= !empty($errors['password']) ? 'input-error' : '' ?>">
                        <label for="password">Новый пароль</label>
                        <?php if (!empty($errors['password'])): ?>
                            <div class="error-message"><?= htmlspecialchars($errors['password']) ?></div>
                        <?php endif; ?>
                    </div>

                    <!-- Подтверждение пароля -->
                    <div class="input-group">
                        <input type="password" name="password_confirm" id="password_confirm" placeholder=""
                            class="<?= !empty($errors['password_confirm']) ? 'input-error' : '' ?>">
                        <label for="password_confirm">Повторите пароль</label>
                        <?php if (!empty($errors['password_confirm'])): ?>
                            <div class="error-message"><?= htmlspecialchars($errors['password_confirm']) ?></div>
                        <?php endif; ?>
                    </div>

                    <button class="btn-reg" type="submit">Сохранить пароль</button>
                </form>
                <a href="login.php" class="auth-link">← Назад ко входу</a>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>