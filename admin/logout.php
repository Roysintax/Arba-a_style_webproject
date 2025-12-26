<?php
/**
 * Admin Logout
 * Toko Islami - Admin Panel
 */

session_start();

// Clear all session data
$_SESSION = [];

// Destroy session
session_destroy();

// Redirect to login
header('Location: login.php');
exit;
