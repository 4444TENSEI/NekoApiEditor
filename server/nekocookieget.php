<?php
header("Access-Control-Allow-Origin: *");
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Content-Type: application/json');

require 'config.php'; // 包含配置文件
require 'db.php';
$db = Database::getInstance($dbConfig);
$conn =$db->getConnection();

try {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            // 构建基础查询
            $query = "SELECT * FROM cookies";
            $conditions = [];
            $params = [];

            // 检查并添加 URL 筛选条件
            if (!empty($_GET['url'])) {
                $conditions[] = "url = ?";
                $params[] =$_GET['url'];
            }

            // 检查并添加日期筛选条件
            if (!empty($_GET['date'])) {
                $conditions[] = "DATE(settime) = ?";
                $params[] =$_GET['date'];
            }

            // 检查并添加时间段筛选条件
            if (!empty($_GET['time_period'])) {
                switch ($_GET['time_period']) {
                    case '上午':
                        $conditions[] = "HOUR(settime) BETWEEN 0 AND 11";
                        break;
                    case '下午':
                        $conditions[] = "HOUR(settime) BETWEEN 12 AND 23";
                        break;
                    case '全天':
                        // 不需要添加条件，因为全天包含了所有记录
                        break;
                    default:
                        header('HTTP/1.1 400 Bad Request');
                        echo json_encode(['success' => false, 'message' => 'Invalid time period.']);
                        exit;
                }
            }

            // 检查并添加 IP 筛选条件
            if (!empty($_GET['ip'])) {
                $conditions[] = "ip = ?";
                $params[] =$_GET['ip'];
            }

            // 如果有筛选条件，添加到查询中
            if (!empty($conditions)) {
                $query .= " WHERE " . implode(' AND ',$conditions);
            }

            // 执行查询
            $stmt =$conn->prepare($query);
            $stmt->execute($params);
            $results =$stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($results);
            break;
        case 'POST':
            // 处理 POST 请求，可能用于删除
            $rawData = file_get_contents("php://input");
            $jsonData = json_decode($rawData, true);

            if (isset($jsonData['action']) &&$jsonData['action'] == 'delete') {
                $ids =$jsonData['ids'] ?? [];

                if (empty($ids)) {
                    header('HTTP/1.1 400 Bad Request');
                    echo json_encode(['success' => false, 'message' => 'No IDs provided.']);
                    break;
                }

                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                $stmt =$conn->prepare("DELETE FROM cookies WHERE id IN ($placeholders)");
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
