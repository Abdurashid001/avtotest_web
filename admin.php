<?php
session_start();
require 'db.php';

// Check if admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$tab = $_GET['tab'] ?? 'dashboard';
$action_msg = '';

// Handle Add Question
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'add_question') {
    $q_text = trim($_POST['question_text'] ?? '');
    $opt_a = trim($_POST['option_a'] ?? '');
    $opt_b = trim($_POST['option_b'] ?? '');
    $opt_c = trim($_POST['option_c'] ?? '');
    $opt_d = trim($_POST['option_d'] ?? '');
    $correct = $_POST['correct_option'] ?? '';
    $explanation = trim($_POST['explanation'] ?? '');
    
    $image_url = null;
    
    // Rasm yuklash
    if (isset($_FILES['question_image']) && $_FILES['question_image']['error'] == UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        $filename = time() . '_' . basename($_FILES['question_image']['name']);
        $target_path = $upload_dir . $filename;
        if (move_uploaded_file($_FILES['question_image']['tmp_name'], $target_path)) {
            $image_url = $target_path;
        }
    }

    if ($q_text && $opt_a && $opt_b && $opt_c && $opt_d && $correct) {
        $stmt = $db->prepare('INSERT INTO questions (question_text, image_url, option_a, option_b, option_c, option_d, correct_option, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        if ($stmt->execute([$q_text, $image_url, $opt_a, $opt_b, $opt_c, $opt_d, $correct, $explanation])) {
            $action_msg = "<div class='alert alert-success'>Savol muvaffaqiyatli qo'shildi!</div>";
        } else {
            $action_msg = "<div class='alert alert-danger'>Xatolik yuz berdi.</div>";
        }
    } else {
        $action_msg = "<div class='alert alert-danger'>Barcha maydonlarni to'ldiring!</div>";
    }
}

