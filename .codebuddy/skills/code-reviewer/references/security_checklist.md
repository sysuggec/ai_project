# PHP Security Checklist

A comprehensive security review checklist for PHP code auditing.

## SQL Injection Prevention

### ✅ What to Check
- [ ] All database queries use prepared statements
- [ ] No direct variable interpolation in SQL strings
- [ ] Parameter binding used instead of string concatenation
- [ ] ORM/query builder methods used correctly

### ❌ Vulnerable Patterns
```php
// BAD: Direct interpolation
$sql = "SELECT * FROM users WHERE id = " . $_GET['id'];

// BAD: Variable in query string
$query = "SELECT * FROM users WHERE name = '$name'";

// BAD: Unsanitized user input
$results = $db->query("SELECT * FROM products WHERE category = '" . $_POST['category'] . "'");
```

### ✅ Secure Patterns
```php
// GOOD: Prepared statements
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = :id");
$stmt->execute(['id' => $id]);

// GOOD: Parameter binding
$stmt = $db->prepare("SELECT * FROM products WHERE category = ?");
$stmt->bind_param("s", $category);
$stmt->execute();
```

## Cross-Site Scripting (XSS) Prevention

### ✅ What to Check
- [ ] All output is escaped using `htmlspecialchars()` or `htmlentities()`
- [ ] Context-appropriate encoding (HTML, JavaScript, URL, CSS)
- [ ] Content Security Policy (CSP) headers configured
- [ ] User input not directly echoed

### ❌ Vulnerable Patterns
```php
// BAD: Direct output
echo $_GET['message'];

// BAD: Unescaped variable
<div><?= $userInput ?></div>

// BAD: Insecure JavaScript
<script>var name = '<?= $name ?>';</script>
```

### ✅ Secure Patterns
```php
// GOOD: HTML context
echo htmlspecialchars($input, ENT_QUOTES, 'UTF-8');

// GOOD: In template
<div><?= htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8') ?></div>

// GOOD: JavaScript context
<script>var name = <?= json_encode($name) ?>;</script>
```

## Input Validation

### ✅ What to Check
- [ ] All user input validated (GET, POST, COOKIE, REQUEST)
- [ ] Whitelist validation used over blacklist
- [ ] Type checking and length limits enforced
- [ ] `filter_input()` and `filter_var()` used

### Validation Patterns
```php
// Validate integer
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// Validate email
$email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);

// Validate with options
$options = ['options' => ['min_range' => 1, 'max_range' => 100]];
$age = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT, $options);

// Sanitize string
$name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);

// Custom validation
if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
    throw new InvalidArgumentException('Invalid username format');
}
```

## Authentication & Authorization

### ✅ What to Check
- [ ] Passwords hashed using `password_hash()` (bcrypt/Argon2)
- [ ] Password verification using `password_verify()`
- [ ] Session management secure
- [ ] Access control enforced at all entry points
- [ ] Login attempts rate-limited
- [ ] Account lockout mechanism after failed attempts

### Secure Authentication Patterns
```php
// Password hashing
$hash = password_hash($password, PASSWORD_DEFAULT);

// Password verification
if (password_verify($password, $storedHash)) {
    // Success
}

// Session regeneration on login
session_regenerate_id(true);

// Secure session configuration
session_set_cookie_params([
    'lifetime' => 3600,
    'path' => '/',
    'domain' => $_SERVER['HTTP_HOST'],
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);
```

## Cross-Site Request Forgery (CSRF) Protection

### ✅ What to Check
- [ ] All forms include CSRF token
- [ ] Token validated on POST/PUT/DELETE requests
- [ ] Tokens are unique and unpredictable
- [ ] Tokens expire after use or timeout

### CSRF Implementation
```php
// Generate token
function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate token
function validateCsrfToken(string $token): bool {
    return isset($_SESSION['csrf_token']) && 
           hash_equals($_SESSION['csrf_token'], $token);
}

// In form
<input type="hidden" name="csrf_token" value="<?= generateCsrfToken() ?>">

// On submission
if (!validateCsrfToken($_POST['csrf_token'] ?? '')) {
    http_response_code(403);
    exit('CSRF token validation failed');
}
```

## File Operations Security

