<?php
session_start();

// Redirect if already logged in
if (isset($_SESSION['username'])) {
    header("Location: dashboard.php");
    exit;
}

require_once "config.php";
$error = '';
$success = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['register'])) {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $secret_code = trim($_POST['secret_code']);

    if (!empty($username) && !empty($email) && !empty($password) && !empty($secret_code)) {
        // Validate administrative secret registration code
        if ($secret_code !== 'educationadmincms') {
            $error = "Incorrect Secret Registration Code. Authorization denied.";
        } // Validate email format
        else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "Invalid email format.";
        } else {
            // Check if username or email already exists
            $stmt = mysqli_prepare($conn, "SELECT id FROM users WHERE username = ? OR email = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ss", $username, $email);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) > 0) {
                    $error = "Username or Email is already registered.";
                    mysqli_stmt_close($stmt);
                } else {
                    mysqli_stmt_close($stmt);
                    
                    // Secure Password Hashing
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insert new user
                    $insert_stmt = mysqli_prepare($conn, "INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
                    if ($insert_stmt) {
                        mysqli_stmt_bind_param($insert_stmt, "sss", $username, $email, $hashed_password);
                        if (mysqli_stmt_execute($insert_stmt)) {
                            $success = "Registration successful! You can now login.";
                        } else {
                            $error = "Something went wrong. Please try again.";
                        }
                        mysqli_stmt_close($insert_stmt);
                    }
                }
            } else {
                $error = "Database error. Please try again.";
            }
        }
    } else {
        $error = "Please fill in all fields.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Register - Education CMS</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-page">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <div class="logo" style="justify-content: center; margin-bottom: 12px;">
                    📚 <span>EduCMS</span>
                </div>
                <h2>Create Admin Account</h2>
                <p>Register as a new Administrator of the CMS.</p>
            </div>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="Choose a username" required autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Enter email" required autocomplete="email">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Choose a password" required autocomplete="new-password">
                </div>

                <div class="form-group">
                    <label for="secret_code">Secret Registration Code</label>
                    <input type="password" id="secret_code" name="secret_code" class="form-control" placeholder="Required for registration" required>
                </div>

                <button type="submit" name="register" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                    Register
                </button>
            </form>

            <div class="auth-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
                <p style="margin-top: 15px; font-size: 0.8rem;"><a href="index.php" style="color: var(--text-secondary);">← Back to Public Dashboard</a></p>
            </div>
        </div>
    </div>
</body>
</html>