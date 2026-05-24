<?php
require_once "config.php";

// Fetch all subjects with page counts
$subjects_res = mysqli_query($conn, "SELECT s.*, COUNT(p.id) as page_count FROM subjects s LEFT JOIN pages p ON s.id = p.subject_id GROUP BY s.id ORDER BY s.title ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subject Directory - Education CMS Catalog</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Site Header -->
    <header class="site-header">
        <div class="container header-inner">
            <a href="index.php" class="logo">
                📚 <span>EduCMS</span>
            </a>
            <nav class="nav-links">
                <a href="index.php" class="nav-link">Home</a>
                <a href="books.php" class="nav-link" style="color: var(--accent); font-weight:700;">Subject Directory</a>
                <a href="login.php" class="btn btn-secondary btn-sm">Admin Portal</a>
            </nav>
        </div>
    </header>

    <!-- Main Subject View -->
    <section class="hero" style="padding-bottom: 20px;">
        <div class="container">
            <h1>Browse Course Subjects</h1>
            <p>Select any subject or topic below to open the interactive study reader and access lecture pages.</p>
        </div>
    </section>

    <div class="container">
        <!-- Live JS Search bar -->
        <div class="search-container">
            <span class="search-icon">🔍</span>
            <input type="text" id="librarySearch" class="search-input" placeholder="Search subjects or topics..." autocomplete="off">
        </div>

        <!-- Subject Grid -->
        <div class="book-grid" id="bookCatalog">
            <?php if (mysqli_num_rows($subjects_res) > 0): ?>
                <?php while ($sub = mysqli_fetch_assoc($subjects_res)): ?>
                    <div class="book-card" data-title="<?php echo htmlspecialchars(strtolower($sub['title'])); ?>" data-desc="<?php echo htmlspecialchars(strtolower($sub['description'])); ?>">
                        <span class="book-badge">Subject</span>
                        <h3 class="book-title"><?php echo htmlspecialchars($sub['title']); ?></h3>
                        <p class="book-desc"><?php echo htmlspecialchars($sub['description'] ?: 'No description provided for this subject. Click Start Reading to explore page contents.'); ?></p>
                        
                        <div class="book-footer">
                            <span class="page-count">
                                📄 <strong><?php echo $sub['page_count']; ?></strong> page<?php echo $sub['page_count'] != 1 ? 's' : ''; ?>
                            </span>
                            <a href="reader.php?subject_id=<?php echo $sub['id']; ?>" class="btn btn-primary btn-sm">
                                Start Reading →
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 60px 0; color: var(--text-secondary);">
                    <div style="font-size: 3rem; margin-bottom: 12px;">📚</div>
                    <h3>CMS directory is currently empty</h3>
                    <p style="margin-top: 8px;">Administrators haven't uploaded any subjects or topics yet.</p>
                    <a href="login.php" class="btn btn-primary btn-sm" style="margin-top: 16px;">Go to Admin Portal</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Empty Search Result Alert -->
        <div id="noResults" style="display: none; text-align: center; padding: 60px 20px; color: var(--text-secondary); margin-bottom: 60px;">
            <div style="font-size: 3rem; margin-bottom: 12px;">🔍</div>
            <h3>No subjects match your search</h3>
            <p>Try searching for a different keyword or topic.</p>
        </div>
    </div>

    <!-- Footer -->
    <footer style="background-color: #0f172a; color: #94a3b8; padding: 40px 0; font-size: 0.9rem; border-top: 1px solid #1e293b; margin-top: auto;">
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

    <!-- Client-side Fast Live Search Filter JS -->
    <script>
        document.getElementById('librarySearch').addEventListener('input', function(e) {
            const query = e.target.value.toLowerCase().trim();
            const cards = document.querySelectorAll('#bookCatalog .book-card');
            let visibleCount = 0;

            cards.forEach(card => {
                const title = card.getAttribute('data-title');
                const desc = card.getAttribute('data-desc');

                if (title.includes(query) || desc.includes(query)) {
                    card.style.display = 'flex';
                    visibleCount++;
                } else {
                    card.style.display = 'none';
                }
            });

            // Handle No Results state
            const noResults = document.getElementById('noResults');
            if (visibleCount === 0 && cards.length > 0) {
                noResults.style.display = 'block';
            } else {
                noResults.style.display = 'none';
            }
        });
    </script>
</body>
</html>
