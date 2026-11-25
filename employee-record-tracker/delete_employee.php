<?php
/**
 * Delete Employee Record (DELETE Operation)
 * Project: Employee Record Tracker
 */
require_once 'auth_check.php';
require_once 'db_connect.php';

$id = isset($_POST['id']) ? (int)$_POST['id'] : (isset($_GET['id']) ? (int)$_GET['id'] : 0);

if ($id <= 0) {
    $_SESSION['flash_message'] = "Invalid employee record ID specified for deletion.";
    $_SESSION['flash_type'] = "danger";
    header("Location: index.php");
    exit;
}

$emp = ert_get_employee_by_id($conn, $id);

if (!$emp) {
    $_SESSION['flash_message'] = "Employee record not found or already deleted.";
    $_SESSION['flash_type'] = "danger";
    header("Location: index.php");
    exit;
}

if (ert_delete_employee($conn, $id)) {
    $_SESSION['flash_message'] = "Employee record for \"{$emp['full_name']}\" ({$emp['employee_code']}) has been deleted from the database.";
    $_SESSION['flash_type'] = "success";
} else {
    $_SESSION['flash_message'] = "Failed to delete employee record.";
    $_SESSION['flash_type'] = "danger";
}

header("Location: index.php");
exit;
?>
