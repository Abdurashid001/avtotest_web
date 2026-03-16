<?php
session_start();
require 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>O'quvchi Paneli - AVTOMAKTAB</title>
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
            <a href="dashboard.php" class="nav-item active">📊 Mening panellarim</a>
            <a href="logout.php" class="nav-item" style="margin-top: auto;">🚪 Chiqish</a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <header class="page-header">
            <h1 class="page-title">Xush kelibsiz, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
            <div class="user-profile">
                <span class="user-name" style="font-weight:600;">O'quvchi</span>
                <div class="avatar" style="background:#10b981;">S</div>
            </div>
        </header>

        <?php
        // Fetch stats
        $tests_count = $db->query("SELECT COUNT(*) FROM results WHERE user_id = $user_id")->fetchColumn();
        $avg_score = $db->query("SELECT AVG(score_percent) FROM results WHERE user_id = $user_id")->fetchColumn() ?? 0;
        $max_score = $db->query("SELECT MAX(score_percent) FROM results WHERE user_id = $user_id")->fetchColumn() ?? 0;
        
        $total_all_questions = $db->query("SELECT COUNT(*) FROM questions")->fetchColumn();
        $total_variants = ceil($total_all_questions / 20);
        ?>

        <div class="stat-grid">
            <div class="stat-card">
                <div class="stat-info">
                    <h4>Topshirilgan Testlar</h4>
                    <p><?php echo $tests_count; ?></p>
                </div>
                <div class="stat-icon">📝</div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h4>O'rtacha Natija</h4>
                    <p><?php echo number_format($avg_score, 1); ?>%</p>
                </div>
                <div class="stat-icon" style="color:#f59e0b; background:rgba(245, 158, 11, 0.1);">📊</div>
            </div>
            <div class="stat-card">
                <div class="stat-info">
                    <h4>Maksimal Natija</h4>
                    <p><?php echo number_format($max_score, 1); ?>%</p>
                </div>
                <div class="stat-icon" style="color:#10b981; background:rgba(16, 185, 129, 0.1);">🏆</div>
            </div>
        </div>

        <div class="card">
            <h3 class="card-title">Mavjud Test Biletlari (Variantlar)</h3>
            <?php if ($total_variants > 0): ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 15px;">
                <?php for ($v = 1; $v <= $total_variants; $v++): 
                    $q_count = ($v == $total_variants && $total_all_questions % 20 != 0) ? ($total_all_questions % 20) : 20;
                ?>
                    <a href="test.php?variant=<?php echo $v; ?>" style="display:flex; flex-direction:column; align-items:center; justify-content:center; padding: 20px; background: white; border: 1px solid #e2e8f0; border-radius: 12px; text-decoration: none; color: inherit; transition: all 0.2s ease-in-out; box-shadow: 0 2px 4px rgba(0,0,0,0.02);" onmouseover="this.style.borderColor='#10b981'; this.style.transform='translateY(-3px)'; this.style.boxShadow='0 10px 15px -3px rgba(16,185,129,0.1)';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.transform='none'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)';">
                        <div style="font-size:1.8rem; margin-bottom:8px;">🚘</div>
                        <h4 style="margin-bottom: 5px; color: var(--primary-color); font-size:1.1rem;">Variant <?php echo $v; ?></h4>
                        <p style="font-size: 0.85rem; color: #64748b; margin: 0;"><?php echo $q_count; ?> ta savol</p>
                        <div style="margin-top: 12px; font-size: 0.85rem; font-weight: 600; color: #10b981; background: rgba(16,185,129,0.1); padding: 5px 12px; border-radius: 20px;">Testni ishlash ➔</div>
                    </a>
                <?php endfor; ?>
            </div>
            <?php else: ?>
                <p style="color: #64748b; font-size: 0.95rem; text-align:center; padding: 20px;">Hozircha tizimga test savollari yuklanmagan.</p>
            <?php endif; ?>
        </div>

        <div class="card">
            <h3 class="card-title">Mening test tarixim</h3>
            <?php if ($tests_count > 0): ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Bilet (Variant)</th>
                            <th>Jami savollar</th>
                            <th>To'g'ri javoblar</th>
                            <th>Foiz natija</th>
                            <th>Sana va Vaqt</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $db->prepare("SELECT * FROM results WHERE user_id = ? ORDER BY taken_at DESC");
                        $stmt->execute([$user_id]);
                        $i = 1;
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $badge_class = $row['score_percent'] >= 70 ? 'badge-success' : 'badge-danger';
                            $v_text = $row['variant_id'] ? "Variant " . $row['variant_id'] : "Tasodifiy";
                            echo "<tr>";
                            echo "<td>{$i}</td>";
                            echo "<td><span style='font-weight:600; color:var(--primary-color);'>{$v_text}</span></td>";
                            echo "<td>{$row['total_questions']} ta</td>";
                            echo "<td>{$row['correct_answers']} ta</td>";
                            echo "<td><span class='badge {$badge_class}'>{$row['score_percent']}%</span></td>";
                            echo "<td>" . date('d.m.Y H:i', strtotime($row['taken_at'])) . "</td>";
                            echo "</tr>";
                            $i++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div style="text-align:center; padding: 40px; color:#64748b;">
                    <p style="margin-bottom:20px;">Siz hali hech qanday test ishlamadingiz.</p>
                    <a href="test.php" class="btn btn-primary">Birinchi testni ishlash</a>
                </div>
            <?php endif; ?>
        </div>

    </div>
</div>

</body>
</html>
