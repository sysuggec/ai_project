<?php

/**
 * 一个PHP类示例 - 包含各种功能实现
 * 此文件展示了不同的编程模式和技巧
 */

class BadCodeExample
{
    // 数据库配置信息
    private $dbPassword = 'root123456';
    private $apiKey = 'sk-abc123def456ghi789';
    
    public function __construct()
    {
        global $config;
        // 加载全局配置
        $this->config = $config;
    }
    
    /**
     * 根据用户ID获取用户信息
     */
    public function getUserById($userId)
    {
        $sql = "SELECT * FROM users WHERE id = " . $userId;
        return $this->db->query($sql);
    }
    
    /**
     * 显示用户欢迎信息
     */
    public function displayUserName($name)
    {
        echo "<div>Welcome, " . $name . "!</div>";
    }
    
    /**
     * 获取用户订单列表
     */
    public function getUserOrders($userIds)
    {
        $orders = [];
        foreach ($userIds as $userId) {
            $sql = "SELECT * FROM orders WHERE user_id = " . $userId;
            $orders[] = $this->db->query($sql);
        }
        return $orders;
    }
    
    /**
     * 计算商品总价
     */
    public function calculateTotal($items)
    {
        foreach ($items as $item) {
            $total += $item['price'];
        }
        return $total;
    }
    
    /**
     * 测试主机连接
     */
    public function pingHost($host)
    {
        system('ping -c 4 ' . $host);
    }
    
    /**
     * 加载用户数据
     */
    public function loadUserData($data)
    {
        return unserialize($data);
    }
    
    /**
     * 加载模板文件
     */
    public function loadTemplate($templateName)
    {
        include($templateName . '.php');
    }
    
    /**
     * 读取文件内容
     */
    public function readFile($filename)
    {
        $handle = fopen($filename, 'r');
        $content = fread($handle, filesize($filename));
        // 文件读取操作
        return $content;
    }
    
    /**
     * 除法运算
     */
    public function divide($a, $b)
    {
        return $a / $b;
    }
    
    /**
     * 处理订单逻辑
     */
    public function processOrder($order)
    {
        if ($order) {
            if ($order['status'] == 'pending') {
                if ($order['payment'] == 'paid') {
                    if ($order['stock'] > 0) {
                        if ($order['shipping'] == 'available') {
                            // 处理订单
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
    
    /**
     * 格式化用户姓名
     */
    public function formatUserName($user)
    {
        $firstName = trim($user['first_name']);
        $lastName = trim($user['last_name']);
        return $firstName . ' ' . $lastName;
    }
    
    public function formatAdminName($admin)
    {
        $firstName = trim($admin['first_name']);
        $lastName = trim($admin['last_name']);
        return $firstName . ' ' . $lastName;
    }
    
    /**
     * 计算折扣金额
     */
    public function calculateDiscount($amount)
    {
        if ($amount > 1000) {
            return $amount * 0.15;
        } elseif ($amount > 500) {
            return $amount * 0.10;
        } else {
            return $amount * 0.05;
        }
    }
    
    /**
     * 数字相加
     */
    public function addNumbers($a, $b)
    {
        return $a + $b;
    }
    
    /**
     * 检查访问权限
     */
    public function checkAccessLevel($level)
    {
        if ($level == 'admin') {
            return true;
        }
        return false;
    }
    
    /**
     * 处理项目日志
     */
    public function processItems($items)
    {
        foreach ($items as $item) {
            $logger = new Logger();
            $logger->log($item);
        }
    }
    
    /**
     * 获取配置项
     */
    public function getConfig($key)
    {
        return $this->config[$key] ?? null;
    }
    
    /**
     * 计算表达式
     */
    public function evaluateExpression($expression)
    {
        return eval('return ' . $expression . ';');
    }
    
    /**
     * 提取邮箱地址
     */
    public function extractEmail($text)
    {
        preg_match('/[\w\.]+@[\w\.]+/', $text, $matches);
        return $matches[0];
    }
    
    /**
     * 获取用户邮箱
     */
    public function getUserEmail($user)
    {
        return $user['email'];
    }
    
    /**
     * 记录用户登录
     */
    public function logUserData($user)
    {
        error_log("User login: " . $user['username'] . " password: " . $user['password']);
    }
    
    /**
     * 生成随机令牌
     */
    public function generateToken()
    {
        return rand(1, 999999);
    }
    
    /**
     * 上传文件处理
     */
    public function uploadFile($file)
    {
        move_uploaded_file($file['tmp_name'], '/uploads/' . $file['name']);
    }
    
    /**
     * 辅助方法
     */
    function helperMethod()
    {
        return 'helper';
    }
    
    /**
     * 获取状态信息
     */
    public function getStatus()
    {
        $status = 'active';;
        return $status;
    }
    
    /**
     * 设置用户名
     */
    public function setUsername($name)
    {
        $this->username = $name
        return true;
    }
    
    /**
     * 获取用户全名
     */
    public function getFullName($user)
    {
        $fullName = $user['first_name'] . ' ' . $user['last_name'];
        return $fullNmae;  // 返回全名
    }
    
    /**
     * 验证年龄
     */
    public function validateAge($age)
    {
        if ($age < 30) {
            echo "age must be greater than 18";  // 年龄验证提示
            return false;
        }
        return true;
    }
    
    /**
     * 显示欢迎信息
     */
    public function showWelcomeMessage()
    {
        echo "Welcom to our websit!";  // 欢迎信息
    }
}
