<?php
require_once 'auth_check.php';
require_once 'db_connect.php';

// Load sample records if user clicks "Load Sample Records"
if (isset($_POST['load_sample_data'])) {
    if (ert_seed_sample_employees($conn)) {
        $_SESSION['flash_message'] = "Sample employee records loaded successfully!";
        $_SESSION['flash_type'] = "success";
    }
    header("Location: index.php");
    exit;
}

// Fetch Dashboard Summary Statistics
$stats = ert_get_stats($conn);

// Search and Filter Parameters (SELECT Operation)
$search = trim($_GET['search'] ?? '');
$dept_filter = trim($_GET['department'] ?? '');
$status_filter = trim($_GET['status'] ?? '');

$employees_rows = ert_get_employees($conn, $search, $dept_filter, $status_filter);

// Helper function for avatar initials
function getInitials($name) {
    $words = preg_split('/\s+/', trim($name));
    $initials = '';
    foreach (array_slice($words, 0, 2) as $w) {
        $initials .= strtoupper(mb_substr($w, 0, 1));
    }
    return $initials ?: 'EM';
}

// Helper function for status badge classes
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'Active': return 'badge-active';
        case 'On Leave': return 'badge-leave';
        case 'Probation': return 'badge-probation';
        case 'Resigned': return 'badge-resigned';
        default: return 'badge-resigned';
    }
}

$departments_list = [
    'Human Resources',
    'Information Technology',
    'Finance & Accounting',
    'Operations',
    'Marketing',
    'Sales & Customer Support',
    'Administration'
];
?>
<!DOCTYPE html>
<html lang="en" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Record Tracker | PHP &amp; MySQL CRUD</title>
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
                    <a class="nav-link active" href="index.php">
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

