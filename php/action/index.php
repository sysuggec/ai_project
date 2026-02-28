<?php
/**
 * 账号风控系统 - 统一入口
 * 
 * 路由格式: ?action=Risk.{方法名}
 * 支持方法: refundReport, riskQuery, refundCancel
 */

// 设置错误报告
error_reporting(E_ALL);
ini_set('display_errors', '0');

// 设置响应头
header('Content-Type: application/json; charset=utf-8');

// 自动加载
require_once __DIR__ . '/../vendor/autoload.php';

// 初始化数据库
App\Config\Database::init();

// 获取action参数
$action = $_GET['action'] ?? $_POST['action'] ?? '';

if (empty($action)) {
    echo json_encode([
        'code' => 1001,
        'msg' => '缺少action参数',
        'data' => null,
    ]);
    exit;
}

// 解析action
$parts = explode('.', $action, 2);
if (count($parts) !== 2) {
    echo json_encode([
        'code' => 1002,
        'msg' => 'action格式错误，应为: Controller.method',
        'data' => null,
    ]);
    exit;
}

$controller = $parts[0];
$method = $parts[1];

// 路由映射
$controllerMap = [
    'Risk' => \App\Controller\RiskControl::class,
];

if (!isset($controllerMap[$controller])) {
    echo json_encode([
        'code' => 1002,
        'msg' => "未知的控制器: {$controller}",
        'data' => null,
    ]);
    exit;
}

// 实例化控制器
$controllerClass = $controllerMap[$controller];
$controllerInstance = new $controllerClass();

// 检查方法是否存在
if (!method_exists($controllerInstance, $method)) {
    echo json_encode([
        'code' => 1002,
        'msg' => "未知的接口方法: {$method}",
        'data' => null,
    ]);
    exit;
}

try {
    // 调用方法
    $result = $controllerInstance->$method();
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
} catch (\Exception $e) {
    echo json_encode([
        'code' => $e->getCode() ?: 9999,
        'msg' => $e->getMessage(),
        'data' => null,
    ], JSON_UNESCAPED_UNICODE);
}
