<?php
require_once 'auth_check.php';
require_once 'db_connect.php';

$errors = [];

$employee_code = ert_next_employee_code($conn);
$full_name = "";
$email = "";
$phone = "";
$department = "";
$position = "";
$salary = "";
$hire_date = date('Y-m-d');
$status = "Active";

$departments_list = [
    'Human Resources',
    'Information Technology',
    'Finance & Accounting',
    'Operations',
    'Marketing',
    'Sales & Customer Support',
    'Administration'
];

// Handle Form Submission (INSERT Operation)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_code = trim($_POST['employee_code'] ?? '');
    $full_name     = trim($_POST['full_name'] ?? '');
    $email         = trim($_POST['email'] ?? '');
    $phone         = trim($_POST['phone'] ?? '');
    $department    = trim($_POST['department'] ?? '');
    $position      = trim($_POST['position'] ?? '');
    $salary        = trim($_POST['salary'] ?? '');
    $hire_date     = trim($_POST['hire_date'] ?? '');
    $status        = trim($_POST['status'] ?? 'Active');

    // Server-side Validation
    if ($employee_code === '') $errors[] = "Employee ID Code is required.";
    if ($full_name === '')     $errors[] = "Full Name is required.";
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "A valid Email Address is required.";
    }
    if ($phone === '')         $errors[] = "Contact Phone Number is required.";
    if ($department === '')    $errors[] = "Department is required.";
    if ($position === '')      $errors[] = "Job Position / Title is required.";
    if ($salary === '' || !is_numeric($salary) || (float)$salary < 0) {
        $errors[] = "A valid Monthly Salary is required.";
    }
    if ($hire_date === '')     $errors[] = "Hire Date is required.";

    // Check if employee_code already exists
    if (empty($errors) && ert_code_exists($conn, $employee_code, 0)) {
        $errors[] = "Employee ID Code '$employee_code' is already assigned to another employee.";
    }

    // Execute INSERT
    if (empty($errors)) {
        $inserted = ert_insert_employee($conn, [
            'employee_code' => $employee_code,
            'full_name'     => $full_name,
            'email'         => $email,
            'phone'         => $phone,
            'department'    => $department,
            'position'      => $position,
            'salary'        => (float)$salary,
            'hire_date'     => $hire_date,
            'status'        => $status
        ]);

        if ($inserted) {
            $_SESSION['flash_message'] = "Employee record for \"$full_name\" ($employee_code) has been added successfully!";
            $_SESSION['flash_type'] = "success";
            header("Location: index.php");
            exit;
        } else {
            $errors[] = "Could not save employee record. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Employee Record | Employee Record Tracker</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <?php ert_render_apple_theme_css(); ?>
</head>
<body>

<!-- Black Translucent Navigation Bar with Black Tabs & Yellow Words -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-brand-custom py-3 sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center gap-3 fw-bold" href="index.php">
            <span class="brand-icon-box">
                <i class="bi bi-people-fill"></i>
            </span>
            <div>
                <span class="d-block lh-1" style="color: var(--apple-yellow-heading); letter-spacing: -0.02em;">Employee Record Tracker</span>
                <small class="fw-medium" style="font-size: 0.72rem; color: var(--apple-yellow-text);">Personnel &amp; Payroll Directory</small>
            </div>
        </a>
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-2 mt-3 mt-lg-0">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="bi bi-grid-1x2-fill me-1"></i> Directory Tab
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="add_employee.php">
                        <i class="bi bi-person-plus-fill me-1"></i> Add Employee Tab
                    </a>
                </li>
                <li class="nav-item ms-lg-2">
                    <span class="emp-code-pill d-inline-flex align-items-center gap-1 py-2 px-3">
                        <i class="bi bi-person-badge-fill"></i> <?= htmlspecialchars($_SESSION['ert_username'] ?? 'kristel') ?>
                    </span>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="logout.php" title="Sign Out">
                        <i class="bi bi-box-arrow-right me-1"></i> Sign Out
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<main class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">

            <!-- Wayfinding Breadcrumb -->
            <nav aria-label="breadcrumb" class="mb-3">
                <ol class="breadcrumb small mb-0">
                    <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none fw-bold" style="color: var(--apple-yellow-heading);">Directory</a></li>
                    <li class="breadcrumb-item active" aria-current="page" style="color: var(--apple-yellow-text);">New Employee Record</li>
                </ol>
            </nav>

            <div class="content-card p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 pb-3 mb-4 border-bottom border-warning border-opacity-25">
                    <div class="stat-icon">
                        <i class="bi bi-person-plus-fill"></i>
                    </div>
                    <div>
                        <h4 class="mb-0">New Employee Record</h4>
                        <p class="small mb-0">Enter personnel and compensation details to insert into the roster.</p>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-apple-danger shadow-sm mb-4">
                        <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-octagon-fill me-2"></i>Please review the highlighted fields:</h6>
                        <ul class="mb-0 small">
                            <?php foreach ($errors as $err): ?>
                                <li><?= htmlspecialchars($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="add_employee.php" class="row g-3">
                    <div class="col-12 col-md-4">
                        <label for="employee_code" class="form-label">Employee ID Code *</label>
                        <input type="text" id="employee_code" name="employee_code" class="form-control font-monospace" 
                               value="<?= htmlspecialchars($employee_code) ?>" placeholder="e.g. EMP-2026-001" required>
                    </div>

                    <div class="col-12 col-md-8">
                        <label for="full_name" class="form-label">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" class="form-control" 
                               value="<?= htmlspecialchars($full_name) ?>" placeholder="Enter employee's full name" required>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="email" class="form-label">Work Email Address *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" id="email" name="email" class="form-control" 
                                   value="<?= htmlspecialchars($email) ?>" placeholder="name@company.ph" required>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="phone" class="form-label">Contact Phone Number *</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-telephone"></i></span>
                            <input type="text" id="phone" name="phone" class="form-control" 
                                   value="<?= htmlspecialchars($phone) ?>" placeholder="09XX-XXX-XXXX" required>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="department" class="form-label">Department *</label>
                        <select id="department" name="department" class="form-select" required>
                            <option value="" disabled <?= $department === '' ? 'selected' : '' ?>>Select Department...</option>
                            <?php foreach ($departments_list as $dept): ?>
                                <option value="<?= htmlspecialchars($dept) ?>" <?= $department === $dept ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($dept) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 col-md-6">
                        <label for="position" class="form-label">Job Position / Title *</label>
                        <input type="text" id="position" name="position" class="form-control" 
                               value="<?= htmlspecialchars($position) ?>" placeholder="e.g. Senior Engineer, HR Officer" required>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="salary" class="form-label">Monthly Salary (PHP) *</label>
                        <div class="input-group">
                            <span class="input-group-text fw-semibold">&#8369;</span>
                            <input type="number" step="0.01" min="0" id="salary" name="salary" class="form-control" 
                                   value="<?= htmlspecialchars($salary) ?>" placeholder="0.00" required>
                        </div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="hire_date" class="form-label">Date Hired *</label>
                        <input type="date" id="hire_date" name="hire_date" class="form-control" 
                               value="<?= htmlspecialchars($hire_date) ?>" required>
                    </div>

                    <div class="col-12 col-md-4">
                        <label for="status" class="form-label">Employment Status *</label>
                        <select id="status" name="status" class="form-select" required>
                            <?php foreach (['Active', 'On Leave', 'Probation', 'Resigned'] as $st): ?>
                                <option value="<?= $st ?>" <?= $status === $st ? 'selected' : '' ?>><?= $st ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12 pt-3 border-top border-warning border-opacity-25 mt-4 d-flex justify-content-end gap-2">
                        <a href="index.php" class="btn btn-apple-dark px-4">Cancel</a>
                        <button type="submit" class="btn btn-apple-yellow px-4">
                            <i class="bi bi-check2-circle me-1"></i> Save Employee Record
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</main>

<footer class="footer-custom py-3 mt-5">
    <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center small">
        <div><strong>Employee Record Tracker</strong> &mdash; Web Systems Technologies</div>
        <div>Signed in as <strong><?= htmlspecialchars($_SESSION['ert_username'] ?? 'kristel') ?></strong> &bull; Apple Fluid Interface</div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/apple-fluid.js?v=20261121"></script>
</body>
</html>
