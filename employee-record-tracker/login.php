<?php
/**
 * Login Page (login.php)
 * Credentials: username = kristel | password = stelacy16
 */
require_once 'db_connect.php';

// Redirect if already signed in
if (!empty($_SESSION['ert_logged_in']) && $_SESSION['ert_logged_in'] === true) {
    header("Location: index.php");
    exit;
}

$error = "";
$username_val = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username_val = trim($_POST['username'] ?? '');
    $password_val = $_POST['password'] ?? '';

    if ($username_val === '' || $password_val === '') {
        $error = "Please enter both your username and password.";
    } else {
        $authenticated = false;

        // Primary required credentials: kristel / stelacy16
        if (strtolower($username_val) === 'kristel' && $password_val === 'stelacy16') {
            $authenticated = true;
            $username_val = 'kristel';
        } elseif ($conn) {
            // Optional database users table lookup fallback
            $stmt = @mysqli_prepare($conn, "SELECT username, password FROM users WHERE LOWER(username) = LOWER(?) LIMIT 1");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $username_val);
                mysqli_stmt_execute($stmt);
                $res = mysqli_stmt_get_result($stmt);
                if ($row = mysqli_fetch_assoc($res)) {
                    if ($password_val === $row['password'] || password_verify($password_val, $row['password'])) {
                        $authenticated = true;
                        $username_val = $row['username'];
                    }
                }
            }
        }

        if ($authenticated) {
            session_regenerate_id(true);
            $_SESSION['ert_logged_in'] = true;
            $_SESSION['ert_username'] = $username_val;
            $_SESSION['flash_message'] = "Welcome back, " . ucfirst($username_val) . "! You are now signed in.";
            $_SESSION['flash_type'] = "success";
            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid username or password. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Employee Record Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <?php ert_render_apple_theme_css(); ?>
</head>
<body class="d-flex flex-column justify-content-center align-items-center min-vh-100 py-4">

<main class="container">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-7 col-lg-5 col-xl-4">

            <!-- Black Translucent Apple Sign-In Card on Red Background -->
            <div class="content-card p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="brand-icon-box mx-auto mb-3" style="width: 3.5rem; height: 3.5rem; font-size: 1.5rem; border-radius: 1rem;">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <h3 class="mb-1">Employee Record Tracker</h3>
                    <p class="small mb-0">
                        Sign in to manage employee records
                    </p>
                </div>

                <?php if (isset($_GET['logged_out'])): ?>
                    <div class="alert alert-apple-success small py-2 px-3 mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>You have signed out safely.</span>
                    </div>
                <?php endif; ?>

                <?php if ($error !== ''): ?>
                    <div class="alert alert-apple-danger small py-2 px-3 mb-3 d-flex align-items-center gap-2">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <span><?= htmlspecialchars($error) ?></span>
                    </div>
                <?php endif; ?>

                <form method="POST" action="login.php" autocomplete="on">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                            <input type="text" id="username" name="username" class="form-control" 
                                   placeholder="Enter username" 
                                   value="<?= htmlspecialchars($username_val) ?>" 
                                   required autofocus>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                            <input type="password" id="password" name="password" class="form-control" 
                                   placeholder="Enter password" required>
                            <button type="button" class="btn btn-apple-dark" id="togglePwdBtn" title="Show/Hide Password" aria-label="Toggle password visibility">
                                <i class="bi bi-eye" id="togglePwdIcon"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-apple-yellow w-100 py-2 fw-bold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In to System
                    </button>
                </form>

                <div class="mt-4 pt-3 border-top border-warning border-opacity-25 text-center">
                    <small class="d-block">
                        Authorized HR &amp; Administration Portal
                    </small>
                </div>
            </div>

        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/apple-fluid.js?v=20261121"></script>
<script>
const toggleBtn = document.getElementById('togglePwdBtn');
const pwdInput = document.getElementById('password');
const pwdIcon = document.getElementById('togglePwdIcon');
if (toggleBtn && pwdInput) {
    toggleBtn.addEventListener('click', () => {
        const isPwd = pwdInput.type === 'password';
        pwdInput.type = isPwd ? 'text' : 'password';
        pwdIcon.className = isPwd ? 'bi bi-eye-slash' : 'bi bi-eye';
    });
}
</script>
</body>
</html>
