<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Content-Type: application/json');

require 'config.php'; // 包含配置文件
require 'db.php';
$db = Database::getInstance($dbConfig);
$conn = $db->getConnection();

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // 构建基础查询
            $query = "SELECT * FROM cookies";
            $conditions = [];
            $params = [];


            // 执行查询
            $stmt = $conn->prepare($query);
            $stmt->execute($params);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($results);
            break;
        case 'POST':
            // 处理 POST 请求，用于删除
            $rawData = file_get_contents("php://input");
            $jsonData = json_decode($rawData, true);

            if (isset($jsonData['action']) && $jsonData['action'] == 'delete') {
                $ids = $jsonData['ids'] ?? [];

                if (empty($ids)) {
                    header('HTTP/1.1 400 Bad Request');
                    echo json_encode(['success' => false, 'message' => 'No IDs provided.']);
                    break;
                }

                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $conn->prepare("DELETE FROM cookies WHERE id IN ($placeholders)");
                $stmt->execute($ids);

                echo json_encode(['success' => true, 'message' => 'Records deleted successfully.']);
            } else {
                // 如果 POST 请求没有提供正确的 action，返回错误
                header('HTTP/1.1 400 Bad Request');
                echo json_encode(['success' => false, 'message' => 'Invalid action.']);
            }
            break;
    }
} catch (PDOException $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['success' => false, 'message' => 'Error handling request: ' . $e->getMessage()]);
}
