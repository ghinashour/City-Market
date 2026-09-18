<?php
$database_url = getenv('DB_URL') ?: (getenv('MYSQL_PUBLIC_URL') ?: getenv('MYSQL_URL'));
$url_parts = $database_url ? parse_url($database_url) : false;

$host = getenv('DB_HOST')
    ?: getenv('MYSQL_PUBLIC_HOST')
    ?: getenv('MYSQLHOST')
    ?: ($url_parts['host'] ?? '127.0.0.1');
$port = (int) (getenv('DB_PORT')
    ?: getenv('MYSQL_PUBLIC_PORT')
    ?: getenv('MYSQLPORT')
    ?: ($url_parts['port'] ?? 3306));
$user = getenv('DB_USER')
    ?: getenv('MYSQLUSER')
    ?: ($url_parts['user'] ?? 'root');
$password = getenv('DB_PASSWORD')
    ?: getenv('MYSQLPASSWORD')
    ?: ($url_parts['pass'] ?? '');
$database = getenv('DB_NAME')
    ?: getenv('MYSQLDATABASE')
    ?: (isset($url_parts['path']) ? ltrim($url_parts['path'], '/') : 'city_market_db');

$connection = mysqli_init();
mysqli_options($connection, MYSQLI_OPT_CONNECT_TIMEOUT, 5);

if (!mysqli_real_connect($connection, $host, $user, $password, $database, $port)) {
    error_log('City Market database connection failed: ' . mysqli_connect_error());
    http_response_code(503);
    exit('The store is temporarily unavailable. Please try again shortly.');
}

mysqli_set_charset($connection, 'utf8mb4');
?>