<?php
session_start();
// If logged in, maybe redirect to dashboard
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="uz">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AVTOMAKTAB - Masofaviy ta'lim platformasi</title>
    <meta name="description" content="1200dan ortiq YHQ test savollaridan iborat onlayn test tizimi. Bilimingizni sinab ko'ring.">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- Header -->
    <header class="landing-header">
        <a href="index.php" class="logo">AVTOMAKTAB</a>
        <nav class="nav-links">
            <a href="login.php" class="nav-btn">Kirish</a>
            <a href="register.php" class="btn btn-primary nav-btn">Ro'yxatdan o'tish</a>
        </nav>
    </header>

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="hero-shapes"></div>
        <div class="hero-content">
            <h1 class="hero-title">Yo'l harakati qoidalarini <span>biz bilan</span> o'rganing</h1>
            <p class="hero-text">1200 dan ortiq test savollari. Zamonaviy onlayn platforma orqali o'z bilimlaringizni sinab ko'ring va haydovchilik guvohnomasini olishga tayyorlaning.</p>
            <div class="hero-buttons">
                <a href="register.php" class="btn btn-primary btn-lg">Boshlash</a>
                <a href="#features" class="btn btn-outline btn-lg">Batafsil ma'lumot</a>
            </div>
        </div>
        <div class="hero-image" style="flex:1; display:flex; justify-content:center; align-items:center; z-index:1;">
            <!-- Placeholder for a realistic 3D illustration or mock -->
            <div style="width: 400px; height: 400px; background: linear-gradient(135deg, #a5b4fc, #6366f1); border-radius: 40px; transform: rotate(10deg); box-shadow: 0 25px 50px -12px rgba(99, 102, 241, 0.5); display: flex; align-items:center; justify-content:center;">
                <svg width="200" height="200" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="features">
        <h2 class="features-title">Platforma Imkoniyatlari</h2>
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">📚</div>
                <h3>Katta Baza</h3>
                <p>1200 dan ortiq eng so'nggi va dolzarb YHQ test savollari.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">⚡</div>
                <h3>Tezkor Natija</h3>
                <p>Test yakunlanishi bilanoq natijalar avtomatik hisoblanadi.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📱</div>
                <h3>Qulay Interfeys</h3>
                <p>Har qanday qurilmada ishlash imkonini beruvchi zamonaviy dizayn.</p>
            </div>
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Statistika</h3>
                <p>O'zlashtirish darajangiz va reytingingizni kuzatib boring.</p>
            </div>
        </div>
    </section>

    <!-- Donate Section -->
    <section id="donate" class="donate-section">
        <div class="donate-container">
            <div class="donate-text">
                <h2>Loyiha mutlaqo bepul! ❤️</h2>
                <p>Ushbu masofaviy ta'lim platformasi barcha uchun ochiq va bepul taqdim etiladi. Agar siz loyihamiz manzur kelgan bo'lsa va uning sifatini yanada oshirishga ko'maklashmoqchi bo'lsangiz, bizni moddiy qo'llab-quvvatlashingiz mumkin (ixtiyoriy).</p>
                <div class="donate-benefits">
                    <span>✨ Server xarajatlari uchun</span>
                    <span>✨ Yangi savollar va funksiyalar</span>
                    <span>✨ Reklamasiz toza interfeys</span>
                </div>
            </div>
            <div class="donate-card-wrapper">
                <div class="credit-card">
                    <div class="card-chip"></div>
                    <div class="card-logo">💳</div>
                    <div class="card-number" id="cardNumber">8600 1204 5678 9012</div>
                    <div class="card-details">
                        <div class="card-holder">
                            <span>KARTA EGASI</span>
                            <h4>AVTOMAKTAB FOUNDER</h4>
                        </div>
                        <div class="card-copy">
                            <button onclick="copyCard()" class="btn-copy" id="copyBtn">Nusxa Olish</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <script>
        function copyCard() {
            var cardNum = document.getElementById("cardNumber").innerText.replace(/\s+/g, '');
            navigator.clipboard.writeText(cardNum).then(function() {
                var btn = document.getElementById("copyBtn");
                btn.innerText = "Nusxalandi! ✓";
                btn.style.background = "#10b981";
                setTimeout(function() {
                    btn.innerText = "Nusxa Olish";
                    btn.style.background = "rgba(255, 255, 255, 0.2)";
                }, 2000);
            });
        }
    </script>

    <footer style="background: var(--bg-dark); color: white; padding: 40px 5%; text-align: center;">
        <p>&copy; 2026 AVTOMAKTAB. Barcha huquqlar himoyalangan.</p>
    </footer>

</body>
</html>
