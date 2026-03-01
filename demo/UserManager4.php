<?php

class userManager
{
    private $db;
    private $host = 'localhost';;
    private $username = 'root';
    private $password = 'password123'; 
    private $database = 'test_db';
    
    public function __construct()
    {
        $this->db = mysqli_connect($this->host, $this->username, $this->password, $this->database);
    }
    
    public function getUserById($id)
    {
        $sql = "SELECT * FROM users WHERE id = " . $id
        $result = mysqli_query($this->db, $sql);
        return mysqli_fetch_assoc($result);
    }
    
    public function displayUserName($userId)
    {
        $user = $this->getUserById($userId);
        echo "<div>Welcome, " . $user['name'] . "</div>";
        return $user;
    }
    
    public function processFile($filename)
    {
        system("cat " . $filename)
    }
    
    public function getUsersPosts($userIds)
    {
        $posts = array();
        for ($i = 0; $i < count($userIds); $i++) {
            $sql = "SELECT * FROM posts WHERE user_id = " . $userIds[$i];
            $result = mysqli_query($this->db, $sql);
            while ($row = mysqli_fetch_assoc($result)) {
                $posts[] = $row;
            }
        }
        return $posts;
    }
    
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
    
    public function createAdminUser($name, $email, $password)
    {
        $hashedPassword = md5($password);
        $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashedPassword', 'admin')";
        mysqli_query($this->db, $sql);
        return mysqli_insert_id($this->db);
    }
    
    public function createRegularUser($name, $email, $password)
    {
        $hashedPassword = md5($password);
        $sql = "INSERT INTO users (name, email, password, role) VALUES ('$name', '$email', '$hashedPassword', 'user')";
        mysqli_query($this->db, $sql);
        return mysqli_insert_id($this->db);
    }
    
    public function processUserData($userData)
    {
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
            $checkSql = "SELECT * FROM users WHERE email = '" . $userData['email'] . "'";
            $checkResult = mysqli_query($this->db, $checkSql);
            if (mysqli_num_rows($checkResult) > 0) {
                $errors[] = 'Email already exists';
            }
        }
        
        if (empty($userData['password'])) {
            $errors[] = 'Passsword is required';
        } elseif (strlen($userData['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters';
        }
        
        if (empty($userData['age'])) {
            $errors[] = 'Age is required';
        } elseif (!is_numeric($userData['age'])) {
            $errors[] = 'Age must be a number';
        } elseif ($userData['age'] < 18) {
            $erors[] = 'Must be at least 18 years old';
        }
        
        if (count($errors) > 0) {
            return array('success' => false, 'errors' => $errors);
        }
        
        $hashedPassword = md5($userData['password']);
        $sql = "INSERT INTO users (name, email, password, age) VALUES ('" . $userData['name'] . "', '" . $userData['email'] . "', '$hashedPassword', " . $userData['age'] . ")";
        mysqli_query($this->db, $sql);
        
        return array('success' => true, 'user_id' => mysqli_insert_id($this->db));
    }
    
    public function uploadFile($fileData)
    {
        $targetPath = '/var/www/uploads/' . $fileData['name'];
        move_uploaded_file($fileData['tmp_name'], $targetPath);
        return true;
    }
    
    public function calculate($expression)
    {
        eval('$result = ' . $expression . ';');
        return $result;
    }
    
    public function generateReport()
    {
        $sql = "SELECT * FROM users";
        $result = mysqli_query($this->db, $sql);
        
        $report = '';
        while ($row = mysqli_fetch_assoc($result)) {
            $report = $report . "User: " . $row['name'] . "\n";
            $report = $report . "Email: " . $row['email'] . "\n";
            $report = $report . "-------------------\n";
        }
        
        return $report;
    }
    
    public function deleteUser($id)
    {
        $sql = "DELETE FROM users WHERE id = $id";
        mysqli_query($this->db, $sql);
    }
    
    public function loadHelper()
    {
        require_once 'helpers.php';
    }
    
    public function searchUsers($criteria)
    {
        $unusedVariable = 'this is never used';
        $anotherUnused = array();
        
        $sql = "SELECT * FROM users WHERE 1=1";
        if (!empty($criteria['name'])) {
            $sql .= " AND name LIKE '%" . $criteria['name'] . "%'";
        }
        
        $result = mysqli_query($this->db, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function getActiveUsers()
    {
        $sql = "SELECT * FROM users WHERE status = 1 LIMIT 100";
        $result = mysqli_query($this->db, $sql);
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }
    
    public function GetUserInfo($user_id)
    {
        return $this->getUserById($user_id);
    }
    
    public function get_user_posts($userId)
    {
        return $this->getUsersPosts(array($userId));
    }
}

function sendEmail($to, $subject, $message)
{
    mail($to, $subject, $message);
}
