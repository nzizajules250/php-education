<?php
require_once "config.php";

// 1. Get Subject/Book ID
$subject_id = isset($_GET['subject_id']) ? intval($_GET['subject_id']) : 0;
if ($subject_id <= 0) {
    header("Location: books.php");
    exit;
}

// 2. Fetch Subject Details
$subject_stmt = mysqli_prepare($conn, "SELECT * FROM subjects WHERE id = ?");
$subject = null;
if ($subject_stmt) {
    mysqli_stmt_bind_param($subject_stmt, "i", $subject_id);
    mysqli_stmt_execute($subject_stmt);
    $res = mysqli_stmt_get_result($subject_stmt);
    if ($res && mysqli_num_rows($res) > 0) {
        $subject = mysqli_fetch_assoc($res);
    }
    mysqli_stmt_close($subject_stmt);
}

if (!$subject) {
    header("Location: books.php");
    exit;
}

// 3. Fetch all pages of this subject, sorted by page_number
$pages_stmt = mysqli_prepare($conn, "SELECT id, title, page_number FROM pages WHERE subject_id = ? ORDER BY page_number ASC, id ASC");
$pages_list = [];
if ($pages_stmt) {
    mysqli_stmt_bind_param($pages_stmt, "i", $subject_id);
    mysqli_stmt_execute($pages_stmt);
    $res = mysqli_stmt_get_result($pages_stmt);
    while ($row = mysqli_fetch_assoc($res)) {
        $pages_list[] = $row;
    }
    mysqli_stmt_close($pages_stmt);
}

// 4. Determine Active Page
$active_page_id = isset($_GET['page_id']) ? intval($_GET['page_id']) : 0;
$active_page = null;

if (!empty($pages_list)) {
    // If no page_id is requested or it's invalid, default to the first page in the sorted list
    $active_page_index = 0;
    if ($active_page_id > 0) {
        foreach ($pages_list as $index => $pg) {
            if ($pg['id'] == $active_page_id) {
                $active_page_index = $index;
                break;
            }
        }
    }
    
    // Fetch full content of the active page
    $active_pg_meta = $pages_list[$active_page_index];
    $active_page_id = $active_pg_meta['id'];
    
    $content_stmt = mysqli_prepare($conn, "SELECT * FROM pages WHERE id = ?");
    if ($content_stmt) {
        mysqli_stmt_bind_param($content_stmt, "i", $active_page_id);
        mysqli_stmt_execute($content_stmt);
        $res = mysqli_stmt_get_result($content_stmt);
        if ($res && mysqli_num_rows($res) > 0) {
            $active_page = mysqli_fetch_assoc($res);
        }
        mysqli_stmt_close($content_stmt);
    }
    
    // 5. Determine Prev/Next Page IDs
    $prev_page = ($active_page_index > 0) ? $pages_list[$active_page_index - 1] : null;
    $next_page = ($active_page_index < count($pages_list) - 1) ? $pages_list[$active_page_index + 1] : null;
}

/**
 * Format page content to support rich HTML while beautifully escaping and formatting code snippets.
 */
