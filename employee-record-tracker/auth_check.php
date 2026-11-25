<?php
/**
 * Authentication Guard (auth_check.php)
 * Ensures the user is logged in before accessing CRUD pages.
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['ert_logged_in']) || $_SESSION['ert_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}
?>
