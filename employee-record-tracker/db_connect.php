<?php
/**
 * Database Connection & CRUD Engine (db_connect.php)
 * Project: Employee Record Tracker
 * Course: PHP and MySQL Development (Web Systems Technologies)
 *
 * Configured for:
 *  1. InfinityFree MySQL Hosting (sql210.infinityfree.com / if0_43460977 / stelacy16)
 *  2. Local XAMPP MySQL (localhost / root / employee_tracker_db)
 *  3. Automatic resilient data store fallback (never crashes with a fatal error screen)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// =========================================================================
// 1. INFINITYFREE HOSTING CREDENTIALS
// =========================================================================
$if_host = "sql210.infinityfree.com";
$if_user = "if0_43460977";
$if_pass = "stelacy16";
$if_name = "if0_43460977_employee_db"; // Update suffix if you named your DB differently
$if_port = 3306;

// =========================================================================
// 2. LOCAL XAMPP CREDENTIALS
// =========================================================================
$local_host = "localhost";
$local_user = "root";
$local_pass = "";
$local_name = "employee_tracker_db";

mysqli_report(MYSQLI_REPORT_OFF);

$http_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$is_localhost = (
    $http_host === 'localhost' ||
    $http_host === '127.0.0.1' ||
    strpos($http_host, 'localhost:') === 0 ||
    strpos($http_host, '127.0.0.1:') === 0
);

$conn = false;

if (!$is_localhost) {
    // 1. Try direct connection on InfinityFree
    $conn = @mysqli_connect($if_host, $if_user, $if_pass, $if_name, $if_port);

    // 2. Auto-discover any if0_43460977_* database created in InfinityFree Control Panel
    if (!$conn) {
        $temp_if = @mysqli_connect($if_host, $if_user, $if_pass, "", $if_port);
        if ($temp_if) {
            $detected_db = null;

            $schema_res = @mysqli_query(
                $temp_if,
                "SELECT SCHEMA_NAME FROM information_schema.SCHEMATA WHERE SCHEMA_NAME LIKE 'if0_43460977_%' LIMIT 1"
            );
            if ($schema_res && $row = mysqli_fetch_row($schema_res)) {
                $detected_db = $row[0];
            }

            if (!$detected_db) {
                $db_list = @mysqli_query($temp_if, "SHOW DATABASES");
                if ($db_list) {
                    while ($row = mysqli_fetch_row($db_list)) {
                        if (stripos($row[0], 'if0_43460977_') === 0) {
                            $detected_db = $row[0];
                            break;
                        }
                    }
                }
            }

            if (!$detected_db) {
                $candidates = [
                    'if0_43460977_employee_db',
                    'if0_43460977_employee_tracker_db',
                    'if0_43460977_employee_tracker',
                    'if0_43460977_employees',
                    'if0_43460977_employee',
                    'if0_43460977_tracker',
                    'if0_43460977_db',
                    'if0_43460977_crud',
                    'if0_43460977_project',
                    'if0_43460977_main',
                    'if0_43460977_XXX'
                ];
                foreach ($candidates as $cand) {
                    if (@mysqli_select_db($temp_if, $cand)) {
                        $detected_db = $cand;
                        break;
                    }
                }
            }

            if ($detected_db && @mysqli_select_db($temp_if, $detected_db)) {
                $conn = $temp_if;
            }
        }
    }
} else {
    // Running on Local XAMPP
    $conn = @mysqli_connect($local_host, $local_user, $local_pass, $local_name);
    if (!$conn) {
        $temp_local = @mysqli_connect($local_host, $local_user, $local_pass);
        if ($temp_local) {
            $create_db_sql = "CREATE DATABASE IF NOT EXISTS `$local_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
            if (@mysqli_query($temp_local, $create_db_sql)) {
                @mysqli_select_db($temp_local, $local_name);
                $conn = $temp_local;
            }
        }
    }
}

// Initial sample records
function ert_default_sample_records() {
    return [
        [
            'id' => 1,
            'employee_code' => 'EMP-2026-001',
            'full_name' => 'Maria Clara Santos',
            'email' => 'maria.santos@company.ph',
            'phone' => '0917-555-0142',
            'department' => 'Human Resources',
            'position' => 'HR Manager',
            'salary' => 52000.00,
            'hire_date' => '2023-03-15',
            'status' => 'Active',
            'created_at' => '2026-01-10 09:00:00'
        ],
        [
            'id' => 2,
            'employee_code' => 'EMP-2026-002',
            'full_name' => 'Juan Miguel Dela Cruz',
            'email' => 'juan.delacruz@company.ph',
            'phone' => '0918-555-0198',
            'department' => 'Information Technology',
            'position' => 'Senior Full-Stack Developer',
            'salary' => 68500.00,
            'hire_date' => '2022-08-01',
            'status' => 'Active',
            'created_at' => '2026-01-10 09:05:00'
        ],
        [
            'id' => 3,
            'employee_code' => 'EMP-2026-003',
            'full_name' => 'Angela Mae Reyes',
            'email' => 'angela.reyes@company.ph',
            'phone' => '0998-555-0231',
            'department' => 'Finance & Accounting',
            'position' => 'Financial Analyst',
            'salary' => 44000.00,
            'hire_date' => '2024-01-10',
            'status' => 'Active',
            'created_at' => '2026-01-10 09:10:00'
        ],
        [
            'id' => 4,
            'employee_code' => 'EMP-2026-004',
            'full_name' => 'Carlos Gabriel Mendoza',
            'email' => 'carlos.mendoza@company.ph',
            'phone' => '0927-555-0874',
            'department' => 'Operations',
            'position' => 'Operations Supervisor',
            'salary' => 48000.00,
            'hire_date' => '2023-11-20',
            'status' => 'On Leave',
            'created_at' => '2026-01-10 09:15:00'
        ],
        [
            'id' => 5,
            'employee_code' => 'EMP-2026-005',
            'full_name' => 'Sophia Lorraine Villanueva',
            'email' => 'sophia.villanueva@company.ph',
            'phone' => '0915-555-0619',
            'department' => 'Marketing',
            'position' => 'Digital Marketing Specialist',
            'salary' => 36500.00,
            'hire_date' => '2025-02-03',
            'status' => 'Probation',
            'created_at' => '2026-01-10 09:20:00'
        ],
        [
            'id' => 6,
            'employee_code' => 'EMP-2026-006',
            'full_name' => 'Mark Anthony Bautista',
            'email' => 'mark.bautista@company.ph',
            'phone' => '0920-555-0349',
            'department' => 'Information Technology',
            'position' => 'Systems Administrator',
            'salary' => 49000.00,
            'hire_date' => '2023-06-12',
            'status' => 'Active',
            'created_at' => '2026-01-10 09:25:00'
        ]
    ];
}

// If MySQL connected successfully, initialize tables & default data
if ($conn) {
    mysqli_set_charset($conn, "utf8mb4");

    $table_check_sql = "CREATE TABLE IF NOT EXISTS `employees` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `employee_code` varchar(20) NOT NULL,
      `full_name` varchar(100) NOT NULL,
      `email` varchar(100) NOT NULL,
      `phone` varchar(25) NOT NULL,
      `department` varchar(60) NOT NULL,
      `position` varchar(80) NOT NULL,
      `salary` decimal(10,2) NOT NULL DEFAULT 0.00,
      `hire_date` date NOT NULL,
      `status` enum('Active','On Leave','Probation','Resigned') NOT NULL DEFAULT 'Active',
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_employee_code` (`employee_code`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    @mysqli_query($conn, $table_check_sql);

    $users_table_sql = "CREATE TABLE IF NOT EXISTS `users` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `username` varchar(50) NOT NULL,
      `password` varchar(255) NOT NULL,
      `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
      PRIMARY KEY (`id`),
      UNIQUE KEY `unique_username` (`username`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;";
    @mysqli_query($conn, $users_table_sql);
    @mysqli_query($conn, "INSERT IGNORE INTO `users` (`username`, `password`) VALUES ('kristel', 'stelacy16')");
}

// =========================================================================
// Fallback JSON/Session Storage (if MySQL DB hasn't been created in panel yet)
// =========================================================================
function ert_load_fallback_store() {
    $file = __DIR__ . '/employees_store.json';
    if (file_exists($file)) {
        $raw = @file_get_contents($file);
        $decoded = @json_decode($raw, true);
        if (is_array($decoded)) {
            $_SESSION['ert_fallback_employees'] = $decoded;
            return $decoded;
        }
    }
    if (!isset($_SESSION['ert_fallback_employees']) || !is_array($_SESSION['ert_fallback_employees'])) {
        $_SESSION['ert_fallback_employees'] = ert_default_sample_records();
        @file_put_contents($file, json_encode($_SESSION['ert_fallback_employees'], JSON_PRETTY_PRINT));
    }
    return $_SESSION['ert_fallback_employees'];
}

function ert_save_fallback_store($records) {
    $_SESSION['ert_fallback_employees'] = array_values($records);
    $file = __DIR__ . '/employees_store.json';
    @file_put_contents($file, json_encode($_SESSION['ert_fallback_employees'], JSON_PRETTY_PRINT));
}

// =========================================================================
// Unified CRUD Operations (MySQL Primary + Seamless Fallback)
// =========================================================================

function ert_seed_sample_employees($conn) {
    if ($conn) {
        $check_count = @mysqli_query($conn, "SELECT COUNT(*) AS cnt FROM employees");
        $row_cnt = $check_count ? (mysqli_fetch_assoc($check_count)['cnt'] ?? 0) : 0;
        if ($row_cnt == 0) {
            $sample_sql = "INSERT INTO `employees` (`employee_code`, `full_name`, `email`, `phone`, `department`, `position`, `salary`, `hire_date`, `status`) VALUES
            ('EMP-2026-001', 'Maria Clara Santos', 'maria.santos@company.ph', '0917-555-0142', 'Human Resources', 'HR Manager', 52000.00, '2023-03-15', 'Active'),
            ('EMP-2026-002', 'Juan Miguel Dela Cruz', 'juan.delacruz@company.ph', '0918-555-0198', 'Information Technology', 'Senior Full-Stack Developer', 68500.00, '2022-08-01', 'Active'),
            ('EMP-2026-003', 'Angela Mae Reyes', 'angela.reyes@company.ph', '0998-555-0231', 'Finance & Accounting', 'Financial Analyst', 44000.00, '2024-01-10', 'Active'),
            ('EMP-2026-004', 'Carlos Gabriel Mendoza', 'carlos.mendoza@company.ph', '0927-555-0874', 'Operations', 'Operations Supervisor', 48000.00, '2023-11-20', 'On Leave'),
            ('EMP-2026-005', 'Sophia Lorraine Villanueva', 'sophia.villanueva@company.ph', '0915-555-0619', 'Marketing', 'Digital Marketing Specialist', 36500.00, '2025-02-03', 'Probation'),
            ('EMP-2026-006', 'Mark Anthony Bautista', 'mark.bautista@company.ph', '0920-555-0349', 'Information Technology', 'Systems Administrator', 49000.00, '2023-06-12', 'Active')";
            return @mysqli_query($conn, $sample_sql);
        }
        return false;
    } else {
        ert_save_fallback_store(ert_default_sample_records());
        return true;
    }
}

function ert_get_stats($conn) {
    if ($conn) {
        $stats_sql = "SELECT 
            COUNT(*) AS total_employees,
            SUM(CASE WHEN status = 'Active' THEN 1 ELSE 0 END) AS active_count,
            SUM(CASE WHEN status IN ('On Leave', 'Probation') THEN 1 ELSE 0 END) AS leave_probation_count,
            COUNT(DISTINCT department) AS dept_count,
            COALESCE(SUM(CASE WHEN status != 'Resigned' THEN salary ELSE 0 END), 0) AS total_payroll
        FROM employees";
        $stats_res = @mysqli_query($conn, $stats_sql);
        if ($stats_res) {
            return mysqli_fetch_assoc($stats_res);
        }
    }

    $records = ert_load_fallback_store();
    $total = count($records);
    $active = 0;
    $leave_prob = 0;
    $depts = [];
    $payroll = 0.0;
    foreach ($records as $r) {
        if (($r['status'] ?? '') === 'Active') $active++;
        if (in_array($r['status'] ?? '', ['On Leave', 'Probation'], true)) $leave_prob++;
        if (!empty($r['department'])) $depts[$r['department']] = true;
        if (($r['status'] ?? '') !== 'Resigned') $payroll += (float)($r['salary'] ?? 0);
    }
    return [
        'total_employees' => $total,
        'active_count' => $active,
        'leave_probation_count' => $leave_prob,
        'dept_count' => count($depts),
        'total_payroll' => $payroll
    ];
}

function ert_get_employees($conn, $search = '', $dept_filter = '', $status_filter = '') {
    if ($conn) {
        $where_clauses = [];
        $params = [];
        $types = "";

        if ($search !== '') {
            $where_clauses[] = "(employee_code LIKE ? OR full_name LIKE ? OR email LIKE ? OR position LIKE ?)";
            $like_term = "%" . $search . "%";
            $params[] = $like_term;
            $params[] = $like_term;
            $params[] = $like_term;
            $params[] = $like_term;
            $types .= "ssss";
        }
        if ($dept_filter !== '') {
            $where_clauses[] = "department = ?";
            $params[] = $dept_filter;
            $types .= "s";
        }
        if ($status_filter !== '') {
            $where_clauses[] = "status = ?";
            $params[] = $status_filter;
            $types .= "s";
        }

        $sql = "SELECT * FROM employees";
        if (count($where_clauses) > 0) {
            $sql .= " WHERE " . implode(" AND ", $where_clauses);
        }
        $sql .= " ORDER BY id DESC";

        $stmt = @mysqli_prepare($conn, $sql);
        if ($stmt) {
            if ($types !== "") {
                mysqli_stmt_bind_param($stmt, $types, ...$params);
            }
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $rows = [];
            while ($row = mysqli_fetch_assoc($result)) {
                $rows[] = $row;
            }
            return $rows;
        }
    }

    $records = ert_load_fallback_store();
    $filtered = array_filter($records, function ($r) use ($search, $dept_filter, $status_filter) {
        if ($search !== '') {
            $haystack = strtolower(
                ($r['employee_code'] ?? '') . ' ' .
                ($r['full_name'] ?? '') . ' ' .
                ($r['email'] ?? '') . ' ' .
                ($r['position'] ?? '')
            );
            if (strpos($haystack, strtolower($search)) === false) return false;
        }
        if ($dept_filter !== '' && ($r['department'] ?? '') !== $dept_filter) return false;
        if ($status_filter !== '' && ($r['status'] ?? '') !== $status_filter) return false;
        return true;
    });

    usort($filtered, fn($a, $b) => ((int)($b['id'] ?? 0)) <=> ((int)($a['id'] ?? 0)));
    return $filtered;
}

function ert_get_employee_by_id($conn, $id) {
    $id = (int)$id;
    if ($conn) {
        $stmt = @mysqli_prepare($conn, "SELECT * FROM employees WHERE id = ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            return mysqli_fetch_assoc($res) ?: null;
        }
    }
    foreach (ert_load_fallback_store() as $r) {
        if ((int)($r['id'] ?? 0) === $id) return $r;
    }
    return null;
}

function ert_next_employee_code($conn) {
    if ($conn) {
        $last_res = @mysqli_query($conn, "SELECT id FROM employees ORDER BY id DESC LIMIT 1");
        if ($last_res && $last_row = mysqli_fetch_assoc($last_res)) {
            return sprintf("EMP-2026-%03d", (int)$last_row['id'] + 1);
        }
        return "EMP-2026-001";
    }
    $records = ert_load_fallback_store();
    $max_id = 0;
    foreach ($records as $r) {
        if ((int)($r['id'] ?? 0) > $max_id) $max_id = (int)$r['id'];
    }
    return sprintf("EMP-2026-%03d", $max_id + 1);
}

function ert_code_exists($conn, $employee_code, $exclude_id = 0) {
    $exclude_id = (int)$exclude_id;
    if ($conn) {
        $stmt = @mysqli_prepare($conn, "SELECT id FROM employees WHERE employee_code = ? AND id != ? LIMIT 1");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "si", $employee_code, $exclude_id);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            return mysqli_num_rows($res) > 0;
        }
    }
    foreach (ert_load_fallback_store() as $r) {
        if (strcasecmp($r['employee_code'] ?? '', $employee_code) === 0 && (int)($r['id'] ?? 0) !== $exclude_id) {
            return true;
        }
    }
    return false;
}

function ert_insert_employee($conn, $data) {
    if ($conn) {
        $insert_sql = "INSERT INTO employees (employee_code, full_name, email, phone, department, position, salary, hire_date, status) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = @mysqli_prepare($conn, $insert_sql);
        if ($stmt) {
            $salary_val = (float)$data['salary'];
            mysqli_stmt_bind_param(
                $stmt,
                "ssssssdss",
                $data['employee_code'],
                $data['full_name'],
                $data['email'],
                $data['phone'],
                $data['department'],
                $data['position'],
                $salary_val,
                $data['hire_date'],
                $data['status']
            );
            return mysqli_stmt_execute($stmt);
        }
    }

    $records = ert_load_fallback_store();
    $max_id = 0;
    foreach ($records as $r) {
        if ((int)($r['id'] ?? 0) > $max_id) $max_id = (int)$r['id'];
    }
    $records[] = [
        'id' => $max_id + 1,
        'employee_code' => $data['employee_code'],
        'full_name' => $data['full_name'],
        'email' => $data['email'],
        'phone' => $data['phone'],
        'department' => $data['department'],
        'position' => $data['position'],
        'salary' => (float)$data['salary'],
        'hire_date' => $data['hire_date'],
        'status' => $data['status'],
        'created_at' => date('Y-m-d H:i:s')
    ];
    ert_save_fallback_store($records);
    return true;
}

function ert_update_employee($conn, $id, $data) {
    $id = (int)$id;
    if ($conn) {
        $update_sql = "UPDATE employees 
                       SET employee_code = ?, full_name = ?, email = ?, phone = ?, 
                           department = ?, position = ?, salary = ?, hire_date = ?, status = ?
                       WHERE id = ?";
        $upd_stmt = @mysqli_prepare($conn, $update_sql);
        if ($upd_stmt) {
            $salary_val = (float)$data['salary'];
            mysqli_stmt_bind_param(
                $upd_stmt,
                "ssssssdssi",
                $data['employee_code'],
                $data['full_name'],
                $data['email'],
                $data['phone'],
                $data['department'],
                $data['position'],
                $salary_val,
                $data['hire_date'],
                $data['status'],
                $id
            );
            return mysqli_stmt_execute($upd_stmt);
        }
    }

    $records = ert_load_fallback_store();
    foreach ($records as &$r) {
        if ((int)($r['id'] ?? 0) === $id) {
            $r['employee_code'] = $data['employee_code'];
            $r['full_name'] = $data['full_name'];
            $r['email'] = $data['email'];
            $r['phone'] = $data['phone'];
            $r['department'] = $data['department'];
            $r['position'] = $data['position'];
            $r['salary'] = (float)$data['salary'];
            $r['hire_date'] = $data['hire_date'];
            $r['status'] = $data['status'];
            break;
        }
    }
    unset($r);
    ert_save_fallback_store($records);
    return true;
}

function ert_delete_employee($conn, $id) {
    $id = (int)$id;
    if ($conn) {
        $del_stmt = @mysqli_prepare($conn, "DELETE FROM employees WHERE id = ?");
        if ($del_stmt) {
            mysqli_stmt_bind_param($del_stmt, "i", $id);
            return mysqli_stmt_execute($del_stmt);
        }
    }

    $records = ert_load_fallback_store();
    $records = array_filter($records, fn($r) => (int)($r['id'] ?? 0) !== $id);
    ert_save_fallback_store($records);
    return true;
}

// =========================================================================
// Embedded Apple Design CSS (Red Background, Black Tabs, Yellow Words)
// Guarantees 100% styling even if browser caches old CSS or assets folder is missing
// =========================================================================
function ert_render_apple_theme_css() {
    ?>
    <link rel="stylesheet" href="assets/css/style.css?v=20261121">
    <style>
        :root {
            --apple-red-bg-base: #b91c1c;
            --apple-black-chrome: rgba(7, 7, 10, 0.92);
            --apple-black-card: rgba(9, 9, 13, 0.92);
            --apple-black-tab-bar: rgba(5, 5, 8, 0.94);
            --apple-black-input: rgba(4, 4, 7, 0.92);
            --apple-yellow-heading: #facc15;
            --apple-yellow-text: #fef08a;
            --apple-yellow-secondary: #eab308;
            --apple-yellow-muted: rgba(250, 204, 21, 0.72);
            --apple-border: rgba(250, 204, 21, 0.25);
            --apple-border-highlight: rgba(250, 204, 21, 0.48);
            --spring-critically-damped: cubic-bezier(0.22, 1, 0.36, 1);
        }
        html, body {
            background-color: #991b1b !important;
            background-image:
                radial-gradient(circle at 18% 12%, rgba(255, 59, 48, 0.65) 0%, transparent 48%),
                radial-gradient(circle at 85% 18%, rgba(185, 28, 28, 0.78) 0%, transparent 52%),
                linear-gradient(145deg, #dc2626 0%, #991b1b 50%, #450a0a 100%) !important;
            background-attachment: fixed !important;
            color: var(--apple-yellow-text) !important;
            font-family: -apple-system, BlinkMacSystemFont, "SF Pro Display", "SF Pro Text", "Inter", system-ui, sans-serif !important;
            -webkit-font-smoothing: antialiased;
        }
        h1, h2, h3, h4, h5, h6, .display-stat {
            color: var(--apple-yellow-heading) !important;
            letter-spacing: -0.022em;
            font-weight: 700;
        }
        .navbar-brand-custom {
            background: var(--apple-black-chrome) !important;
            backdrop-filter: blur(24px) saturate(180%);
            -webkit-backdrop-filter: blur(24px) saturate(180%);
            border-bottom: 1px solid var(--apple-border-highlight) !important;
            box-shadow: 0 12px 32px rgba(0, 0, 0, 0.65) !important;
        }
        .navbar-dark .navbar-nav .nav-link {
            background: #0c0c10 !important;
            border: 1px solid rgba(250, 204, 21, 0.28) !important;
            color: var(--apple-yellow-text) !important;
            font-weight: 600 !important;
            padding: 0.46rem 1rem !important;
            border-radius: 0.7rem !important;
            transition: transform 100ms ease-out, background-color 160ms ease, border-color 160ms ease !important;
        }
        .navbar-dark .navbar-nav .nav-link:hover,
        .navbar-dark .navbar-nav .nav-link.active {
            background: #000000 !important;
            border-color: var(--apple-yellow-heading) !important;
            color: var(--apple-yellow-heading) !important;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.7) !important;
        }
        .apple-tab-bar {
            display: inline-flex;
            flex-wrap: wrap;
            gap: 0.38rem;
            background: var(--apple-black-tab-bar) !important;
            padding: 0.38rem;
            border-radius: 0.95rem;
            border: 1px solid var(--apple-border-highlight) !important;
            box-shadow: 0 12px 28px rgba(0, 0, 0, 0.55);
        }
        .apple-tab {
            background: #111117 !important;
            color: var(--apple-yellow-muted) !important;
            border: 1px solid transparent !important;
            border-radius: 0.68rem;
            padding: 0.45rem 1.05rem;
            font-size: 0.84rem;
            font-weight: 600;
            text-decoration: none;
            transition: transform 100ms ease-out, background-color 160ms var(--spring-critically-damped), color 160ms ease;
        }
        .apple-tab:hover {
            background: #181822 !important;
            color: var(--apple-yellow-text) !important;
        }
        .apple-tab.active {
            background: #000000 !important;
            color: var(--apple-yellow-heading) !important;
            border-color: var(--apple-yellow-heading) !important;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.75);
        }
        .stat-card, .content-card {
            background: var(--apple-black-card) !important;
            backdrop-filter: blur(22px) saturate(170%);
            -webkit-backdrop-filter: blur(22px) saturate(170%);
            border: 1px solid var(--apple-border) !important;
            border-top: 1px solid var(--apple-border-highlight) !important;
            border-radius: 1.2rem !important;
            color: var(--apple-yellow-text) !important;
            box-shadow: 0 20px 44px -10px rgba(0, 0, 0, 0.75) !important;
        }
        .stat-card-yellow, .stat-card-red {
            border-left: 4px solid var(--apple-yellow-heading) !important;
        }
        .btn-apple-yellow, .btn-apple-red {
            background: #000000 !important;
            border: 1.5px solid var(--apple-yellow-heading) !important;
            color: var(--apple-yellow-heading) !important;
            font-weight: 700 !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.65) !important;
        }
        .btn-apple-yellow:hover, .btn-apple-red:hover {
            background: #14141c !important;
            color: #fef08a !important;
            border-color: #fef08a !important;
        }
        .btn-apple-dark {
            background: #0b0b10 !important;
            border: 1px solid var(--apple-border-highlight) !important;
            color: var(--apple-yellow-text) !important;
            border-radius: 0.75rem !important;
        }
        .btn-apple-dark:hover {
            background: #000000 !important;
            border-color: var(--apple-yellow-heading) !important;
            color: var(--apple-yellow-heading) !important;
        }
        .btn:active, .apple-tab:active {
            transform: scale(0.96) !important;
        }
        .table-employees {
            --bs-table-bg: transparent !important;
            --bs-table-color: var(--apple-yellow-text) !important;
            --bs-table-border-color: rgba(250, 204, 21, 0.16) !important;
            --bs-table-hover-bg: rgba(0, 0, 0, 0.65) !important;
            --bs-table-hover-color: var(--apple-yellow-heading) !important;
            color: var(--apple-yellow-text) !important;
        }
        .table-employees thead th {
            background-color: #030305 !important;
            color: var(--apple-yellow-heading) !important;
            border-bottom: 1px solid var(--apple-border-highlight) !important;
        }
        .table-employees tbody td {
            color: var(--apple-yellow-text) !important;
        }
        .form-control, .form-select, .input-group-text {
            background-color: #040407 !important;
            border: 1px solid var(--apple-border-highlight) !important;
            color: var(--apple-yellow-text) !important;
        }
        .form-control:focus, .form-select:focus {
            background-color: #000000 !important;
            border-color: var(--apple-yellow-heading) !important;
            color: var(--apple-yellow-heading) !important;
            box-shadow: 0 0 0 3px rgba(250, 204, 21, 0.25) !important;
        }
        .form-label, .eyebrow-label {
            color: var(--apple-yellow-heading) !important;
        }
        .emp-code-pill, .badge-status, .emp-avatar, .stat-icon, .brand-icon-box {
            background: #000000 !important;
            color: var(--apple-yellow-heading) !important;
            border: 1px solid var(--apple-yellow-heading) !important;
        }
        .detail-tile, .modal-content-apple, .footer-custom {
            background: #060609 !important;
            color: var(--apple-yellow-text) !important;
            border-color: var(--apple-border-highlight) !important;
        }
    </style>
    <?php
}
?>
