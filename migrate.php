<?php
require 'db.php';

try {
    // Drop user_answers first
    $db->exec("DROP TABLE IF EXISTS user_answers");
    // Drop results
    $db->exec("DROP TABLE IF EXISTS results");
    // Drop questions table to recreate with new option_d
    $db->exec("DROP TABLE IF EXISTS questions");
    
    echo "Eski jadvallar tozalandi.\n";
    
    // Now just re-run db.php code essentially by creating them with option_d
    $db->exec("CREATE TABLE IF NOT EXISTS questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question_text TEXT NOT NULL,
        image_url VARCHAR(255) NULL,
        option_a VARCHAR(255) NOT NULL,
        option_b VARCHAR(255) NOT NULL,
        option_c VARCHAR(255) NOT NULL,
        option_d VARCHAR(255) NOT NULL,
        correct_option ENUM('a', 'b', 'c', 'd') NOT NULL,
        explanation TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS results (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        total_questions INT NOT NULL,
        correct_answers INT NOT NULL,
        score_percent DECIMAL(5,2) NOT NULL,
        taken_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");
    
    $db->exec("CREATE TABLE IF NOT EXISTS user_answers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        result_id INT NOT NULL,
        question_id INT NOT NULL,
        user_answer ENUM('a', 'b', 'c', 'd') NOT NULL,
        is_correct BOOLEAN NOT NULL,
        FOREIGN KEY (result_id) REFERENCES results(id) ON DELETE CASCADE,
        FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
    )");

    echo "Yangi jadvallar yaratildi!\n";
} catch (PDOException $e) {
    die("Xatolik: " . $e->getMessage());
}
?>
