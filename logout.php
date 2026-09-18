<?php
// logout.php — fully destroy session and clear session cookie
if (session_status() === PHP_SESSION_NONE) {
	session_start();
}

// Unset all session variables
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
if (ini_get("session.use_cookies")) {
	$params = session_get_cookie_params();
	setcookie(session_name(), '', time() - 42000,
		$params['path'], $params['domain'], $params['secure'], $params['httponly']
	);
}

// Destroy the session
session_destroy();

// Send no-cache headers to avoid serving cached authenticated pages
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Redirect to home
header("Location: index.php");
exit();
?>
