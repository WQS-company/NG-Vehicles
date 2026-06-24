<?php
// config/paystack.php

/**
 * Simple helper to fetch environment variables from server env or .env-like files.
 * If `env()` is not defined elsewhere in the project, this keeps it safe.
 */
if (!function_exists('env')) {
    function env($key, $default = null) {
        if (isset($_SERVER[$key])) return $_SERVER[$key];
        if (getenv($key) !== false) return getenv($key);
        return $default;
    }
}

$secretKey = env('PAYSTACK_SECRET');
$publicKey = env('PAYSTACK_PUBLIC');

if (class_exists('App\\Models\\Setting')) {
    try {
        $settingModel = new App\Models\Setting();
        if (empty($secretKey)) {
            $secretKey = $settingModel->get('paystack_secret_key', $secretKey);
        }
        if (empty($publicKey)) {
            $publicKey = $settingModel->get('paystack_public_key', $publicKey);
        }
    } catch (Exception $e) {
        // Ignore configuration lookup errors and fall back to environment variables.
    }
}

return [
    // Set your Paystack secret key here. Keep this private and secure.
    'secret_key' => $secretKey ?: 'PAYSTACK_SECRET_KEY_HERE',
    'public_key' => $publicKey ?: '',
    'base_url' => 'https://api.paystack.co',
    'currency' => 'NGN'
];
