<?php
require_once "config.php";

// Fetch Stats for Landing Page
$subject_count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM subjects");
$subject_count = mysqli_fetch_assoc($subject_count_res)['total'];

$page_count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM pages");
$page_count = mysqli_fetch_assoc($page_count_res)['total'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EduCMS - Interactive Education CMS</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .hero-banner {
            padding: 80px 0 100px;
            text-align: center;
            background: radial-gradient(circle at top right, rgba(99, 102, 241, 0.08), transparent 50%),
                        radial-gradient(circle at bottom left, rgba(16, 185, 129, 0.05), transparent 50%);
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            background-color: var(--accent-light);
            color: var(--accent);
            padding: 6px 14px;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 700;
            margin-bottom: 24px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
            margin-bottom: 80px;
        }
        .feature-card {
            background-color: var(--bg-secondary);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 30px;
            transition: var(--transition);
            box-shadow: var(--shadow-sm);
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-lg);
            border-color: var(--accent-light);
        }
        .feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background-color: var(--accent-light);
            color: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 20px;
        }
        .feature-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .feature-desc {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }
    </style>
</head>
<body>
    <!-- Site Header -->
    <header class="site-header">
        <div class="container header-inner">
            <a href="index.php" class="logo">
                📚 <span>EduCMS</span>
            </a>
            <nav class="nav-links">
                <a href="index.php" class="nav-link" style="color: var(--accent); font-weight:700;">Home</a>
                <a href="books.php" class="nav-link">Subject Directory</a>
                <a href="login.php" class="btn btn-secondary btn-sm">Admin Portal</a>
            </nav>
        </div>
    </header>

    <!-- Hero Area -->
    <section class="hero-banner">
        <div class="container">
            <span class="hero-badge">🎓 Welcome to Online Learning</span>
            <h1 style="font-size: 3.5rem; font-weight: 800; line-height: 1.15; margin-bottom: 20px; letter-spacing: -0.03em;">
                Empowering Students with <br><span style="background: linear-gradient(135deg, var(--accent) 0%, #312e81 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Interactive Content</span>
            </h1>
            <p style="font-size: 1.2rem; color: var(--text-secondary); max-width: 650px; margin: 0 auto 36px; line-height: 1.7;">
                Access administrative course subjects, lecture pages, study guides, and comprehensive notes. A modern Education CMS designed to make reading and learning effortless.
            </p>
            <div style="display: flex; gap: 16px; justify-content: center;">
                <a href="books.php" class="btn btn-primary" style="font-size: 1.05rem; padding: 12px 28px;">📖 Open Subject Directory</a>
                <a href="login.php" class="btn btn-secondary" style="font-size: 1.05rem; padding: 12px 28px;">Admin Dashboard</a>
            </div>
        </div>
    </section>

    <!-- Metrics Stats Banner -->
    <section style="background-color: var(--bg-secondary); border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); padding: 40px 0;">
        <div class="container" style="display: flex; justify-content: space-around; flex-wrap: wrap; gap: 30px; text-align: center;">
            <div>
                <div style="font-size: 2.5rem; font-weight: 800; color: var(--accent); line-height: 1; margin-bottom: 6px;"><?php echo $subject_count; ?></div>
                <div style="font-size: 0.95rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em;">Academic Subjects</div>
            </div>
            <div style="width: 1px; background-color: var(--border); align-self: stretch;"></div>
            <div>
                <div style="font-size: 2.5rem; font-weight: 800; color: var(--success); line-height: 1; margin-bottom: 6px;"><?php echo $page_count; ?></div>
                <div style="font-size: 0.95rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em;">Interactive Pages</div>
            </div>
        </div>
    </section>

    <!-- Key CMS Features -->
    <section class="container" style="padding: 80px 0 40px;">
        <h2 style="text-align: center; font-size: 2rem; font-weight: 800; margin-bottom: 12px;">Made for Modern Classrooms</h2>
        <p style="text-align: center; color: var(--text-secondary); max-width: 500px; margin: 0 auto 50px;">Our custom Education CMS focuses on clear, fast navigation and easy reading.</p>
        
        <div class="feature-grid">
            <!-- Feature 1 -->
            <div class="feature-card">
                <div class="feature-icon">📖</div>
                <h3 class="feature-title">E-Reading Interface</h3>
                <p class="feature-desc">A professional split-screen viewer that lets students toggle between chapters and page indices with one click.</p>
            </div>
            
            <!-- Feature 2 -->
            <div class="feature-card">
                <div class="feature-icon">🔍</div>
                <h3 class="feature-title">Instant Subject Filter</h3>
                <p class="feature-desc">Search through administrative subjects instantly using our fast Javascript client filter. No page reloading required.</p>
            </div>

            <!-- Feature 3 -->
            <div class="feature-card">
                <div class="feature-icon">🛡️</div>
                <h3 class="feature-title">Protected Dashboard</h3>
                <p class="feature-desc">Secure session and code protections for the administrative board, keeping curriculum edits exclusive to authorized educators.</p>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer style="background-color: #0f172a; color: #94a3b8; padding: 40px 0; font-size: 0.9rem; border-top: 1px solid #1e293b;">
        <div class="container" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 20px;">
            <div>
                <span style="color: white; font-weight: 800; font-size: 1.1rem; display: flex; align-items: center; gap: 6px;">📚 EduCMS</span>
                <p style="margin-top: 6px; font-size: 0.8rem; color: #64748b;">Education Course Management System © <?php echo date('Y'); ?></p>
            </div>
            <div style="display: flex; gap: 20px;">
                <a href="books.php" style="color: #94a3b8;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#94a3b8'">Subject Directory</a>
                <a href="login.php" style="color: #94a3b8;" onmouseover="this.style.color='white'" onmouseout="this.style.color='#94a3b8'">Admin Panel</a>
            </div>
        </div>
    </footer>
</body>
</html>