### ✅ What to Check
- [ ] No direct user input in file paths
- [ ] File paths validated and sanitized
- [ ] Allowed directories whitelisted
- [ ] File extensions validated
- [ ] `basename()` used to prevent path traversal
- [ ] Upload directory outside web root

### Secure File Handling
```php
// Whitelist allowed extensions
$allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions)) {
    throw new Exception('Invalid file type');
}

// Sanitize path
$filename = basename($_POST['filename']);
$filepath = '/var/www/uploads/' . $filename;

// Validate within allowed directory
$realpath = realpath($filepath);
if ($realpath === false || strpos($realpath, '/var/www/uploads/') !== 0) {
    throw new Exception('Invalid file path');
}
```

## Command Injection Prevention

### ✅ What to Check
- [ ] No user input in shell commands
- [ ] `escapeshellarg()` used for arguments
- [ ] `escapeshellcmd()` used for commands
- [ ] Prefer native PHP functions over shell commands

### Secure Command Execution
```php
// BAD: Direct user input
system("convert " . $_GET['file'] . " output.png");

// GOOD: Escaped arguments
$file = escapeshellarg($_GET['file']);
system("convert " . $file . " output.png");

// BETTER: Use native PHP functions
$image = imagecreatefromstring(file_get_contents($_GET['file']));
imagepng($image, 'output.png');
```

## Session Security

### ✅ What to Check
- [ ] Session cookies are HttpOnly
- [ ] Session cookies are Secure (HTTPS only)
- [ ] SameSite attribute configured
- [ ] Session ID regenerated on login
- [ ] Session timeout implemented
- [ ] Session data validated

### Secure Session Configuration
```php
// Configure before session_start()
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.cookie_samesite', 'Strict');
ini_set('session.use_strict_mode', 1);

// Regenerate on privilege change
session_regenerate_id(true);

// Set timeout
ini_set('session.gc_maxlifetime', 3600); // 1 hour
```

## Cryptography

### ✅ What to Check
- [ ] Modern algorithms used (AES-256-GCM, SHA-256+)
- [ ] Random values generated using `random_bytes()` or `openssl_random_pseudo_bytes()`
- [ ] Keys managed securely (not hardcoded)
- [ ] IVs/nonces used correctly
- [ ] Deprecated functions avoided (MD5, SHA1 for security)

### Secure Cryptography
```php
// Generate random bytes
$token = bin2hex(random_bytes(32));

// Symmetric encryption (AES-256-GCM)
$key = random_bytes(32);
$iv = random_bytes(16);
$tag = '';
$encrypted = openssl_encrypt($data, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

// Hashing
$hash = hash('sha256', $data);

// HMAC for message authentication
$hmac = hash_hmac('sha256', $data, $secretKey);
```

## Error Handling & Logging

### ✅ What to Check
- [ ] Error messages don't expose sensitive information
- [ ] Errors logged to secure location
- [ ] Custom error handlers configured
- [ ] Debug mode disabled in production
- [ ] Stack traces hidden from users

### Secure Error Handling
```php
// Production configuration
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/var/log/php/error.log');

// Custom error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    error_log("Error: $errstr in $errfile:$errline");
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
});

// Exception handler
set_exception_handler(function($exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo "An error occurred. Please try again later.";
});
```

## HTTP Security Headers

### ✅ What to Check
- [ ] Content-Security-Policy configured
- [ ] X-Frame-Options set
- [ ] X-Content-Type-Options set
- [ ] Strict-Transport-Security enabled
- [ ] X-XSS-Protection enabled

### Security Headers
```php
header("Content-Security-Policy: default-src 'self'");
header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Strict-Transport-Security: max-age=31536000; includeSubDomains");
header("X-XSS-Protection: 1; mode=block");
header("Referrer-Policy: strict-origin-when-cross-origin");
```

## Dependency Security

### ✅ What to Check
- [ ] Composer dependencies up to date
- [ ] No known vulnerabilities in dependencies
- [ ] Dependencies from trusted sources
- [ ] Lock file committed to version control

### Dependency Management
```bash
# Check for vulnerabilities
composer audit

# Update dependencies
composer update

# Review outdated packages
composer outdated
```

## Data Protection

### ✅ What to Check
- [ ] Sensitive data encrypted at rest
- [ ] Data minimization practiced
- [ ] PII handled according to regulations (GDPR, etc.)
- [ ] Backup encryption enabled
- [ ] Secure deletion of sensitive data
