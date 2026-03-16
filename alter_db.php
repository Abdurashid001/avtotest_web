<?php
require 'db.php';
try {
    // Add explanation column
    $db->exec("ALTER TABLE questions ADD COLUMN explanation TEXT NULL AFTER correct_option");
    
    // Create user answers detail table
    $db->exec("CREATE TABLE IF NOT EXISTS user_answers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        result_id INT NOT NULL,
        question_id INT NOT NULL,
        user_answer ENUM('a', 'b', 'c', 'd') NOT NULL,
        is_correct BOOLEAN NOT NULL,
        FOREIGN KEY (result_id) REFERENCES results(id) ON DELETE CASCADE,
        FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
    )");

    echo "Baza yangilandi!";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Izoh ustuni allaqachon qo'shilgan, jadvallar mavjud.";
    } else {
        echo "Xatolik: " . $e->getMessage();
    }
}
?>
