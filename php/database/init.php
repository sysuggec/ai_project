<?php
/**
 * 数据库初始化脚本
 */

require_once __DIR__ . '/../vendor/autoload.php';

$dbFile = __DIR__ . '/../database/riskctl.db';
$sqlFile = __DIR__ . '/../database/20260211_create_risk_tables.sql';

try {
    // 创建SQLite数据库连接
    $pdo = new PDO("sqlite:{$dbFile}");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 读取SQL文件
    $sql = file_get_contents($sqlFile);
    
    // 分割SQL语句并执行
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $pdo->exec($statement);
        }
    }

    echo "数据库初始化成功！\n";
    echo "数据库文件: {$dbFile}\n";
    
    // 验证表是否创建成功
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    echo "创建的表: " . implode(', ', $tables) . "\n";
    
} catch (PDOException $e) {
    echo "数据库初始化失败: " . $e->getMessage() . "\n";
    exit(1);
}
