<?php
session_start();
require_once "config.php";

// Session check to protect dashboard
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];
$error = '';
$success = '';

// Handle Delete Page
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    $stmt = mysqli_prepare($conn, "DELETE FROM pages WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Page deleted successfully.";
        } else {
            $error = "Failed to delete the page.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle Add Page
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_page'])) {
    $subject_id = intval($_POST['subject_id']);
    $title = trim($_POST['title']);
    $page_number = intval($_POST['page_number']);
    $content = trim($_POST['content']);

    if ($subject_id > 0 && !empty($title) && !empty($content)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO pages (subject_id, title, page_number, content) VALUES (?, ?, ?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "isis", $subject_id, $title, $page_number, $content);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Page created successfully!";
            } else {
                $error = "Error creating page.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error = "Please fill in all required fields (Subject, Title, Content).";
    }
}

// Handle Update Page
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_page'])) {
    $id = intval($_POST['page_id']);
    $subject_id = intval($_POST['subject_id']);
    $title = trim($_POST['title']);
    $page_number = intval($_POST['page_number']);
    $content = trim($_POST['content']);

    if ($id > 0 && $subject_id > 0 && !empty($title) && !empty($content)) {
        $stmt = mysqli_prepare($conn, "UPDATE pages SET subject_id = ?, title = ?, page_number = ?, content = ? WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "isisi", $subject_id, $title, $page_number, $content, $id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Page updated successfully!";
            } else {
                $error = "Error updating page.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error = "Please fill in all required fields.";
    }
}

