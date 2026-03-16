<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Variant logic
$variant_id = isset($_GET['variant']) ? (int)$_GET['variant'] : 1;
if ($variant_id < 1) $variant_id = 1;

$limit_questions = 20;
$offset = ($variant_id - 1) * $limit_questions;

$stmt = $db->prepare("SELECT id, question_text, image_url, option_a, option_b, option_c, option_d FROM questions ORDER BY id ASC LIMIT ? OFFSET ?");
$stmt->bindValue(1, $limit_questions, PDO::PARAM_INT);
$stmt->bindValue(2, $offset, PDO::PARAM_INT);
$stmt->execute();
$questions = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($questions) == 0) {
    die("<div style='text-align:center; padding:50px; font-family:sans-serif;'><h2>Hozircha bazada savollar yo'q.</h2><a href='dashboard.php'>Ortga qaytish</a></div>");
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Ishlash - AVTOMAKTAB</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body style="background:var(--bg-light);">

    <div style="text-align:center; margin-top:30px;">
        <a href="dashboard.php" class="btn btn-outline" style="border-radius:50px;">Bosh sahifaga qaytish</a>
    </div>

    <form action="submit_test.php" method="POST" class="test-container" id="testForm">
        <h2 style="text-align:center; margin-bottom: 30px; color:var(--primary-color);">YHQ Onlayn Test - Variant <?php echo $variant_id; ?></h2>
        
        <input type="hidden" name="variant_id" value="<?php echo $variant_id; ?>">

        <?php foreach ($questions as $index => $q): ?>
            <div class="question-block">
                <div class="question-text">
                    <?php echo ($index + 1) . ". " . nl2br(htmlspecialchars($q['question_text'])); ?>
                </div>
                
                <?php if (!empty($q['image_url'])): ?>
                    <div style="margin-bottom: 20px; text-align: center;">
                        <img src="<?php echo htmlspecialchars($q['image_url']); ?>" alt="Savol rasmi" style="max-width: 100%; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                    </div>
                <?php endif; ?>
                
                <!-- We pass the question ID secretly but require an answer -->
                <input type="hidden" name="question_ids[]" value="<?php echo $q['id']; ?>">

                <div class="options-grid">
                    <label class="option-label">
                        <input type="radio" name="answer_<?php echo $q['id']; ?>" value="a" required>
                        <span class="option-text"><?php echo htmlspecialchars($q['option_a']); ?></span>
                    </label>
                    <label class="option-label">
                        <input type="radio" name="answer_<?php echo $q['id']; ?>" value="b" required>
                        <span class="option-text"><?php echo htmlspecialchars($q['option_b']); ?></span>
                    </label>
                    <label class="option-label">
                        <input type="radio" name="answer_<?php echo $q['id']; ?>" value="c" required>
                        <span class="option-text"><?php echo htmlspecialchars($q['option_c']); ?></span>
                    </label>
                    <label class="option-label">
                        <input type="radio" name="answer_<?php echo $q['id']; ?>" value="d" required>
                        <span class="option-text"><?php echo htmlspecialchars($q['option_d']); ?></span>
                    </label>
                </div>
            </div>
        <?php endforeach; ?>

        <div style="text-align:center; margin-top: 40px; padding-top:20px; border-top:2px solid #f1f5f9;">
            <button type="submit" class="btn btn-primary btn-lg" style="width:100%; max-width:400px; border-radius:12px;">Testni Yakunlash qismi baholash</button>
        </div>
    </form>

</body>
</html>
