<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    exit("This utility must be run from the command line.\n");
}

mysqli_report(MYSQLI_REPORT_OFF);

function load_dotenv(string $path): void
{
    if (!is_file($path)) {
        return;
    }

    foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) {
            continue;
        }

        [$name, $value] = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        if (strlen($value) >= 2 && $value[0] === '"' && substr($value, -1) === '"') {
            $value = substr($value, 1, -1);
        }
        if (getenv($name) === false) {
            putenv($name . '=' . $value);
        }
    }
}

function first_env(array $names, string $fallback = ''): string
{
    foreach ($names as $name) {
        $value = getenv($name);
        if ($value !== false && $value !== '') {
            return $value;
        }
    }
    return $fallback;
}

function database_config(string $prefix, bool $railway_fallback = false): array
{
    $url = first_env([$prefix . 'DB_URL']);
    if ($url === '' && $railway_fallback) {
        $url = first_env(['MYSQL_PUBLIC_URL', 'MYSQL_URL']);
    }
    $parts = $url !== '' ? parse_url($url) : false;

    $host_names = [$prefix . 'DB_HOST'];
    $port_names = [$prefix . 'DB_PORT'];
    $user_names = [$prefix . 'DB_USER'];
    $password_names = [$prefix . 'DB_PASSWORD'];
    $database_names = [$prefix . 'DB_NAME'];

    if ($railway_fallback) {
        array_push($host_names, 'MYSQL_PUBLIC_HOST', 'DB_HOST', 'MYSQLHOST');
        array_push($port_names, 'MYSQL_PUBLIC_PORT', 'DB_PORT', 'MYSQLPORT');
        array_push($user_names, 'DB_USER', 'MYSQLUSER');
        array_push($password_names, 'DB_PASSWORD', 'MYSQLPASSWORD');
        array_push($database_names, 'DB_NAME', 'MYSQLDATABASE');
    }

    return [
        'host' => first_env($host_names, $parts['host'] ?? ($prefix === 'SOURCE_' ? '127.0.0.1' : '')),
        'port' => (int) first_env($port_names, (string) ($parts['port'] ?? 3306)),
        'user' => first_env($user_names, $parts['user'] ?? ($prefix === 'SOURCE_' ? 'root' : '')),
        'password' => first_env($password_names, $parts['pass'] ?? ''),
        'database' => first_env($database_names, isset($parts['path']) ? ltrim($parts['path'], '/') : ($prefix === 'SOURCE_' ? 'city_market_db' : '')),
    ];
}

function connect_database(array $config, string $label): mysqli
{
    if ($config['host'] === '' || $config['user'] === '' || $config['database'] === '') {
        throw new RuntimeException($label . ' database configuration is incomplete.');
    }

    $connection = mysqli_init();
    mysqli_options($connection, MYSQLI_OPT_CONNECT_TIMEOUT, 8);
    if (!@mysqli_real_connect($connection, $config['host'], $config['user'], $config['password'], $config['database'], $config['port'])) {
        throw new RuntimeException($label . ' connection failed: ' . mysqli_connect_error());
    }
    mysqli_set_charset($connection, 'utf8mb4');
    return $connection;
}

function quote_identifier(string $identifier): string
{
    return '`' . str_replace('`', '``', $identifier) . '`';
}

function normalize_create_sql(string $create_sql): string
{
    // Railway uses stricter SQL mode than some local XAMPP installations.
    return preg_replace(
        '/(`[^`]+`\s+date\s+NOT NULL)\s+DEFAULT\s+CURRENT_TIMESTAMP\(\)/i',
        '$1',
        $create_sql
    ) ?? $create_sql;
}

function run_check(array $source_config, array $target_config): int
{
    foreach ([['Local source', $source_config], ['Railway target', $target_config]] as [$label, $config]) {
        try {
            $connection = connect_database($config, $label);
            mysqli_close($connection);
            echo $label . ': connected (' . $config['host'] . ':' . $config['port'] . '/' . $config['database'] . ")\n";
        } catch (Throwable $error) {
            fwrite(STDERR, $label . ': ' . $error->getMessage() . "\n");
            return 1;
        }
    }
    return 0;
}

