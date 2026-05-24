<?php
session_start();
require_once "config.php";

// Session check to protect dashboard
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// Get Stats
// 1. Total Subjects
$subject_count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM subjects");
$subject_count = mysqli_fetch_assoc($subject_count_res)['total'];

// 2. Total Pages
$page_count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM pages");
$page_count = mysqli_fetch_assoc($page_count_res)['total'];

// 3. Total Administrators
$user_count_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
$user_count = mysqli_fetch_assoc($user_count_res)['total'];

// Get Recent Subjects
$recent_subjects_res = mysqli_query($conn, "SELECT * FROM subjects ORDER BY created_at DESC LIMIT 5");

// Get Recent Pages
$recent_pages_res = mysqli_query($conn, "SELECT p.*, s.title as subject_title FROM pages p JOIN subjects s ON p.subject_id = s.id ORDER BY p.created_at DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - EduCMS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar Navigation -->
        <aside class="admin-sidebar">
            <div class="admin-sidebar-header">
                📚 <span>EduCMS Admin</span>
            </div>
            <ul class="admin-menu">
                <li class="admin-menu-item active">
                    <a href="dashboard.php">📊 Dashboard</a>
                </li>
                <li class="admin-menu-item">
                    <a href="manage_subjects.php">📁 Manage Subjects</a>
                </li>
                <li class="admin-menu-item">
                    <a href="manage_pages.php">📄 Manage Pages</a>
                </li>
                <li class="admin-menu-item" style="margin-top: 20px; border-top: 1px solid #1e293b; padding-top: 10px;">
                    <a href="index.php" target="_blank">🌐 View CMS</a>
                </li>
            </ul>
            <div class="admin-sidebar-footer">
                <a href="logout.php" class="btn btn-danger btn-sm" style="width: 100%;">🚪 Logout</a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="admin-content">
            <header class="admin-header">
                <div class="admin-title">Dashboard Overview</div>
                <div class="admin-user">
                    <div class="admin-user-avatar">
                        <?php echo strtoupper(substr($username, 0, 1)); ?>
                    </div>
                    <div>
                        <div style="font-weight: 700; font-size: 0.95rem;"><?php echo htmlspecialchars($username); ?></div>
                        <div style="font-size: 0.75rem; color: var(--text-secondary);">Administrator</div>
                    </div>
                </div>
            </header>

            <div class="admin-body">
                <!-- Welcome Banner -->
                <div class="card" style="background: linear-gradient(135deg, var(--accent) 0%, #312e81 100%); color: white; padding: 30px; border: none; margin-bottom: 30px;">
                    <h2 style="font-weight: 800; font-size: 1.8rem; margin-bottom: 8px;">Welcome back, <?php echo htmlspecialchars($username); ?>!</h2>
                    <p style="opacity: 0.9; font-size: 1rem; max-width: 600px;">
                        Manage your school's online repository of academic subjects, educational modules, and course pages easily. Create subjects, write lesson content, and organize learning paths.
                    </p>
                </div>

                <!-- Stats Counters -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <h4>Total Subjects</h4>
                            <div class="stat-value"><?php echo $subject_count; ?></div>
                        </div>
                        <div class="stat-icon">📁</div>
                    </div>
                    
                    <div class="stat-card">
                        <div class="stat-info">
                            <h4>Total Pages</h4>
                            <div class="stat-value"><?php echo $page_count; ?></div>
                        </div>
                        <div class="stat-icon">📄</div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-info">
                            <h4>Administrators</h4>
                            <div class="stat-value"><?php echo $user_count; ?></div>
                        </div>
                        <div class="stat-icon">👤</div>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(450px, 1fr)); gap: 24px;">
                    <!-- Recent Subjects -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">🆕 Recent Subjects</div>
                            <a href="manage_subjects.php" class="btn btn-secondary btn-sm">Manage All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Subject Name</th>
                                        <th>Date Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($recent_subjects_res) > 0): ?>
                                        <?php while($sub = mysqli_fetch_assoc($recent_subjects_res)): ?>
                                            <tr>
                                                <td style="font-weight: 600;"><?php echo htmlspecialchars($sub['title']); ?></td>
                                                <td style="color: var(--text-secondary);"><?php echo date('M d, Y', strtotime($sub['created_at'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" class="empty-table">No subjects added yet. <a href="manage_subjects.php" style="color: var(--accent); font-weight:600;">Add one here</a></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Recent Pages -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">📝 Recent Pages Added</div>
                            <a href="manage_pages.php" class="btn btn-secondary btn-sm">Manage All</a>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Page Title</th>
                                        <th>Subject</th>
                                        <th>Added</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($recent_pages_res) > 0): ?>
                                        <?php while($p = mysqli_fetch_assoc($recent_pages_res)): ?>
                                            <tr>
                                                <td style="font-weight: 600;"><?php echo htmlspecialchars($p['title']); ?></td>
                                                <td><span class="book-badge" style="margin-bottom:0; font-size:0.7rem; padding:2px 8px;"><?php echo htmlspecialchars($p['subject_title']); ?></span></td>
                                                <td style="color: var(--text-secondary);"><?php echo date('M d, Y', strtotime($p['created_at'])); ?></td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="3" class="empty-table">No pages created yet. <a href="manage_pages.php" style="color: var(--accent); font-weight:600;">Add one here</a></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>