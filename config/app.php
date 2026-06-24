<?php
// config/app.php
define('APP_NAME', 'NVOTS');
define('APP_TITLE', 'National Vehicle Ownership & Traceability System');
define('APP_VERSION', '1.0.0');

// Base URL detection
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$script = $_SERVER['SCRIPT_NAME'] ?? '';
$dir = dirname($script);
if ($dir === '/' || $dir === '\\') {
    $dir = '';
}
define('BASE_URL', $protocol . $host . $dir);

// Session Security Config
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', 1);
}
ini_set('session.cookie_samesite', 'Lax');

// System Roles
define('ROLE_SUPER_ADMIN', 'SUPER_ADMIN');
define('ROLE_REGISTRATION_ADMIN', 'REGISTRATION_ADMIN');
define('ROLE_VERIFICATION_ADMIN', 'VERIFICATION_ADMIN');
define('ROLE_BENEFICIARY', 'BENEFICIARY');

