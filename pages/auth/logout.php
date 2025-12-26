<?php
/**
 * User Logout
 * Toko Islami - Online Shop & Artikel
 */

require_once '../../includes/functions.php';

// Clear session
$_SESSION = [];
session_destroy();

// Redirect to home
header('Location: ../../index.php');
exit;
