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

// Handle Delete Subject
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    
    $stmt = mysqli_prepare($conn, "DELETE FROM subjects WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $delete_id);
        if (mysqli_stmt_execute($stmt)) {
            $success = "Subject and all its pages deleted successfully.";
        } else {
            $error = "Failed to delete the subject.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Handle Add Subject
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_subject'])) {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (!empty($title)) {
        $stmt = mysqli_prepare($conn, "INSERT INTO subjects (title, description) VALUES (?, ?)");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ss", $title, $description);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Subject added successfully!";
            } else {
                $error = "Error adding subject. Title might be already taken.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error = "Subject title is required.";
    }
}

// Handle Update Subject
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_subject'])) {
    $id = intval($_POST['subject_id']);
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);

    if (!empty($title)) {
        $stmt = mysqli_prepare($conn, "UPDATE subjects SET title = ?, description = ? WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssi", $title, $description, $id);
            if (mysqli_stmt_execute($stmt)) {
                $success = "Subject updated successfully!";
            } else {
                $error = "Error updating subject.";
            }
            mysqli_stmt_close($stmt);
        }
    } else {
        $error = "Subject title is required.";
    }
}

// Fetch subject for editing if edit_id is set
$edit_mode = false;
$edit_subject = ['id' => '', 'title' => '', 'description' => ''];
if (isset($_GET['edit_id'])) {
    $edit_id = intval($_GET['edit_id']);
    $stmt = mysqli_prepare($conn, "SELECT * FROM subjects WHERE id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $edit_id);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result && mysqli_num_rows($result) > 0) {
            $edit_subject = mysqli_fetch_assoc($result);
            $edit_mode = true;
        }
        mysqli_stmt_close($stmt);
    }
}

// Fetch all subjects to display
$subjects_res = mysqli_query($conn, "SELECT s.*, COUNT(p.id) as page_count FROM subjects s LEFT JOIN pages p ON s.id = p.subject_id GROUP BY s.id ORDER BY s.title ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Subjects - EduCMS Admin</title>
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
                <li class="admin-menu-item active">
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
                <div class="admin-title">Manage Subjects</div>
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

                <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px; align-items: start;">
                    <!-- CRUD FORM -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title"><?php echo $edit_mode ? '✏️ Edit Subject' : '➕ Add New Subject'; ?></div>
                        </div>
                        <div class="form-card">
                            <form action="manage_subjects.php" method="POST">
                                <?php if ($edit_mode): ?>
                                    <input type="hidden" name="subject_id" value="<?php echo $edit_subject['id']; ?>">
                                <?php endif; ?>

                                <div class="form-group">
                                    <label for="title">Subject Title</label>
                                    <input type="text" id="title" name="title" class="form-control" placeholder="e.g., Mathematics 101, History of Art" required value="<?php echo htmlspecialchars($edit_subject['title']); ?>">
                                </div>

                                <div class="form-group">
                                    <label for="description">Short Description</label>
                                    <textarea id="description" name="description" class="form-control" placeholder="Briefly describe this academic subject..." style="min-height: 140px;"><?php echo htmlspecialchars($edit_subject['description']); ?></textarea>
                                </div>

                                <div style="display: flex; gap: 10px; margin-top: 20px;">
                                    <?php if ($edit_mode): ?>
                                        <button type="submit" name="update_subject" class="btn btn-primary" style="flex-grow: 1;">Update Subject</button>
                                        <a href="manage_subjects.php" class="btn btn-secondary">Cancel</a>
                                    <?php else: ?>
                                        <button type="submit" name="add_subject" class="btn btn-primary" style="width: 100%;">Create Subject</button>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- SUBJECT LIST TABLE -->
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">📁 Active Academic Subjects</div>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Subject Name</th>
                                        <th>Description</th>
                                        <th>Pages</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if(mysqli_num_rows($subjects_res) > 0): ?>
                                        <?php while($sub = mysqli_fetch_assoc($subjects_res)): ?>
                                            <tr>
                                                <td style="font-weight: 700; color: var(--accent);">
                                                    <?php echo htmlspecialchars($sub['title']); ?>
                                                </td>
                                                <td style="font-size: 0.85rem; color: var(--text-secondary); max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                    <?php echo htmlspecialchars($sub['description']); ?>
                                                </td>
                                                <td>
                                                    <span class="book-badge" style="margin-bottom:0; font-size:0.75rem; padding: 2px 8px;">
                                                        <?php echo $sub['page_count']; ?> page<?php echo $sub['page_count'] != 1 ? 's' : ''; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="actions-cell">
                                                        <a href="manage_subjects.php?edit_id=<?php echo $sub['id']; ?>" class="btn btn-secondary btn-sm" title="Edit">✏️ Edit</a>
                                                        <a href="manage_subjects.php?delete_id=<?php echo $sub['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure? Deleting this subject will also delete all pages inside it!');" title="Delete">🗑️ Delete</a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="4" class="empty-table">No subjects added yet. Create your first subject using the form!</td>
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