// Fetch page for editing if edit_id is set
$edit_mode = false;
$edit_page = ['id' => '', 'subject_id' => '', 'title' => '', 'page_number' => '1', 'content' => ''];
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = mysqli_prepare($conn, "SELECT * FROM pages WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $edit_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result && mysqli_num_rows($result) > 0) {
            $edit_page = mysqli_fetch_assoc($result);
            $edit_mode = true;
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch all subjects for the dropdowns
$subjects_res = mysqli_query($conn, "SELECT * FROM subjects ORDER BY title ASC");
$subjects_list = [];
if ($subjects_res) {
    while ($row = mysqli_fetch_assoc($subjects_res)) {
        $subjects_list[] = $row;
    }
}

// Handle Subject Filter
$filter_subject_id = 0;
if (isset($_GET['subject_filter'])) {
    $filter_subject_id = intval($_GET['subject_filter']);
}

// Fetch pages with filtering
$query = "SELECT p.*, s.title as subject_title FROM pages p JOIN subjects s ON p.subject_id = s.id";
if ($filter_subject_id > 0) {
    $query .= " WHERE p.subject_id = " . $filter_subject_id;
}
$query .= " ORDER BY s.title ASC, p.page_number ASC";
$pages_res = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Pages - EduCMS Admin</title>
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
                <li class="admin-menu-item">
                    <a href="dashboard.php">📊 Dashboard</a>
                </li>
                <li class="admin-menu-item">
                    <a href="manage_subjects.php">📁 Manage Subjects</a>
                </li>
                <li class="admin-menu-item active">
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
                <div class="admin-title">Manage Subject Pages</div>
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
                <!-- Message Alerts -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <div style="display: grid; grid-template-columns: 1fr; gap: 30px;">
                    
                    <!-- DUAL COLUMN ROW: CREATE FORM on LEFT, VIEW LIST TABLE on RIGHT -->
                    <div style="display: grid; grid-template-columns: 1.2fr 2fr; gap: 30px; align-items: start;">
                        
                        <!-- CREATE/EDIT PAGE FORM -->
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title"><?php echo $edit_mode ? '✏️ Edit Page Content' : '➕ Add New Page'; ?></div>
                            </div>
                            <div class="form-card">
                                <?php if (empty($subjects_list)): ?>
                                    <div class="alert alert-danger" style="margin-bottom: 0;">
                                        ⚠️ You must <a href="manage_subjects.php" style="font-weight:700; text-decoration:underline;">Create a Subject</a> first before adding pages!
                                    </div>
                                <?php else: ?>
                                    <form action="manage_pages.php" method="POST">
                                        <?php if ($edit_mode): ?>
                                            <input type="hidden" name="page_id" value="<?php echo $edit_page['id']; ?>">
                                        <?php endif; ?>

                                        <div class="form-group">
                                            <label for="subject_id">Select Subject</label>
                                            <select id="subject_id" name="subject_id" class="form-control" required>
                                                <option value="">-- Choose Subject --</option>
                                                <?php foreach ($subjects_list as $subj): ?>
                                                    <option value="<?php echo $subj['id']; ?>" <?php echo $edit_page['subject_id'] == $subj['id'] ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($subj['title']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>

                                        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 15px;">
                                            <div class="form-group">
                                                <label for="title">Page Title</label>
                                                <input type="text" id="title" name="title" class="form-control" placeholder="e.g., Chapter 1 Introduction" required value="<?php echo htmlspecialchars($edit_page['title']); ?>">
                                            </div>
                                            <div class="form-group">
                                                <label for="page_number">Page #</label>
                                                <input type="number" id="page_number" name="page_number" class="form-control" min="1" required value="<?php echo htmlspecialchars($edit_page['page_number']); ?>">
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="content">Page Content (Supports HTML or Text)</label>
                                            <textarea id="content" name="content" class="form-control" placeholder="Write full lecture notes, lesson content, or text here..." style="min-height: 250px;" required><?php echo htmlspecialchars($edit_page['content']); ?></textarea>
                                        </div>

                                        <div style="display: flex; gap: 10px; margin-top: 20px;">
                                            <?php if ($edit_mode): ?>
                                                <button type="submit" name="update_page" class="btn btn-primary" style="flex-grow: 1;">Update Page</button>
                                                <a href="manage_pages.php" class="btn btn-secondary">Cancel</a>
                                            <?php else: ?>
                                                <button type="submit" name="add_page" class="btn btn-primary" style="width: 100%;">Publish Page</button>
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- PAGES LIST TABLE -->
                        <div class="card">
                            <div class="card-header" style="flex-wrap: wrap; gap: 16px;">
                                <div class="card-title">📄 Active Lesson Pages</div>
                                
                                <!-- SUBJECT FILTER DROPDOWN -->
                                <form action="manage_pages.php" method="GET" style="display: flex; align-items: center; gap: 8px;">
                                    <label for="subject_filter" style="font-size:0.8rem; font-weight:700; color:var(--text-secondary);">Filter:</label>
                                    <select id="subject_filter" name="subject_filter" class="form-control" style="padding: 6px 12px; font-size: 0.85rem; width: auto;" onchange="this.form.submit()">
                                        <option value="0">All Subjects</option>
                                        <?php foreach ($subjects_list as $subj): ?>
                                            <option value="<?php echo $subj['id']; ?>" <?php echo $filter_subject_id == $subj['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($subj['title']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </form>
                            </div>
                            
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Subject</th>
                                            <th>Page #</th>
                                            <th>Page Title</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if(mysqli_num_rows($pages_res) > 0): ?>
                                            <?php while($p = mysqli_fetch_assoc($pages_res)): ?>
                                                <tr>
                                                    <td>
                                                        <span class="book-badge" style="margin-bottom:0; font-size:0.7rem; padding: 2px 8px;">
                                                            <?php echo htmlspecialchars($p['subject_title']); ?>
                                                        </span>
                                                    </td>
                                                    <td style="font-weight: 700; text-align: center; color: var(--text-secondary);">
                                                        <?php echo $p['page_number']; ?>
                                                    </td>
                                                    <td style="font-weight: 600; max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                        <?php echo htmlspecialchars($p['title']); ?>
                                                    </td>
                                                    <td>
                                                        <div class="actions-cell">
                                                            <a href="manage_pages.php?edit_id=<?php echo $p['id']; ?><?php echo $filter_subject_id > 0 ? '&subject_filter='.$filter_subject_id : ''; ?>" class="btn btn-secondary btn-sm" title="Edit">✏️ Edit</a>
                                                            <a href="manage_pages.php?delete_id=<?php echo $p['id']; ?><?php echo $filter_subject_id > 0 ? '&subject_filter='.$filter_subject_id : ''; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this page?');" title="Delete">🗑️ Delete</a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="4" class="empty-table">
                                                    No pages found. <?php echo $filter_subject_id > 0 ? "Try selecting another subject filter or add a new page." : "Create your first subject page using the form!"; ?>
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