// Handle Bulk Upload via TXT or ZIP File
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] == 'bulk_upload') {
    if (isset($_FILES['bulk_file']) && $_FILES['bulk_file']['error'] == UPLOAD_ERR_OK) {
        
        $file_tmp = $_FILES['bulk_file']['tmp_name'];
        $file_ext = strtolower(pathinfo($_FILES['bulk_file']['name'], PATHINFO_EXTENSION));
        
        $content = '';
        $extracted_images = [];
        $upload_dir = 'uploads/';
        
        // ZIP fayl bo'lsa rasmlarni va txt faylni olish
        if ($file_ext === 'zip') {
            $zip = new ZipArchive;
            if ($zip->open($file_tmp) === TRUE) {
                // Vaqtinchalik papka
                $temp_dir = 'uploads/temp_' . time() . '/';
                mkdir($temp_dir, 0755, true);
                $zip->extractTo($temp_dir);
                $zip->close();
                
                // Read dir recursively to find inside folders like "test5/"
                $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($temp_dir));
                foreach ($iterator as $file) {
                    if ($file->isDir()) continue;
                    
                    $f_path = $file->getPathname();
                    $f_name = $file->getFilename();
                    
                    // Skip hidden files like .DS_Store or MacOS resource forks
                    if (strpos($f_name, '.') === 0 || strpos($f_path, '__MACOSX') !== false) continue;
                    
                    $ext = strtolower(pathinfo($f_name, PATHINFO_EXTENSION));
                    
                    // TXT faylni o'qish (birinchi uchraganini)
                    if ($ext === 'txt' && $content === '') {
                        $content = file_get_contents($f_path);
                    } 
                    // Rasmlarni doimiy uploads papkasiga ko'chirish
                    elseif (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $new_name = time() . '_' . rand(100,999) . '_' . $f_name;
                        $final_path = $upload_dir . $new_name;
                        rename($f_path, $final_path);
                        // Original nomini saqlab qolish txt bilan solishtirish uchun
                        $extracted_images[$f_name] = $final_path;
                    }
                }
                
                // Vaqtinchalik papkani tozalash (recursively delete)
                $dir_iterator = new RecursiveDirectoryIterator($temp_dir, RecursiveDirectoryIterator::SKIP_DOTS);
                $files_it = new RecursiveIteratorIterator($dir_iterator, RecursiveIteratorIterator::CHILD_FIRST);
                foreach($files_it as $file) {
                    chmod($file->getRealPath(), 0777); // Fix MacOS Permission Denied
                    if ($file->isDir()){
                        rmdir($file->getRealPath());
                    } else {
                        unlink($file->getRealPath());
                    }
                }
                rmdir($temp_dir);
                
                if ($content === '') {
                    $action_msg = "<div class='alert alert-danger'>ZIP ichidan matnli .txt fayl topilmadi! .txt fayl bo'lishi shart.</div>";
                    goto end_bulk; // Skip parsing
                }
                
            } else {
                $action_msg = "<div class='alert alert-danger'>ZIP faylni ochishda xatolik yuz berdi.</div>";
                goto end_bulk;
            }
        } 
        elseif ($file_ext === 'txt') {
            $content = file_get_contents($file_tmp);
        } else {
            $action_msg = "<div class='alert alert-danger'>Faqat .txt yoki .zip formatidagi fayllar qabul qilinadi.</div>";
            goto end_bulk;
        }
        
        $lines = explode("\n", str_replace("\r", "", $content));
        
        $success_count = 0;
        $failed_count = 0;
        $current_q = [];
        
        $stmt = $db->prepare('INSERT INTO questions (question_text, image_url, option_a, option_b, option_c, option_d, correct_option, explanation) VALUES (?, ?, ?, ?, ?, ?, ?, ?)');
        
        $insert = function() use (&$current_q, $stmt, &$success_count, &$failed_count, &$extracted_images) {
            $q_text = trim($current_q['q'] ?? '');
            $opt_a = trim($current_q['a'] ?? '');
            $opt_b = trim($current_q['b'] ?? '');
            $opt_c = trim($current_q['c'] ?? '');
            $opt_d = trim($current_q['d'] ?? '');
            $exp = trim($current_q['i'] ?? '');
            $img = trim($current_q['img'] ?? '');
            
            $final_img_url = null;
            if ($img !== '' && isset($extracted_images[$img])) {
                $final_img_url = $extracted_images[$img]; // Rasm URL manzili 
            }
            
            $ansRaw = strtolower(trim($current_q['j'] ?? ''));
            preg_match('/^[a-d]/i', $ansRaw, $ansMatch);
            $ans = $ansMatch[0] ?? '';
            
            if ($q_text !== '' && $opt_a !== '' && $opt_b !== '' && $ans !== '') {
                if ($opt_c === '') $opt_c = '-';
                if ($opt_d === '') $opt_d = '-';
                
                if ($stmt->execute([$q_text, $final_img_url, $opt_a, $opt_b, $opt_c, $opt_d, $ans, $exp])) {
                    $success_count++;
                } else {
                    $failed_count++;
                }
            } else {
                if ($q_text !== '' || $opt_a !== '') { 
                    $failed_count++;   
                }
            }
        };

        $state = 'q';
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '---') === 0) continue;
            
            // Rasmni aniqlash
            if (preg_match('/^(?:\d+[\.\-\)]?\s*)?(?:Rasm|Image|R|Rasmi)?\s*[\:\-\.]\s*(.*?\.(?:jpg|jpeg|png|gif|webp))/i', $line, $m)) {
                // Agar eski savol tugagan bo'lib, u yangi savolning rasmini birinchi yozgan bo'lsa:
                if (isset($current_q['a']) || isset($current_q['j'])) {
                    $insert();
                    $current_q = [];
                    $state = 'q';
                }
                $current_q['img'] = trim($m[1]);
            }
            // A, B, C, D variantlarini aniqlash
            elseif (preg_match('/^([A-D])\s*[\)\.\:\-]\s*(.*)/i', $line, $m)) {
                $letter = strtolower($m[1]);
                $current_q[$letter] = $m[2];
                $state = $letter;
            } 
            // Javobni aniqlash
            elseif (preg_match('/^(?:Javob|To\'g\'ri|To\'g\'ri javob|Answer)\s*[\:\-\.]\s*(.*)/i', $line, $m)) {
                $current_q['j'] = $m[1];
                $state = 'j';
            } 
            // Izohni aniqlash
            elseif (preg_match('/^(?:Izoh|Tushuntirish|Explanation)\s*[\:\-\.]\s*(.*)/i', $line, $m)) {
                $current_q['i'] = $m[1];
                $state = 'i';
            } 
            // Demak bu savolning matni bo'lishi mumkin!
            else {
                // Agar biz allaqachon javob yoki variantlar qabul qilgan bo'lsak, demak bu yangi savolning boshi!
                if (isset($current_q['a']) || isset($current_q['j'])) {
                    $insert(); // Oldingisini saqlaymiz
                    $clean_line = preg_replace('/^(?:\d+[\.\)]\s*)?(?:Savol\s*[\:\-\.]?\s*)?/i', '', $line);
                    // Tozalash! Muhim qism, rasmlar aralashib ketmasligi uchun
                    $current_q = ['q' => $clean_line];
                    $state = 'q';
                } else {
                    // Yoki bu hozirgi yozilayotgan savol/variantning davomi
                    if ($state === 'q') {
                        $clean_line = preg_replace('/^(?:\d+[\.\)]\s*)?(?:Savol\s*[\:\-\.]?\s*)?/i', '', $line);
                        $current_q['q'] = isset($current_q['q']) ? $current_q['q'] . "\n" . $clean_line : $clean_line;
                    } else {
                        $current_q[$state] .= "\n" . $line;
                    }
                }
            }
        }
        
        // Final insert
        if (!empty($current_q['q'])) {
            $insert();
            $current_q = []; // clear
        }
        
        $msg_type = $success_count > 0 ? 'alert-success' : 'alert-danger';
        $report = "Fayldan <b>$success_count ta</b> savol muvaffaqiyatli qo'shildi!";
        if ($failed_count > 0) {
            $report .= " Shuningdek, <b>$failed_count ta</b> savol tizim formatga mos kelmagani uchun qolib ketdi.";
        }
        
        if ($success_count == 0 && $content !== '') {
            $preview = htmlspecialchars(mb_substr($content, 0, 300));
            $report .= "<hr><p style='font-size:0.9rem'>Sizning faylingizdagi qatorlar quyidagicha boshlangan, shuning uchun tizim taniy olmagan:</p><pre style='background:rgba(255,255,255,0.5); padding:10px; border-radius:8px; font-size:0.8rem; white-space:pre-wrap; color:black;'>{$preview}</pre>";
        }
        
        $action_msg = "<div class='alert $msg_type'>$report</div>";
        
        end_bulk:
    } else {
        $action_msg = "<div class='alert alert-danger'>Fayl yuklashda xatolik! Fayl tanlaganingizga ishonch hosil qiling.</div>";
    }
}

