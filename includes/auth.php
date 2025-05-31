<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Note: Authentication functions are now in functions.php to avoid redeclaration
// This file is kept for backward compatibility but functions are centralized

// Only call checkSessionTimeout if the function exists (after functions.php is loaded)
if (function_exists('checkSessionTimeout')) {
    checkSessionTimeout();
}
?>