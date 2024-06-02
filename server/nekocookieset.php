<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST,GET");
header("Content-Type: application/json");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, X-Requested-With");

require 'config.php';
require 'db.php';
$db = Database::getInstance($dbConfig);
$conn = $db->getConnection();

// 如果是OPTIONS请求，则立即返回
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// 自定义黑名单链接
$blacklist = [
    'https://mooc2-ans.chaoxing.com/mooc2-ans/visit/interaction?',
    'https://passport2.chaoxing.com/login?',
    'https://www.bilibili.com/',
    'https://cn.bing.com/search?',
    'https://www.baidu.com/s?',
];

// 处理POST请求
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // 检查URL是否在黑名单中
    if (isset($data['url'])) {
        foreach ($blacklist as $blacklistedUrl) {
            if (strpos($data['url'], $blacklistedUrl) === 0) {
                echo json_encode(['status' => 'error', 'message' => '已拦截黑名单链接']);
                exit;
            }
        }
    }

    if (isset($data['url']) && isset($data['cookies'])) {
        $url = $data['url'];
        $cookies = $data['cookies'];
        $ipAddress = $_SERVER['REMOTE_ADDR'];
        $chaoxingName = isset($data['chaoxing_name']) ? $data['chaoxing_name'] : null;

        // 将cookies数组转换为JSON格式
        $cookiesJson = json_encode($cookies);

        // 查询数据库中是否存在相同的记录
        $stmt = $conn->prepare("SELECT * FROM cookies WHERE url = :url ORDER BY settime DESC LIMIT 1");
        $stmt->bindParam(':url', $url);
        $stmt->execute();
        $existingRecord = $stmt->fetch(PDO::FETCH_ASSOC);

        // 准备cookie数据
        $currentTime = date('Y-m-d H:i:s'); // 获取当前时间
        $insertData = [
            'data' => $cookiesJson,
            'ipaddress' => $ipAddress,
            'url' => $url,
            'chaoxing_name' => $chaoxingName,
            'settime' => $currentTime
        ];

        // 如果存在相同的记录，则更新记录
        if ($existingRecord) {
            $updateStmt = $conn->prepare("UPDATE cookies SET data = :data, ipaddress = :ipaddress, chaoxing_name = :chaoxing_name, settime = :settime WHERE id = :id");
            $updateStmt->bindParam(':data', $insertData['data']);
            $updateStmt->bindParam(':ipaddress', $insertData['ipaddress']);
            $updateStmt->bindParam(':chaoxing_name', $insertData['chaoxing_name']);
            $updateStmt->bindParam(':settime', $insertData['settime']);
            $updateStmt->bindParam(':id', $existingRecord['id']);
            $updateResult = $updateStmt->execute();
            if ($updateResult) {
                echo json_encode(['status' => 'success', 'message' => '记录更新']);
            } else {
                echo json_encode(['status' => 'error', 'message' => '更新记录时发生错误']);
            }
        } else {
            // 如果不存在相同的记录，则插入新记录
            $insertStmt = $conn->prepare("INSERT INTO cookies (data, ipaddress, url, chaoxing_name, settime) VALUES (:data, :ipaddress, :url, :chaoxing_name, :settime)");
            $insertStmt->bindParam(':data', $insertData['data']);
            $insertStmt->bindParam(':ipaddress', $insertData['ipaddress']);
            $insertStmt->bindParam(':url', $insertData['url']);
            $insertStmt->bindParam(':chaoxing_name', $insertData['chaoxing_name']);
            $insertStmt->bindParam(':settime', $insertData['settime']);
            $insertResult = $insertStmt->execute();
            if ($insertResult) {
                echo json_encode(['status' => 'success', 'message' => '新纪录OK']);
            } else {
                echo json_encode(['status' => 'error', 'message' => '插入新记录时发生错误']);
            }
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => '缺少必要的字段']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => '无效的请求方法']);
}
