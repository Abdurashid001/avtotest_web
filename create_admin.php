<?php
require 'db.php';

$full_name = 'Asosiy Admin';
$phone = '+998900000000';
$password = 'admin123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);
$role = 'admin';

// Check if user already exists
$stmt = $db->prepare('SELECT id FROM users WHERE phone = ?');
$stmt->execute([$phone]);
$user = $stmt->fetch();

if ($user) {
    // Update existing to admin
    $updateStmt = $db->prepare('UPDATE users SET role = ?, password = ? WHERE phone = ?');
    $updateStmt->execute([$role, $hashed_password, $phone]);
    echo "Mavjud foydalanuvchi adminga o'zgartirildi.\n";
} else {
    // Insert new admin
    $insertStmt = $db->prepare('INSERT INTO users (full_name, phone, password, role) VALUES (?, ?, ?, ?)');
    $insertStmt->execute([$full_name, $phone, $hashed_password, $role]);
    echo "Yangi admin yaratildi.\n";
}

echo "Telefon raqam: $phone\n";
echo "Parol: $password\n";
?>