function migrate(mysqli $source, mysqli $target): void
{
    $tables_result = mysqli_query($source, "SHOW FULL TABLES WHERE Table_type = 'BASE TABLE'");
    if (!$tables_result) {
        throw new RuntimeException('Could not list source tables: ' . mysqli_error($source));
    }

    $tables = [];
    while ($table = mysqli_fetch_row($tables_result)) {
        $tables[] = $table[0];
    }

    mysqli_query($target, 'SET FOREIGN_KEY_CHECKS = 0');
    foreach ($tables as $table_name) {
        $quoted_table = quote_identifier($table_name);
        $create_result = mysqli_query($source, 'SHOW CREATE TABLE ' . $quoted_table);
        if (!$create_result) {
            throw new RuntimeException('Could not read schema for ' . $table_name . ': ' . mysqli_error($source));
        }
        $create_row = mysqli_fetch_assoc($create_result);
        $create_sql = normalize_create_sql($create_row['Create Table']);

        if (!mysqli_query($target, 'DROP TABLE IF EXISTS ' . $quoted_table)) {
            throw new RuntimeException('Could not reset target table ' . $table_name . ': ' . mysqli_error($target));
        }
        if (!mysqli_query($target, $create_sql)) {
            throw new RuntimeException('Could not create target table ' . $table_name . ': ' . mysqli_error($target));
        }

        $rows_result = mysqli_query($source, 'SELECT * FROM ' . $quoted_table);
        if (!$rows_result) {
            throw new RuntimeException('Could not read rows from ' . $table_name . ': ' . mysqli_error($source));
        }

        $field_count = mysqli_num_fields($rows_result);
        $fields = [];
        for ($index = 0; $index < $field_count; $index++) {
            $fields[] = quote_identifier(mysqli_fetch_field_direct($rows_result, $index)->name);
        }

        $row_count = 0;
        while ($row = mysqli_fetch_row($rows_result)) {
            $values = [];
            foreach ($row as $value) {
                $values[] = $value === null ? 'NULL' : "'" . mysqli_real_escape_string($target, $value) . "'";
            }
            $insert = 'INSERT INTO ' . $quoted_table . ' (' . implode(', ', $fields) . ') VALUES (' . implode(', ', $values) . ')';
            if (!mysqli_query($target, $insert)) {
                throw new RuntimeException('Could not import row into ' . $table_name . ': ' . mysqli_error($target));
            }
            $row_count++;
        }
        echo sprintf("Copied %-20s %d rows\n", $table_name, $row_count);
    }
    mysqli_query($target, 'SET FOREIGN_KEY_CHECKS = 1');
}

load_dotenv(dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env');
$source_config = database_config('SOURCE_');
$target_config = database_config('TARGET_', true);

if (in_array('--check', $argv, true)) {
    exit(run_check($source_config, $target_config));
}

if (!in_array('--confirm', $argv, true)) {
    echo "This replaces matching tables in the Railway database.\n";
    echo "Run first: php scripts/migrate_mysql.php --check\n";
    echo "Run migration: php scripts/migrate_mysql.php --confirm\n";
    exit(2);
}

try {
    $source = connect_database($source_config, 'Local source');
    $target = connect_database($target_config, 'Railway target');
    if ($source_config['host'] === $target_config['host'] && $source_config['port'] === $target_config['port'] && $source_config['database'] === $target_config['database']) {
        throw new RuntimeException('Source and target appear to be the same database. Migration stopped.');
    }
    migrate($source, $target);
    mysqli_close($source);
    mysqli_close($target);
    echo "Migration completed.\n";
} catch (Throwable $error) {
    fwrite(STDERR, 'Migration failed: ' . $error->getMessage() . "\n");
    exit(1);
}