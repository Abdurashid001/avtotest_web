<?php
session_start();
require 'db.php';

$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (!empty($full_name) && !empty($phone) && !empty($password) && !empty($password_confirm)) {
        if ($password !== $password_confirm) {
            $error = 'Parollar mos kelmadi!';
        } else {
            // Check if phone already exists
            $stmt = $db->prepare('SELECT id FROM users WHERE phone = ?');
            $stmt->execute([$phone]);
            if ($stmt->fetch()) {
                $error = 'Bu telefon raqam allaqachon ro\'yxatdan o\'tgan!';
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare('INSERT INTO users (full_name, phone, password) VALUES (?, ?, ?)');
                if ($stmt->execute([$full_name, $phone, $hashed_password])) {
                    $success = "Ro'yxatdan muvaffaqiyatli o'tdingiz. Tizimga kirishingiz mumkin.";
                } else {
                    $error = "Xatolik yuz berdi. Iltimos qayta urinib ko'ring.";
                }
            }
        }
    } else {
        $error = 'Iltimos, barcha maydonlarni to\'ldiring!';
    }
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ro'yxatdan o'tish - E-AVTOMAKTAB</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

    <div class="auth-container">
        <div class="auth-glass">
            <h1 class="auth-title">Ro'yxatdan o'tish</h1>
            <p class="auth-subtitle">Yangi hisob yaratish uchun ma'lumotlarni kiriting</p>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo htmlspecialchars($success); ?> <a href="login.php">Kirish</a>
                </div>
            <?php else: ?>
                <form action="register.php" method="POST" class="auth-form">
                    <div class="form-group">
                        <label for="full_name">F.I.SH</label>
                        <input type="text" name="full_name" id="full_name" placeholder="To'liq ismingiz" required value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Telefon raqam</label>
                        <input type="text" name="phone" id="phone" placeholder="+998 90 123 45 67" required value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Parol</label>
                        <input type="password" name="password" id="password" placeholder="Parol yarating" required>
                    </div>

                    <div class="form-group">
                        <label for="password_confirm">Parolni tasdiqlash</label>
                        <input type="password" name="password_confirm" id="password_confirm" placeholder="Parolni qayta kiriting" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">Ro'yxatdan o'tish</button>
                </form>
            <?php endif; ?>

            <div class="auth-footer">
                <p>Hisobingiz bormi? <a href="login.php">Tizimga kirish</a></p>
                <p><a href="index.php">Bosh sahifaga qaytish</a></p>
            </div>
        </div>
    </div>

</body>
</html>
