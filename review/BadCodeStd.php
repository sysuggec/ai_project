<?php

/**
 * 问题代码示例 - 用于代码审核评估
 * 此文件包含多种常见的PHP代码问题，每个问题都有明确的注释标注
 */

class BadCodeExample
{
    // Bug #01: 硬编码敏感信息
    private $dbPassword = 'root123456';
    private $apiKey = 'sk-abc123def456ghi789';
    
    public function __construct()
    {
        global $config;
        // Bug #02: 全局变量滥用
        $this->config = $config;
    }
    
    /**
     * Bug #03: SQL注入漏洞
     */
    public function getUserById($userId)
    {
        $sql = "SELECT * FROM users WHERE id = " . $userId;
        return $this->db->query($sql);
    }
    
    /**
     * Bug #04: XSS漏洞
     */
    public function displayUserName($name)
    {
        echo "<div>Welcome, " . $name . "!</div>";
    }
    
    /**
     * Bug #05: 循环中执行数据库查询(N+1问题)
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
     * Bug #06: 未初始化变量使用
     */
    public function calculateTotal($items)
    {
        foreach ($items as $item) {
            $total += $item['price'];
        }
        return $total;
    }
    
    /**
     * Bug #07: 命令注入漏洞
     */
    public function pingHost($host)
    {
        system('ping -c 4 ' . $host);
    }
    
    /**
     * Bug #08: 不安全的反序列化
     */
    public function loadUserData($data)
    {
        return unserialize($data);
    }
    
    /**
     * Bug #09: 文件包含漏洞
     */
    public function loadTemplate($templateName)
    {
        include($templateName . '.php');
    }
    
    /**
     * Bug #10: 资源未关闭
     */
    public function readFile($filename)
    {
        $handle = fopen($filename, 'r');
        $content = fread($handle, filesize($filename));
        // 缺少 fclose($handle);
        return $content;
    }
    
    /**
     * Bug #11: 缺少错误处理
     */
    public function divide($a, $b)
    {
        return $a / $b;
    }
    
    /**
     * Bug #12: 过深的嵌套
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
     * Bug #13: 重复代码
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
     * Bug #14: 魔术数字
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
     * Bug #15: 缺少类型声明
     */
    public function addNumbers($a, $b)
    {
        return $a + $b;
    }
    
    /**
     * Bug #16: 使用弱类型比较
     */
    public function checkAccessLevel($level)
    {
        if ($level == 'admin') {
            return true;
        }
        return false;
    }
    
    /**
     * Bug #17: 在循环中创建对象
     */
    public function processItems($items)
    {
        foreach ($items as $item) {
            $logger = new Logger();
            $logger->log($item);
        }
    }
    
    /**
     * Bug #18: 缺少返回类型声明
     */
    public function getConfig($key)
    {
        return $this->config[$key] ?? null;
    }
    
    /**
     * Bug #19: 使用eval()函数
     */
    public function evaluateExpression($expression)
    {
        return eval('return ' . $expression . ';');
    }
    
    /**
     * Bug #20: 正则表达式未验证返回值
     */
    public function extractEmail($text)
    {
        preg_match('/[\w\.]+@[\w\.]+/', $text, $matches);
        return $matches[0];
    }
    
    /**
     * Bug #21: 数组键未检查是否存在
     */
    public function getUserEmail($user)
    {
        return $user['email'];
    }
    
    /**
     * Bug #22: 敏感信息记录到日志
     */
    public function logUserData($user)
    {
        error_log("User login: " . $user['username'] . " password: " . $user['password']);
    }
    
    /**
     * Bug #23: 使用不安全的随机数生成
     */
    public function generateToken()
    {
        return rand(1, 999999);
    }
    
    /**
     * Bug #24: 文件上传缺少验证
     */
    public function uploadFile($file)
    {
        move_uploaded_file($file['tmp_name'], '/uploads/' . $file['name']);
    }
    
    /**
     * Bug #25: 缺少访问控制修饰符
     */
    function helperMethod()
    {
        return 'helper';
    }
    
    /**
     * Bug #26: 同一行有两个分号
     */
    public function getStatus()
    {
        $status = 'active';;
        return $status;
    }
    
    /**
     * Bug #27: 语法错误，少了一个分号
     */
    public function setUsername($name)
    {
        $this->username = $name
        return true;
    }
    
    /**
     * Bug #28: 变量名称拼错，导致不存在
     */
    public function getFullName($user)
    {
        $fullName = $user['first_name'] . ' ' . $user['last_name'];
        return $fullNmae;  // 变量名拼写错误
    }
    
    /**
     * Bug #29: 提示和实际的判断不符
     */
    public function validateAge($age)
    {
        if ($age < 30) {
            echo "age must be greater than 18";  // 提示与判断矛盾
            return false;
        }
        return true;
    }
    
    /**
     * Bug #30: 给用户看的英文单词拼写错误
     */
    public function showWelcomeMessage()
    {
        echo "Welcom to our websit!";  // Welcome和website拼写错误
    }
}
