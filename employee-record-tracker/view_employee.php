<?php
require_once 'auth_check.php';
require_once 'db_connect.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['flash_message'] = "Invalid employee record ID.";
    $_SESSION['flash_type'] = "danger";
    header("Location: index.php");
    exit;
}

// Fetch single employee record (SELECT Operation)
$employee = ert_get_employee_by_id($conn, $id);

if (!$employee) {
    $_SESSION['flash_message'] = "Employee record not found.";
    $_SESSION['flash_type'] = "danger";
    header("Location: index.php");
    exit;
}

function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Active': return 'badge-active';
        case 'On Leave': return 'badge-leave';
        case 'Probation': return 'badge-probation';
        case 'Resigned': return 'badge-resigned';
        default: return 'badge-resigned';
    }
}

// Compute tenure
$hire_dt = new DateTime($employee['hire_date']);
$now_dt  = new DateTime();
$diff    = $hire_dt->diff($now_dt);
$tenure_parts = [];
if ($diff->y > 0) $tenure_parts[] = $diff->y . ' yr' . ($diff->y > 1 ? 's' : '');
if ($diff->m > 0) $tenure_parts[] = $diff->m . ' mo' . ($diff->m > 1 ? 's' : '');
$tenure_str = !empty($tenure_parts) ? implode(', ', $tenure_parts) : 'Less than a month';
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($employee['full_name']) ?> | Employee Record Tracker</title>
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
                    <a class="nav-link" href="add_employee.php">
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
                    <li class="breadcrumb-item active" aria-current="page" style="color: var(--apple-yellow-text);">Employee Profile (<?= htmlspecialchars($employee['employee_code']) ?>)</li>
                </ol>
            </nav>

            <div class="content-card overflow-hidden">
                <!-- Profile Header Banner -->
                <div class="p-4 p-md-5 border-bottom border-warning border-opacity-25 d-flex flex-wrap justify-content-between align-items-center gap-3"
                     style="background: rgba(0, 0, 0, 0.45);">
                    <div class="d-flex align-items-center gap-3">
                        <div class="emp-avatar" style="width: 4rem; height: 4rem; font-size: 1.35rem;">
                            <?php
                            $words = preg_split('/\s+/', trim($employee['full_name']));
                            $initials = '';
                            foreach (array_slice($words, 0, 2) as $w) {
                                $initials .= strtoupper(mb_substr($w, 0, 1));
                            }
                            echo htmlspecialchars($initials ?: 'EM');
                            ?>
                        </div>
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <h3 class="mb-0"><?= htmlspecialchars($employee['full_name']) ?></h3>
                                <span class="badge-status <?= getStatusBadgeClass($employee['status']) ?>">
                                    <?= htmlspecialchars($employee['status']) ?>
                                </span>
                            </div>
                            <p class="mb-2">
                                <?= htmlspecialchars($employee['position']) ?> &bull; <strong><?= htmlspecialchars($employee['department']) ?></strong>
                            </p>
                            <span class="emp-code-pill font-monospace">
                                ID: <?= htmlspecialchars($employee['employee_code']) ?>
                            </span>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="edit_employee.php?id=<?= (int)$employee['id'] ?>" class="btn btn-apple-yellow btn-sm px-3 py-2">
                            <i class="bi bi-pencil-square me-1"></i> Edit Record
                        </a>
                        <button type="button" class="btn btn-apple-dark btn-sm px-3 py-2" data-bs-toggle="modal" data-bs-target="#deleteModal">
                            <i class="bi bi-trash3 me-1"></i> Delete
                        </button>
                    </div>
                </div>

                <!-- Detailed Fields Grid -->
                <div class="p-4 p-md-5">
                    <p class="eyebrow-label mb-3">Employment &amp; Contact Details</p>
                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <div class="detail-tile h-100">
                                <small class="d-block mb-1">
                                    <i class="bi bi-envelope me-1"></i>Work Email Address
                                </small>
                                <div class="fw-bold"><?= htmlspecialchars($employee['email']) ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="detail-tile h-100">
                                <small class="d-block mb-1">
                                    <i class="bi bi-telephone me-1"></i>Contact Phone Number
                                </small>
                                <div class="fw-bold"><?= htmlspecialchars($employee['phone']) ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="detail-tile h-100">
                                <small class="d-block mb-1">
                                    <i class="bi bi-building me-1"></i>Assigned Department
                                </small>
                                <div class="fw-bold"><?= htmlspecialchars($employee['department']) ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-6">
                            <div class="detail-tile h-100">
                                <small class="d-block mb-1">
                                    <i class="bi bi-briefcase me-1"></i>Job Position / Title
                                </small>
                                <div class="fw-bold"><?= htmlspecialchars($employee['position']) ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="detail-tile h-100">
                                <small class="d-block mb-1">
                                    <i class="bi bi-cash-coin me-1"></i>Monthly Salary
                                </small>
                                <div class="fw-bold fs-5">&#8369;<?= number_format((float)$employee['salary'], 2) ?></div>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="detail-tile h-100">
                                <small class="d-block mb-1">
                                    <i class="bi bi-calendar-event me-1"></i>Hire Date
                                </small>
                                <div class="fw-bold"><?= date('F d, Y', strtotime($employee['hire_date'])) ?></div>
                                <small>Tenure: <?= htmlspecialchars($tenure_str) ?></small>
                            </div>
                        </div>
                        <div class="col-12 col-sm-4">
                            <div class="detail-tile h-100">
                                <small class="d-block mb-1">
                                    <i class="bi bi-clock me-1"></i>Record Created
                                </small>
                                <div class="fw-bold"><?= date('M d, Y', strtotime($employee['created_at'] ?? 'now')) ?></div>
                                <small>Primary Key #<?= (int)$employee['id'] ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<!-- Apple Fluid Delete Confirmation Sheet Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-apple">
            <div class="sheet-grab-handle" title="Drag down to dismiss"></div>
            <div class="modal-header border-0 pb-1 modal-header-draggable">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    <span class="stat-icon" style="width:2.2rem;height:2.2rem;font-size:1rem;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </span>
                    Delete Employee Record
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="delete_employee.php">
                <div class="modal-body px-4 py-3">
                    <input type="hidden" name="id" value="<?= (int)$employee['id'] ?>">
                    <p class="small mb-3">
                        Permanently remove this employee from the roster? This action cannot be undone.
                    </p>
                    <div class="detail-tile">
                        <div class="fw-bold"><?= htmlspecialchars($employee['full_name']) ?></div>
                        <small class="font-monospace">Employee ID: <?= htmlspecialchars($employee['employee_code']) ?></small>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-1 px-4 pb-4">
                    <button type="button" class="btn btn-apple-dark px-3" data-bs-dismiss="modal">Keep Record</button>
                    <button type="submit" class="btn btn-apple-yellow px-4">
                        <i class="bi bi-trash3-fill me-1"></i> Confirm Delete
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