<!-- Main Container -->
<main class="container py-4">

    <!-- Flash Message Alert -->
    <?php if (!empty($_SESSION['flash_message'])): ?>
        <?php $is_err = ($_SESSION['flash_type'] ?? 'success') === 'danger'; ?>
        <div class="alert <?= $is_err ? 'alert-apple-danger' : 'alert-apple-success' ?> alert-dismissible fade show shadow-sm d-flex align-items-center mb-4" role="alert">
            <i class="bi <?= $is_err ? 'bi-exclamation-triangle-fill' : 'bi-check-circle-fill' ?> fs-5 me-2"></i>
            <div class="fw-semibold"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['flash_message'], $_SESSION['flash_type']); ?>
    <?php endif; ?>

    <!-- Black Segmented Status Tabs with Yellow Words -->
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="apple-tab-bar" role="tablist" aria-label="Employee Status Tabs">
            <?php
            $status_tabs = [
                '' => 'All Records',
                'Active' => 'Active',
                'On Leave' => 'On Leave',
                'Probation' => 'Probation',
                'Resigned' => 'Resigned'
            ];
            foreach ($status_tabs as $tab_val => $tab_label):
                $is_active_tab = ($status_filter === $tab_val);
                $query_params = array_filter([
                    'search' => $search,
                    'department' => $dept_filter,
                    'status' => $tab_val
                ], fn($v) => $v !== '');
                $tab_href = 'index.php' . (!empty($query_params) ? '?' . http_build_query($query_params) : '');
            ?>
                <a href="<?= htmlspecialchars($tab_href) ?>" class="apple-tab <?= $is_active_tab ? 'active' : '' ?>">
                    <?= htmlspecialchars($tab_label) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <a href="add_employee.php" class="btn btn-apple-yellow px-3 py-2">
            <i class="bi bi-plus-lg me-1"></i> New Employee Record
        </a>
    </div>

    <!-- Summary KPI Black Cards with Yellow Words -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-yellow p-3 h-100 d-flex align-items-center justify-content-between">
                <div>
                    <p class="eyebrow-label mb-1">Total Workforce</p>
                    <h3 class="display-stat mb-0"><?= (int)($stats['total_employees'] ?? 0) ?></h3>
                    <small>Across <?= (int)($stats['dept_count'] ?? 0) ?> departments</small>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-people-fill"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-yellow p-3 h-100 d-flex align-items-center justify-content-between">
                <div>
                    <p class="eyebrow-label mb-1">Active Personnel</p>
                    <h3 class="display-stat mb-0"><?= (int)($stats['active_count'] ?? 0) ?></h3>
                    <small>Currently on duty</small>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-person-check-fill"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-yellow p-3 h-100 d-flex align-items-center justify-content-between">
                <div>
                    <p class="eyebrow-label mb-1">Leave &amp; Probation</p>
                    <h3 class="display-stat mb-0"><?= (int)($stats['leave_probation_count'] ?? 0) ?></h3>
                    <small>Monitored status</small>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="stat-card stat-card-yellow p-3 h-100 d-flex align-items-center justify-content-between">
                <div>
                    <p class="eyebrow-label mb-1">Monthly Payroll</p>
                    <h3 class="display-stat mb-0">&#8369;<?= number_format((float)($stats['total_payroll'] ?? 0), 2) ?></h3>
                    <small>Active &amp; non-resigned</small>
                </div>
                <div class="stat-icon">
                    <i class="bi bi-cash-stack"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Black Card -->
    <div class="content-card p-3 p-md-4 mb-4">
        <form method="GET" action="index.php" class="row g-3 align-items-end">
            <div class="col-12 col-md-5">
                <label for="search" class="form-label">Search Directory</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-search"></i></span>
                    <input type="text" id="search" name="search" class="form-control" 
                           placeholder="Name, Employee ID, Email, or Role..." 
                           value="<?= htmlspecialchars($search) ?>">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-md-3">
                <label for="department" class="form-label">Department</label>
                <select id="department" name="department" class="form-select">
                    <option value="">All Departments</option>
                    <?php foreach ($departments_list as $dept): ?>
                        <option value="<?= htmlspecialchars($dept) ?>" <?= $dept_filter === $dept ? 'selected' : '' ?>>
                            <?= htmlspecialchars($dept) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-md-2">
                <label for="status" class="form-label">Status</label>
                <select id="status" name="status" class="form-select">
                    <option value="">All Statuses</option>
                    <?php foreach (['Active', 'On Leave', 'Probation', 'Resigned'] as $st): ?>
                        <option value="<?= $st ?>" <?= $status_filter === $st ? 'selected' : '' ?>><?= $st ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-apple-yellow w-100">
                    <i class="bi bi-sliders2 me-1"></i> Filter
                </button>
                <?php if ($search !== '' || $dept_filter !== '' || $status_filter !== ''): ?>
                    <a href="index.php" class="btn btn-apple-dark" title="Clear Filters">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Employee Records Black Table Card -->
    <div class="content-card overflow-hidden">
        <div class="p-3 p-md-4 border-bottom border-warning border-opacity-25 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h5 class="mb-0">Employee Roster</h5>
                <small>Showing <?= count($employees_rows) ?> employee record(s)</small>
            </div>
            <a href="add_employee.php" class="btn btn-apple-yellow btn-sm px-3 py-2">
                <i class="bi bi-plus-lg me-1"></i> Add Employee
            </a>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-employees mb-0">
                <thead>
                    <tr>
                        <th class="ps-4">Employee ID</th>
                        <th>Employee</th>
                        <th>Role &amp; Department</th>
                        <th>Phone</th>
                        <th>Monthly Salary</th>
                        <th>Hire Date</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($employees_rows) > 0): ?>
                        <?php foreach ($employees_rows as $row): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="emp-code-pill font-monospace">
                                        <?= htmlspecialchars($row['employee_code']) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="emp-avatar">
                                            <?= htmlspecialchars(getInitials($row['full_name'])) ?>
                                        </div>
                                        <div>
                                            <div class="fw-bold"><?= htmlspecialchars($row['full_name']) ?></div>
                                            <div class="small"><?= htmlspecialchars($row['email']) ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= htmlspecialchars($row['position']) ?></div>
                                    <div class="small">
                                        <i class="bi bi-building me-1"></i><?= htmlspecialchars($row['department']) ?>
                                    </div>
                                </td>
                                <td>
                                    <i class="bi bi-telephone me-1"></i><?= htmlspecialchars($row['phone']) ?>
                                </td>
                                <td class="fw-bold">
                                    &#8369;<?= number_format((float)$row['salary'], 2) ?>
                                </td>
                                <td class="small">
                                    <?= date('M d, Y', strtotime($row['hire_date'])) ?>
                                </td>
                                <td>
                                    <span class="badge-status <?= getStatusBadgeClass($row['status']) ?>">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </td>
                                <td class="text-end pe-4">
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Record Actions">
                                        <a href="view_employee.php?id=<?= (int)$row['id'] ?>" 
                                           class="btn btn-apple-dark" title="Inspect Profile">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="edit_employee.php?id=<?= (int)$row['id'] ?>" 
                                           class="btn btn-apple-dark" title="Edit Record">
                                            <i class="bi bi-pencil-square"></i>
                                        </a>
                                        <button type="button" 
                                                class="btn btn-apple-dark" 
                                                title="Delete Record"
                                                data-bs-toggle="modal" 
                                                data-bs-target="#deleteModal"
                                                data-emp-id="<?= (int)$row['id'] ?>"
                                                data-emp-name="<?= htmlspecialchars($row['full_name']) ?>"
                                                data-emp-code="<?= htmlspecialchars($row['employee_code']) ?>">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="py-3">
                                    <i class="bi bi-folder2-open display-5 d-block mb-2"></i>
                                    <h6 class="fw-bold">No Employee Records Found</h6>
                                    <p class="small mb-3">
                                        <?php if ($search !== '' || $dept_filter !== '' || $status_filter !== ''): ?>
                                            No records matched your current search or filter criteria.
                                        <?php else: ?>
                                            Your database table is currently empty. Add a new employee or load sample records below.
                                        <?php endif; ?>
                                    </p>
                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="add_employee.php" class="btn btn-apple-yellow btn-sm px-3">
                                            <i class="bi bi-person-plus-fill me-1"></i> Add First Employee
                                        </a>
                                        <?php if ((int)($stats['total_employees'] ?? 0) === 0): ?>
                                            <form method="POST" action="index.php" class="d-inline">
                                                <button type="submit" name="load_sample_data" class="btn btn-apple-dark btn-sm px-3">
                                                    <i class="bi bi-database-add me-1"></i> Load Sample Records
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Apple Fluid Delete Confirmation Sheet Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content modal-content-apple">
            <div class="sheet-grab-handle" title="Drag down to dismiss"></div>
            <div class="modal-header border-0 pb-1 modal-header-draggable">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="deleteModalLabel">
                    <span class="stat-icon" style="width:2.2rem;height:2.2rem;font-size:1rem;">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                    </span>
                    Delete Employee Record
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="delete_employee.php">
                <div class="modal-body px-4 py-3">
                    <input type="hidden" name="id" id="deleteEmpId">
                    <p class="small mb-3">
                        Permanently remove this employee from the roster? This action cannot be undone.
                    </p>
                    <div class="detail-tile">
                        <div class="fw-bold" id="deleteEmpName"></div>
                        <small class="font-monospace" id="deleteEmpCode"></small>
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

<!-- Footer -->
<footer class="footer-custom py-3 mt-5">
    <div class="container d-flex flex-column flex-sm-row justify-content-between align-items-center small">
        <div>
            <strong>Employee Record Tracker</strong> &mdash; Web Systems Technologies (PHP &amp; MySQL CRUD)
        </div>
        <div>
            Signed in as <strong><?= htmlspecialchars($_SESSION['ert_username'] ?? 'kristel') ?></strong> &bull; Apple Fluid Interface
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/apple-fluid.js?v=20261121"></script>
</body>
</html>
