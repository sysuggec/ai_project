<?php
/**
 * 用户管理类
 * 这是一个包含多种问题的示例文件，用于代码审查测试
 */

class userManager
{
    private $db;
    private $host = 'localhost';
    private $username = 'root';
    private $password = 'password123';  // 硬编码密码
    private $database = 'test_db';
    
    public function __construct()
    {
        $this->db = mysqli_connect($this->host, $this->username, $this->password, $this->database);
    }
    
    // 缺少返回类型声明，参数缺少类型声明
    public function getUserById($id)
    {
        // SQL 注入风险：直接拼接用户输入
        $sql = "SELECT * FROM users WHERE id = " . $id;
        $result = mysqli_query($this->db, $sql);
        return mysqli_fetch_assoc($result);
    }
    
    // XSS 漏洞：直接输出用户数据
    public function displayUserName($userId)
    {
        $user = $this->getUserById($userId);
        echo "<div>Welcome, " . $user['name'] . "</div>";  // XSS 风险
        return $user;
    }
    
    // 命令注入风险
    public function processFile($filename)
    {
        // 危险函数：直接使用用户输入执行系统命令
        system("cat " . $filename);
    }
    
    // 性能问题：循环中的数据库查询
    public function getUsersPosts($userIds)
    {
        $posts = array();
        for ($i = 0; $i < count($userIds); $i++) {  // 每次循环都调用 count()
            // N+1 查询问题
            $sql = "SELECT * FROM posts WHERE user_id = " . $userIds[$i];
            $result = mysqli_query($this->db, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                $posts[] = $row;
            }
        }
        return $posts;
    }
    
    // 深层嵌套问题
    public function validateUser($data)
    {
        if (isset($data['name'])) {
            if (strlen($data['name']) > 0) {
                if (strlen($data['name']) < 100) {
                    if (preg_match('/^[a-zA-Z0-9]+$/', $data['name'])) {
                        if (isset($data['email'])) {
                            if (filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                                if (isset($data['age'])) {
                                    if ($data['age'] >= 18) {
                                        return true;
                                    } else {
                                        return false;
                                    }
                                } else {
                                    return false;
                                }
                            } else {
                                return false;
                            }
                        } else {
                            return false;
                        }
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    // 重复代码
    public function createAdminUser($name, $email, $password)
    {
        $hashedPassword = md5($password);  // 不安全的哈希算法
        $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashedPassword', 'admin')";
        mysqli_query($this->db, $sql);
        return mysqli_insert_id($this->db);
    }
    
    public function createRegularUser($name, $email, $password)
    {
        $hashedPassword = md5($password);  // 不安全的哈希算法（重复）
        $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashedPassword', 'user')";
        mysqli_query($this->db, $sql);
        return mysqli_insert_id($this->db);
    }
    
    // 过长的方法，缺少类型声明
    public function processUserData($userData)
    {
        // 过长的行，超过120字符
        $validationRules = array('name' => 'required|min:3|max:50', 'email' => 'required|email|unique:users', 'password' => 'required|min:8|confirmed', 'age' => 'required|integer|min:18|max:120');
        
        $errors = array();
        
        if (empty($userData['name'])) {
            $errors[] = 'Name is required';
        }
        
        if (empty($userData['email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        } else {
            // 检查邮箱是否已存在（SQL 注入风险）
            $checkSql = "SELECT * FROM users WHERE email = '" . $userData['email'] . "'";
            $checkResult = mysqli_query($this->db, $checkSql);
            if (mysqli_num_rows($checkResult) > 0) {
                $errors[] = 'Email already exists';
            }
        }
        
        if (empty($userData['password'])) {
            $errors[] = 'Password is required';
        } elseif (strlen($userData['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        
        if (empty($userData['age'])) {
            $errors[] = 'Age is required';
        } elseif (!is_numeric($userData['age'])) {
            $errors[] = 'Age must be a number';
        } elseif ($userData['age'] < 18) {
            $errors[] = 'Must be at least 18 years old';
        }
        
        if (count($errors) > 0) {
            return array('success' => false, 'errors' => $errors);
        }
        
        // 保存用户（SQL 注入风险）
        $hashedPassword = md5($userData['password']);
        $sql = "INSERT INTO users (name, email, password, age) VALUES ('" . $userData['name'] . "', '" . $userData['email'] . "', '$hashedPassword', " . $userData['age'] . ")";
        mysqli_query($this->db, $sql);
        
        return array('success' => true, 'user_id' => mysqli_insert_id($this->db));
    }
    
    // 不安全的文件操作
    public function uploadFile($fileData)
    {
        $targetPath = '/var/www/uploads/' . $fileData['name'];
        move_uploaded_file($fileData['tmp_name'], $targetPath);  // 未验证文件类型和内容
        return true;
    }
    
    // 使用 eval() 函数（危险）
    public function calculate($expression)
    {
        eval('$result = ' . $expression . ';');  // 代码注入风险
        return $result;
    }
    
    // 性能问题：未优化的大数据处理
    public function generateReport()
    {
        $sql = "SELECT * FROM users";
        $result = mysqli_query($this->db, $sql);
        
        $report = '';
        while ($row = mysqli_fetch_assoc($result)) {
            // 字符串拼接性能问题
            $report = $report . "User: " . $row['name'] . "\n";
            $report = $report . "Email: " . $row['email'] . "\n";
            $report = $report . "-------------------\n";
        }
        
        return $report;
    }
    
    // 缺少错误处理
    public function deleteUser($id)
    {
        $sql = "DELETE FROM users WHERE id = $id";
        mysqli_query($this->db, $sql);
        // 未检查执行结果，未处理错误
    }
    
    // 使用 require_once（应使用自动加载）
    public function loadHelper()
    {
        require_once 'helpers.php';
    }
    
    // 未使用的变量和代码
    public function searchUsers($criteria)
    {
        $unusedVariable = 'this is never used';  // 未使用的变量
        $anotherUnused = array();
        
        $sql = "SELECT * FROM users WHERE 1=1";
        if (!empty($criteria['name'])) {
            $sql .= " AND name LIKE '%" . $criteria['name'] . "%'";  // SQL 注入
        }
        
        $result = mysqli_query($this->db, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    // 魔术数字和硬编码值
    public function getActiveUsers()
    {
        $sql = "SELECT * FROM users WHERE status = 1 LIMIT 100";  // 魔术数字 1 和 100
        $result = mysqli_query($this->db, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    // 不一致的命名风格
    public function GetUserInfo($user_id)  // 方法名使用 PascalCase，应使用 camelCase
    {
        return $this->getUserById($user_id);
    }
    
    public function get_user_posts($userId)  // 方法名使用 snake_case，应使用 camelCase
    {
        return $this->getUsersPosts(array($userId));
    }
}

// 在全局作用域定义函数（应避免）
function sendEmail($to, $subject, $message)
{
    // 未验证邮箱地址
    mail($to, $subject, $message);
}
