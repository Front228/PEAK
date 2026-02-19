<?php
// functions.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendPasswordResetEmail($to, $resetLink) {
    $mailConfig = require __DIR__ . '/mail.php';

    $mail = new PHPMailer(true);
    try {
        // Настройки SMTP
        $mail->isSMTP();
        $mail->Host       = $mailConfig['host'];
        $mail->SMTPAuth   = true;
        $mail->Username   = $mailConfig['username'];
        $mail->Password   = $mailConfig['password'];
        $mail->SMTPSecure = $mailConfig['secure'];
        $mail->Port       = $mailConfig['port'];

        // Отправитель и получатель
        $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
        $mail->addAddress($to);

        // Содержимое
        $mail->isHTML(false); // отправляем как текст
        $mail->Subject = 'Восстановление пароля';
        $mail->Body    = "Здравствуйте!\n\nВы запросили сброс пароля. Перейдите по ссылке, чтобы установить новый пароль:\n\n{$resetLink}\n\nСсылка действительна 1 час.\n\nЕсли вы не запрашивали сброс — проигнорируйте это письмо.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Ошибка отправки email: " . $mail->ErrorInfo);
        return false;
    }
}
?>