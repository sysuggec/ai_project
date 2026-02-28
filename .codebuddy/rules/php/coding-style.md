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

## 命名空间规范

### 命名空间结构
- 命名空间必须与目录结构一致（遵循 PSR-4）
- 根命名空间通常为项目名称或厂商名：`App\`、`Acme\`
- 使用有意义的子命名空间划分功能模块：

```
App\
├── Controllers\      # 控制器
├── Models\           # 数据模型
├── Services\         # 业务逻辑服务
├── Repositories\     # 数据访问层
├── Exceptions\       # 异常类
├── Helpers\          # 辅助类
├── Traits\           # Trait 复用代码
├── Interfaces\       # 接口定义
└── Config\           # 配置类
```

### use 语句规范
- 每个 `use` 语句导入一个类、函数或常量
- `use` 语句块按字母顺序排列
- `use` 语句块后必须有空行
- 避免使用别名（`as`）除非存在命名冲突：

```php
<?php
declare(strict_types=1);

namespace App\Services;

use App\Exceptions\UserNotFoundException;
use App\Interfaces\UserRepositoryInterface;
use App\Models\User;

class UserService
{
    // ...
}
```

### 导入规则
- **必须使用 `use` 导入**，禁止在代码中使用完全限定名：

```php
// ❌ 错误：使用完全限定名
public function get(): \App\Models\User
{
    return new \App\Models\User();
}

// ✅ 正确：使用 use 导入
use App\Models\User;

public function get(): User
{
    return new User();
}
```

### 多级命名空间
- 使用子命名空间组织相关类：

```php
namespace App\Services\Payment;
namespace App\Services\Payment\Gateways;
namespace App\Services\Payment\Validators;
```

### 全局命名空间
- 避免在全局命名空间中定义类、函数、常量
- 如需使用内置类，直接使用类名（无需导入）：

```php
// 内置类直接使用
$date = new DateTime();
$error = new Exception('Error');

// 或显式导入（推荐）
use DateTime;
use Exception;

$date = new DateTime();
$error = new Exception('Error');
```

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

## 文件加载

- **避免使用 `require`、`require_once`、`include`、`include_once`**
- 使用 Composer 自动加载（PSR-4）加载类文件
- 如需动态加载，使用 `class_exists()` 配合自动加载：
  ```php
  // 推荐：依赖 Composer 自动加载
  use App\Services\UserService;

  // 动态加载类（如插件系统）
  if (class_exists($className)) {
      $instance = new $className();
  }
  ```
- 配置 `composer.json` 的 `autoload` 和 `autoload-dev`：

## PHP 版本兼容性

- **目标版本：PHP 7.4**（即使本地开发环境使用 PHP 8+）
- 所有代码必须兼容 PHP 7.4 语法和特性
- Composer 依赖版本需确保支持 PHP 7.4：
  ```json
  {
      "require": {
          "php": "^7.4|^8.0",
          "laravel/framework": "^8.0"
      }
  }
  ```
- **PHP 7.4 与 8.x 差异注意事项**：
  - 可使用命名参数（PHP 8+），但 PHP 7.4 不支持
  - 可使用联合类型（PHP 8+），PHP 7.4 需分开处理或使用 `@var` 注解
  - 可使用 `match` 表达式（PHP 8+），PHP 7.4 需用 `switch`
  - 可使用 `nullsafe` 操作符 `?->`（PHP 8+），PHP 7.4 需用传统 null 检查
  - 可使用构造函数属性提升（PHP 8+），PHP 7.4 需传统写法
  - 属性类型声明可使用 `mixed`（PHP 8+），PHP 7.4 不支持
  - `str_contains()`、`str_starts_with()`、`str_ends_with()` 为 PHP 8+ 新增，需使用 polyfill 或替代方案

  ```php
  // PHP 8+ 写法（不兼容 7.4）
  public function __construct(private UserRepository $repo) {}

  // PHP 7.4 兼容写法
  private UserRepository $repo;
  public function __construct(UserRepository $repo)
  {
      $this->repo = $repo;
  }
  ```

  ```php
  // PHP 8+ 写法（不兼容 7.4）
  $name = $user?->profile?->name;

  // PHP 7.4 兼容写法
  $name = $user && $user->profile ? $user->profile->name : null;
  ```

## 代码质量

- 保持函数和方法简短（通常不超过 50 行）
- 一个方法只做一件事
- 避免深层嵌套（通常不超过 3 层）
- 提取重复代码为独立方法
- 使用有意义的变量和方法名