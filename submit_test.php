<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['question_ids'])) {
    $user_id = $_SESSION['user_id'];
    $question_ids = $_POST['question_ids'];
    $total_questions = count($question_ids);
    $correct_answers = 0;
    
    $user_answers_data = [];
    $detailed_results = [];

    if ($total_questions > 0) {
        $stmt = $db->prepare('SELECT id, question_text, image_url, option_a, option_b, option_c, option_d, correct_option, explanation FROM questions WHERE id = ?');

        foreach ($question_ids as $q_id) {
            $user_answer = $_POST['answer_' . $q_id] ?? '';
            
            $stmt->execute([$q_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $is_correct = ($row['correct_option'] === $user_answer);
                if ($is_correct) {
                    $correct_answers++;
                }
                
                $user_answers_data[] = [
                    'q_id' => $q_id,
                    'user_answer' => $user_answer,
                    'is_correct' => $is_correct ? 1 : 0
                ];
                
                $row['user_answer'] = $user_answer;
                $row['is_correct'] = $is_correct;
                $detailed_results[] = $row;
            }
        }

        $score_percent = ($correct_answers / $total_questions) * 100;
        
        $variant_id = isset($_POST['variant_id']) ? (int)$_POST['variant_id'] : null;

        // Save result
        $insertStmt = $db->prepare('INSERT INTO results (user_id, variant_id, total_questions, correct_answers, score_percent) VALUES (?, ?, ?, ?, ?)');
        $insertStmt->execute([$user_id, $variant_id, $total_questions, $correct_answers, $score_percent]);
        $result_id = $db->lastInsertId();
        
        // Save detailed user answers history
        $ansStmt = $db->prepare('INSERT INTO user_answers (result_id, question_id, user_answer, is_correct) VALUES (?, ?, ?, ?)');
        foreach ($user_answers_data as $ans) {
            if ($ans['user_answer'] !== '') {
                $ansStmt->execute([$result_id, $ans['q_id'], $ans['user_answer'], $ans['is_correct']]);
            }
        }

        ?>
        <!DOCTYPE html>
        <html lang="uz">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Batafsil Natijalar</title>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
            <link rel="stylesheet" href="style.css">
            <style>
                .result-block {
                    background: white; border-radius: 16px; padding: 25px; margin-bottom: 25px; 
                    box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 5px solid #e2e8f0;
                }
                .result-correct { border-left-color: #10b981; }
                .result-wrong { border-left-color: #ef4444; }
                .q-text { font-size: 1.2rem; font-weight: 600; margin-bottom: 15px; }
                .option { padding: 10px 15px; border-radius: 8px; background: #f8fafc; margin-bottom: 8px; }
                .opt-correct { background: #d1fae5; font-weight: 600; border: 1px solid #10b981;}
                .opt-user-wrong { background: #fee2e2; font-weight: 600; border: 1px solid #ef4444; color: #b91c1c; text-decoration: line-through;}
                .explanation-box { background: #eff6ff; padding: 15px; border-radius: 12px; margin-top: 15px; border: 1px solid #bfdbfe; color: #1e3a8a;}
            </style>
        </head>
        <body style="background:var(--bg-light); padding: 40px 5%;">
            
            <div style="max-width: 800px; margin: 0 auto;">
                <div style="text-align:center; margin-bottom:40px;">
                    <div style="font-size: 4rem; margin-bottom:20px;"><?php echo $score_percent >= 70 ? '🎉' : '😔'; ?></div>
                    <h1 style="font-size: 2.5rem; color:<?php echo $score_percent>=70 ? '#10b981':'#ef4444'; ?>"><?php echo number_format($score_percent, 1); ?>%</h1>
                    <p style="font-size:1.1rem; color:var(--text-muted);">Siz <?php echo $total_questions; ?> ta savoldan <?php echo $correct_answers; ?> tasiga to'g'ri javob berdingiz.</p>
                    <a href="dashboard.php" class="btn btn-primary" style="margin-top:20px;">Profilga qaytish</a>
                </div>

                <h2 style="margin-bottom:20px;">Xato va To'g'ri javoblaringiz tahlili:</h2>

                <?php foreach ($detailed_results as $index => $q): 
                    $status_class = $q['is_correct'] ? 'result-correct' : 'result-wrong';
                ?>
                    <div class="result-block <?php echo $status_class; ?>">
                        <div class="q-text">
                            <?php echo ($index + 1) . ". " . nl2br(htmlspecialchars($q['question_text'])); ?>
                        </div>
                        
                        <?php if (!empty($q['image_url'])): ?>
                            <div style="margin-bottom: 15px;">
                                <img src="<?php echo htmlspecialchars($q['image_url']); ?>" alt="Savol rasmi" style="max-height: 200px; border-radius: 8px;">
                            </div>
                        <?php endif; ?>

                        <?php
                            $options = ['a', 'b', 'c', 'd'];
                            foreach ($options as $opt) {
                                $class = 'option';
                                $label = '';
                                
                                // if this option is the correct one
                                if ($q['correct_option'] === $opt) {
                                    $class .= ' opt-correct';
                                    $label = ' (To\'g\'ri javob ✓)';
                                } 
                                // if user chose this but it's wrong
                                elseif ($q['user_answer'] === $opt && !$q['is_correct']) {
                                    $class .= ' opt-user-wrong';
                                    $label = ' (Sizning xato javobingiz ✗)';
                                }
                                
                                echo "<div class='$class'><b>" . strtoupper($opt) . ".</b> " . htmlspecialchars($q['option_' . $opt]) . $label . "</div>";
                            }
                        ?>

                        <?php if (!empty($q['explanation'])): ?>
                            <div class="explanation-box">
                                <strong>💡 Izoh:</strong><br>
                                <?php echo nl2br(htmlspecialchars($q['explanation'])); ?>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>

                <div style="text-align:center; margin-top: 40px;">
                    <a href="dashboard.php" class="btn btn-primary btn-lg">Profilga qaytish</a>
                </div>
            </div>

        </body>
        </html>
        <?php
        exit();
    }
}

header("Location: dashboard.php");
exit();
?>
