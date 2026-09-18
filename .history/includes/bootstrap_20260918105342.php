<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax'
    ]);
    session_start();
}

function client_ip(): string
{
    return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function rate_limit(string $action, int $max_attempts, int $window_seconds): bool
{
    $key = hash('sha256', $action . '|' . client_ip());
    $path = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'city_market_rate_' . $key . '.json';
    $now = time();
    $attempts = [];

    if (is_file($path)) {
        $stored = json_decode((string) file_get_contents($path), true);
        if (is_array($stored)) {
            $attempts = array_values(array_filter($stored, function ($timestamp) use ($now, $window_seconds) {
                return is_int($timestamp) && $timestamp > $now - $window_seconds;
            }));
        }
    }

    if (count($attempts) >= $max_attempts) {
        return false;
    }

    $attempts[] = $now;
    file_put_contents($path, json_encode($attempts), LOCK_EX);
    return true;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>