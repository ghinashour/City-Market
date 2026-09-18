<?php
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = (int) (getenv('DB_PORT') ?: 3306);
$user = getenv('DB_USER') ?: 'root';
$password = getenv('DB_PASSWORD') ?: '';
$database = getenv('DB_NAME') ?: 'city_market_db';

$connection = mysqli_init();
mysqli_options($connection, MYSQLI_OPT_CONNECT_TIMEOUT, 5);

if (!mysqli_real_connect($connection, $host, $user, $password, $database, $port)) {
    error_log('City Market database connection failed: ' . mysqli_connect_error());
    http_response_code(503);
    exit('The store is temporarily unavailable. Please try again shortly.');
}

mysqli_set_charset($connection, 'utf8mb4');
?>