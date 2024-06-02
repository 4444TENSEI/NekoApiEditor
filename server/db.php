<?php

header("Access-Control-Allow-Origin: *"); // 更改为具体的允许域
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

require 'config.php'; // 包含配置文件

// 创建数据库连接实例
$db = Database::getInstance($dbConfig);

class Database {
    private static $instance = null;
    private $conn;

    private function __construct($dbConfig) {
        try {
            $this->conn = new PDO(
                "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']}",
                $dbConfig['user'],
                $dbConfig['pass'],
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance($dbConfig) {
        if (self::$instance == null) {
            self::$instance = new Database($dbConfig);
        } elseif (!self::$instance->conn) {
            // 如果连接已断开，尝试重新连接
            self::$instance->reconnect($dbConfig);
        }
        return self::$instance;
    }

    private function reconnect($dbConfig) {
        try {
            $this->conn = new PDO(
                "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['name']}",
                $dbConfig['user'],
                $dbConfig['pass'],
                array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'")
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Reconnection failed: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->conn;
    }
}