// Handle Delete Question
if (isset($_GET['delete_q'])) {
    $del_id = (int)$_GET['delete_q'];
    $stmt = $db->prepare('DELETE FROM questions WHERE id = ?');
    $stmt->execute([$del_id]);
    header("Location: admin.php?tab=questions&deleted=1");
    exit();
}

if (isset($_GET['deleted']) && $_GET['deleted'] == 1) {
    $action_msg = "<div class='alert alert-success'>Savol o'chirildi!</div>";
}

?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - AVTOMAKTAB</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .sidebar { background: #1e1b4b; color: white; border-right: none; }
        .sidebar-logo { color: white; }
        .nav-item { color: #cbd5e1; }
        .nav-item:hover, .nav-item.active { background: #4f46e5; color: white; }
    </style>
</head>
<body>

<div class="dashboard-container">
    
    <!-- Sidebar -->
    <div class="sidebar">
        <a href="index.php" class="sidebar-logo">AVTOMAKTAB</a>
        <nav>
            <a href="admin.php?tab=dashboard" class="nav-item <?php echo $tab=='dashboard' ? 'active' : ''; ?>">📊 Bosh sahifa</a>
            <a href="admin.php?tab=questions" class="nav-item <?php echo $tab=='questions' ? 'active' : ''; ?>">📝 Savollar bazasi</a>
            <a href="admin.php?tab=users" class="nav-item <?php echo $tab=='users' ? 'active' : ''; ?>">👥 Foydalanuvchilar</a>
            <a href="logout.php" class="nav-item" style="margin-top: auto;">🚪 Chiqish</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header class="page-header">
            <h1 class="page-title">
                <?php 
                    if($tab == 'dashboard') echo "Admin Dashboard";
                    elseif($tab == 'questions') echo "Savollar Boshqaruvi";
                    elseif($tab == 'users') echo "Foydalanuvchilar";
                ?>
            </h1>
            <div class="user-profile">
                <span class="user-name" style="font-weight:600;"><?php echo htmlspecialchars($_SESSION['full_name']); ?></span>
                <div class="avatar">A</div>
            </div>
        </header>

        <?php echo $action_msg; ?>

        <?php if ($tab == 'dashboard'): 
            // Fetch stats
            $users_count = $db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
            $questions_count = $db->query("SELECT COUNT(*) FROM questions")->fetchColumn();
            $tests_count = $db->query("SELECT COUNT(*) FROM results")->fetchColumn();
        ?>
            <div class="stat-grid">
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>O'quvchilar</h4>
                        <p><?php echo $users_count; ?></p>
                    </div>
                    <div class="stat-icon">👨‍🎓</div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Jami Savollar</h4>
                        <p><?php echo $questions_count; ?></p>
                    </div>
                    <div class="stat-icon">📚</div>
                </div>
                <div class="stat-card">
                    <div class="stat-info">
                        <h4>Topshirilgan Testlar</h4>
                        <p><?php echo $tests_count; ?></p>
                    </div>
                    <div class="stat-icon">✅</div>
                </div>
            </div>

            <div class="card">
                <h3 class="card-title">So'nggi test natijalari</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>F.I.SH</th>
                                <th>Telefon</th>
                                <th>Natija</th>
                                <th>Foiz</th>
                                <th>Sana</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $db->query("SELECT u.full_name, u.phone, r.correct_answers, r.total_questions, r.score_percent, r.taken_at FROM results r JOIN users u ON r.user_id = u.id ORDER BY r.taken_at DESC LIMIT 5");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $badge_class = $row['score_percent'] >= 70 ? 'badge-success' : 'badge-danger';
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                                echo "<td>{$row['correct_answers']} / {$row['total_questions']}</td>";
                                echo "<td><span class='badge {$badge_class}'>{$row['score_percent']}%</span></td>";
                                echo "<td>" . date('d.m.Y H:i', strtotime($row['taken_at'])) . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($tab == 'questions'): ?>
            <div class="card">
                <h3 class="card-title">Yangi savol qo'shish</h3>
                <form action="admin.php?tab=questions" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_question">
                    <div class="form-group">
                        <label>Savol matni</label>
                        <textarea name="question_text" class="form-control" rows="3" required style="width:100%; border-radius:12px; border:1px solid #e2e8f0; padding:12px; font-family:inherit;"></textarea>
                    </div>
                    <div class="form-group">
                        <label>Rasm yuklash (ixtiyoriy, rasmli savollar yoki yo'l belgilari uchun)</label>
                        <input type="file" name="question_image" accept="image/*" style="width:100%; border-radius:12px; border:1px solid #e2e8f0; padding:10px; color: black; background: white;">
                    </div>
                    <div class="stat-grid" style="grid-template-columns: repeat(2, 1fr); gap:15px; margin-bottom:15px;">
                        <div class="form-group" style="margin:0;">
                            <label>A varianti</label>
                            <input type="text" name="option_a" required style="width:100%; border-radius:12px; border:1px solid #e2e8f0; padding:10px; color: black; background: white;">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>B varianti</label>
                            <input type="text" name="option_b" required style="width:100%; border-radius:12px; border:1px solid #e2e8f0; padding:10px; color: black; background: white;">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>C varianti</label>
                            <input type="text" name="option_c" required style="width:100%; border-radius:12px; border:1px solid #e2e8f0; padding:10px; color: black; background: white;">
                        </div>
                        <div class="form-group" style="margin:0;">
                            <label>D varianti</label>
                            <input type="text" name="option_d" required style="width:100%; border-radius:12px; border:1px solid #e2e8f0; padding:10px; color: black; background: white;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Qaysi biri to'g'ri javob?</label>
                        <select name="correct_option" required style="width:100%; border-radius:12px; border:1px solid #e2e8f0; padding:12px; font-family:inherit; background:white;">
                            <option value="">Tanlang...</option>
                            <option value="a">A varianti to'g'ri</option>
                            <option value="b">B varianti to'g'ri</option>
                            <option value="c">C varianti to'g'ri</option>
                            <option value="d">D varianti to'g'ri</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>To'g'ri javob uchun izoh (O'quvchi xato qilganda o'rganishi uchun yordam beradi)</label>
                        <textarea name="explanation" class="form-control" rows="2" style="width:100%; border-radius:12px; border:1px solid #e2e8f0; padding:12px; font-family:inherit;" placeholder="Tushuntirish matni (ixtiyoriy)"></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Saqlash</button>
                </form>
            </div>
            
            <div class="card">
                <h3 class="card-title">Foydalning (TXT yoki ZIP) Ommaviy Savol Qo'shish</h3>
                <div style="background:var(--bg-light); padding:20px; border-radius:12px; margin-bottom:20px; border:1px solid #e2e8f0;">
                    <strong>Qanday ishlaydi? (.zip):</strong>
                    <p style="font-size: 0.95rem; margin-top: 5px; color: #475569;">
                        Agar rasmli testlar bo'lsa barcha rasmlarni (jpg, png) va txt faylni битta <b>.zip</b> arxiviga solib yuklang.<br>
                        Txt fayl ichida rasmni bog'lash uchun shunday yozing: <code>Rasm: belgi1.jpg</code>
                    </p>
                    <pre style="margin-top:10px; color:#64748b; font-size:0.9rem; line-height:1.5;">
Savol: Rasmdagi belgi nimani anglatadi?
Rasm: belgi-1.jpg
A: Keng yo'lda
B: Chorrahada
C: Belgilanmagan joyda
D: Hech qachon
Javob: A
Izoh: Quvib o'tish keng yo'llarda ruxsat etiladi...
---
Savol: Keyingi savol (rasmsiz oddiy)
A: ...
B: ...</pre>
                </div>
                <form action="admin.php?tab=questions" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="bulk_upload">
                    <div class="form-group">
                        <label>Tayyorlangan .txt yoki .zip faylni yuklang</label>
                        <input type="file" name="bulk_file" accept=".txt,.zip" required style="width:100%; border-radius:12px; border:1px solid #e2e8f0; padding:10px; color: black; background: white;">
                    </div>
                    <button type="submit" class="btn btn-success" style="background:#10b981; color:white; border:none; padding:12px 24px; border-radius:12px; font-weight:600; cursor:pointer;">Ommaviy yuklash</button>
                </form>
            </div>

            <div class="card">
                <h3 class="card-title">Mavjud savollar listi</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>Savol matni</th>
                                <th>To'g'ri javob</th>
                                <th>Amal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $db->query("SELECT id, question_text, correct_option FROM questions ORDER BY id DESC");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>{$row['id']}</td>";
                                echo "<td>" . htmlspecialchars(substr($row['question_text'], 0, 50)) . "...</td>";
                                echo "<td><span class='badge badge-success' style='text-transform:uppercase;'>{$row['correct_option']}</span></td>";
                                echo "<td>
                                    <a href='admin.php?tab=questions&delete_q={$row['id']}' class='btn btn-outline' style='color:#ef4444; border-color:#ef4444; padding:6px 12px;' onclick=\"return confirm('Haqiqatan o\'chirmoqchimisiz?');\">O'chirish</a>
                                </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

        <?php elseif ($tab == 'users'): ?>
            <div class="card">
                <h3 class="card-title">Barcha Foydalanuvchilar</h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>#ID</th>
                                <th>F.I.SH</th>
                                <th>Telefon</th>
                                <th>Testlar soni</th>
                                <th>Ro'yxatdan o'tgan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $db->query("SELECT u.id, u.full_name, u.phone, u.created_at, (SELECT COUNT(*) FROM results r WHERE r.user_id = u.id) as tests_count FROM users u WHERE u.role = 'student' ORDER BY u.id DESC");
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                echo "<tr>";
                                echo "<td>{$row['id']}</td>";
                                echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                                echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                                echo "<td>{$row['tests_count']} ta test</td>";
                                echo "<td>" . date('d.m.Y', strtotime($row['created_at'])) . "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>