function format_page_content($content) {
    if (stripos($content, '<code') === false) {
        return $content;
    }
    
    // 1. Process pre-wrapped code blocks: <pre><code>...</code></pre>
    $content = preg_replace_callback('/<pre\b([^>]*)>\s*<code\b([^>]*)>(.*?)<\/code>\s*<\/pre>/is', function($matches) {
        $pre_attrs = $matches[1];
        $code_attrs = $matches[2];
        $code_content = $matches[3];
        $escaped = htmlspecialchars($code_content, ENT_QUOTES, 'UTF-8');
        return '<pre class="code-block"' . $pre_attrs . '><code' . $code_attrs . '>' . $escaped . '</code></pre>';
    }, $content);
    
    // 2. Process stand-alone <code>...</code> blocks (could be inline or multi-line block)
    $content = preg_replace_callback('/<code\b([^>]*)>(.*?)<\/code>/is', function($matches) {
        $attrs = $matches[1];
        $code_content = $matches[2];
        $escaped = htmlspecialchars($code_content, ENT_QUOTES, 'UTF-8');
        
        // If it contains newlines, render as a styled block; otherwise render inline
        if (strpos($code_content, "\n") !== false || strpos($code_content, "\r") !== false) {
            return '<pre class="code-block"><code' . $attrs . '>' . $escaped . '</code></pre>';
        } else {
            return '<code class="code-inline"' . $attrs . '>' . $escaped . '</code>';
        }
    }, $content);
    
    return $content;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($subject['title']); ?> - Study Reader</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Reader Specific Layout Adjustments */
        .site-header {
            position: relative;
            background-color: var(--bg-secondary);
        }
        .reader-body p {
            margin-bottom: 24px;
            color: #334155;
            font-size: 1.05rem;
            line-height: 1.85;
        }
        .reader-body h1, .reader-body h2, .reader-body h3 {
            margin: 32px 0 16px;
            color: var(--text-primary);
            font-weight: 700;
        }
        .reader-body h1 { font-size: 1.75rem; }
        .reader-body h2 { font-size: 1.5rem; }
        .reader-body h3 { font-size: 1.25rem; }
    </style>
</head>
<body style="background-color: var(--bg-primary);">
    <!-- Site Header -->
    <header class="site-header">
        <div class="container header-inner" style="max-width: 100%; padding: 0 40px;">
            <a href="index.php" class="logo">
                📚 <span>EduCMS</span>
            </a>
            <div class="nav-links">
                <a href="books.php" class="btn btn-secondary btn-sm">← Back to Subject Directory</a>
            </div>
        </div>
    </header>

    <div class="reader-layout">
        <!-- Sidebar: Table of Contents -->
        <aside class="reader-sidebar">
            <div>
                <div class="reader-sidebar-title"><?php echo htmlspecialchars($subject['title']); ?></div>
                <div class="reader-sidebar-desc"><?php echo htmlspecialchars($subject['description'] ?: 'Digital course content'); ?></div>
            </div>
            
            <div style="border-top: 1px solid var(--border); padding-top: 16px;">
                <h4 style="font-size: 0.8rem; font-weight: 700; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 12px;">Subject Pages</h4>
                
                <?php if (!empty($pages_list)): ?>
                    <ul class="toc-list">
                        <?php foreach ($pages_list as $index => $pg): ?>
                            <li class="toc-item <?php echo $pg['id'] == $active_page_id ? 'active' : ''; ?>">
                                <a href="reader.php?subject_id=<?php echo $subject_id; ?>&page_id=<?php echo $pg['id']; ?>">
                                    <span class="toc-item-number"><?php echo $pg['page_number']; ?></span>
                                    <span style="overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?php echo htmlspecialchars($pg['title']); ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p style="font-size: 0.85rem; color: var(--text-secondary); font-style: italic;">No pages created yet.</p>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Main Content Pane -->
        <main class="reader-content">
            <?php if ($active_page): ?>
                <article class="reader-article">
                    <header class="reader-header">
                        <div class="reader-subject"><?php echo htmlspecialchars($subject['title']); ?> — Page <?php echo $active_page['page_number']; ?></div>
                        <h1 class="reader-title"><?php echo htmlspecialchars($active_page['title']); ?></h1>
                    </header>
                    
                    <div class="reader-body">
                        <!-- We support line breaks and simple HTML elements for rich text -->
                        <?php 
                            if ($active_page['content'] === strip_tags($active_page['content'])) {
                                echo nl2br(htmlspecialchars($active_page['content']));
                            } else {
                                echo format_page_content($active_page['content']);
                            }
                        ?>
                    </div>

                    <!-- Prev/Next Navigation Footer -->
                    <div class="reader-nav">
                        <?php if ($prev_page): ?>
                            <a href="reader.php?subject_id=<?php echo $subject_id; ?>&page_id=<?php echo $prev_page['id']; ?>" class="btn btn-secondary btn-sm">
                                👈 Page <?php echo $prev_page['page_number']; ?>: <?php echo htmlspecialchars(substr($prev_page['title'], 0, 20)); ?><?php echo strlen($prev_page['title']) > 20 ? '...' : ''; ?>
                            </a>
                        <?php else: ?>
                            <div></div> <!-- Spacer -->
                        <?php endif; ?>

                        <?php if ($next_page): ?>
                            <a href="reader.php?subject_id=<?php echo $subject_id; ?>&page_id=<?php echo $next_page['id']; ?>" class="btn btn-primary btn-sm">
                                Page <?php echo $next_page['page_number']; ?>: <?php echo htmlspecialchars(substr($next_page['title'], 0, 20)); ?><?php echo strlen($next_page['title']) > 20 ? '...' : ''; ?> 👉
                            </a>
                        <?php else: ?>
                            <div></div> <!-- Spacer -->
                        <?php endif; ?>
                    </div>
                </article>
            <?php else: ?>
                <div class="reader-empty">
                    <div style="font-size: 4rem; margin-bottom: 20px;">📖</div>
                    <h3>This Subject is empty</h3>
                    <p>The instructor has successfully created the subject entry but hasn't written or published study pages inside yet.</p>
                    <a href="books.php" class="btn btn-secondary" style="margin-top: 10px;">Return to Directory</a>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
