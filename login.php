<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST['phone'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!empty($phone) && !empty($password)) {
        $stmt = $db->prepare('SELECT id, full_name, password, role FROM users WHERE phone = ?');
        $stmt->execute([$phone]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: admin.php");
            } else {
                header("Location: dashboard.php");
            }
            exit();
        } else {
            $error = 'Telefon raqam yoki parol noto\'g\'ri!';
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
    <title>Tizimga kirish - AVTOMAKTAB</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">

    <div class="auth-container">
        <div class="auth-glass">
            <h1 class="auth-title">Xush kelibsiz</h1>
            <p class="auth-subtitle">Tizimga kirish uchun ma'lumotlaringizni kiriting</p>

            <?php if ($error): ?>
                <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="phone">Telefon raqam</label>
                    <input type="text" name="phone" id="phone" placeholder="+998 90 123 45 67" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Parol</label>
                    <input type="password" name="password" id="password" placeholder="Parolingiz" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Kirish</button>
            </form>

            <div class="auth-footer">
                <p>Hisobingiz yo'qmi? <a href="register.php">Ro'yxatdan o'tish</a></p>
                <p><a href="index.php">Bosh sahifaga qaytish</a></p>
            </div>
        </div>
    </div>

</body>
</html>
