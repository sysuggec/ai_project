---
description: php 代码规范，编写和 review php 代码的时候需要参考
alwaysApply: false
enabled: true
updatedAt: 2026-02-27T04:16:20.185Z
provider: 
---

# PHP 编码规范
> This file extends [common/coding-style.md](../common/coding-style.md) with PHP specific content.

## PSR 标准

本项目遵循 PHP-FIG 的 PSR 编码标准：

### PSR-1: 基本编码标准
- 文件必须只使用 `<?php` 或 `<?=` 标签
- 文件必须只使用 UTF-8 without BOM 编码
- 命名空间和类必须遵循 "autoload" PSR
- 类名必须是 `StudlyCaps`
- 常量名必须是 `UPPER_CASE`
- 方法名必须是 `camelCase`

### PSR-2: 编码风格指南
- 使用 4 个空格缩进，不使用制表符
- 文件末尾保留一个空行
- 行长度不超过 120 字符
- `namespace` 声明后必须有一个空行
- `use` 声明块后必须有一个空行
- 类和方法的左花括号 `{` 独占一行，右花括号 `}` 必须独占一行
- 控制结构关键字后必须有一个空格
- 控制结构的左花括号 `{` 在同一行，右花括号 `}` 独占一行

### PSR-4: 自动加载标准
- 使用完全限定命名空间和类名
- 根命名空间与 vendor 目录关联
- 命名空间分隔符转换为目录分隔符
- 文件扩展名必须为 `.php`

## 命名约定

### 类名
- 使用 PascalCase（大驼峰）：`UserService`, `DatabaseConnection`
- 接口类名建议加 `Interface` 后缀：`UserInterface`
- 抽象类名建议加 `Abstract` 前缀：`AbstractController`

### 方法名
- 使用 camelCase（小驼峰）：`getUserById()`, `saveUserData()`
- 布尔返回值方法名以 `is`、`has`、`can` 开头：`isValid()`, `hasPermission()`

### 变量名
- 使用 camelCase：`$userName`, `$isActive`
- 常量使用 UPPER_CASE：`MAX_RETRIES`, `DEFAULT_TIMEOUT`

## 代码组织

### 文件结构
```php
<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\User;

/**
 * 用户服务类
 */
class UserService
{
    // 常量
    private const MAX_RETRIES = 3;

    // 属性
    private UserRepository $repository;

    // 构造函数
    public function __construct(UserRepository $repository)
    {
        $this->repository = $repository;
    }

    // 公共方法
    public function getUser(int $id): ?User
    {
        // 实现代码
    }

    // 私有方法
    private function validateUser(User $user): bool
    {
        // 实现代码
    }
}
```

### 访问控制
- 始终显式声明访问修饰符（`public`、`protected`、`private`）
- 优先使用 `private`，仅在必要时使用 `protected` 或 `public`

## 类型声明

### 严格类型模式
- 所有 PHP 文件应包含 `declare(strict_types=1);`
- 尽可能为所有函数和方法添加类型声明
- 使用返回类型声明

### 示例
```php
<?php
declare(strict_types=1);

function calculateTotal(float $price, int $quantity): float
{
    return $price * $quantity;
}

function getUserData(int $userId): ?array
{
    // 返回数组或 null
}
```

## 最佳实践

### 依赖注入
- 使用构造函数注入依赖
- 避免在方法中直接实例化类
- 使用接口编程，而非具体实现

### 异常处理
- 使用类型化异常类
- 抛出有意义的异常消息
- 适当捕获和处理异常

### 数据验证
- 验证所有输入数据
- 使用专门的验证类或库
- 在最接近数据源的地方进行验证

### 注释和文档
- 使用 PHPDoc 格式注释类、方法和复杂逻辑
- 为复杂算法添加解释性注释
- 避免注释显而易见的代码

## 安全规范

- 始终使用预处理语句防止 SQL 注入
- 对用户输入进行验证和过滤
- 使用 `htmlspecialchars()` 或模板引擎转义输出
- 避免直接使用 `eval()`、`exec()` 等危险函数
- 敏感信息（密码、密钥）不要硬编码
- 使用环境变量存储配置信息

## 错误处理

- 在生产环境中关闭 `display_errors`
- 配置适当的错误报告级别
- 使用 `try-catch` 块处理可恢复错误
- 记录错误日志以便调试

## 代码质量

- 保持函数和方法简短（通常不超过 50 行）
- 一个方法只做一件事
- 避免深层嵌套（通常不超过 3 层）
- 提取重复代码为独立方法
- 使用有意义的变量和方法名