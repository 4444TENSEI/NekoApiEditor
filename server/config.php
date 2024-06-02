<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
// 1.自定义数据库信息
$dbConfig = array(
    'host' => getenv('DB_HOST') ?: 'localhost',
    'port' => getenv('DB_PORT') ?: '3306',
    'user' => getenv('DB_USER') ?: 'cookies',
    'pass' => getenv('DB_PASS') ?: '123456',
    'name' => getenv('DB_NAME') ?: 'cookies'
);



foreach ($dbConfig as$key => $value) {
    if ($value === null) {
        throw new Exception("Database configuration error: {$key} is not set.");
    }
}

return $dbConfig;