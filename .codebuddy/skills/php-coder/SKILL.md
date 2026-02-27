---
name: s-php-coder
description: 编写任何 PHP 代码时应使用此技能。它提供标准化工作流程、PSR 合规检查、代码模板和高质量 PHP 开发的最佳实践。
---

# PHP 代码编写器

## 概述

此技能提供编写符合项目标准和 PSR 规范的 PHP 代码的完整工作流程。在创建、修改或审查 PHP 代码时使用此技能，以确保一致性、类型安全、安全性和可维护性。

## 前置准备

### 规则文件引用

编写 PHP 代码前，参考以下规则文件：
- PHP 编码规范：`.codebuddy/rules/php/coding-style.md`
- 通用编码实践：`.codebuddy/rules/common/coding-style.md`

### 理解需求

- 确认功能需求和业务逻辑
- 明确代码位置（默认 `src/` 目录）
- 了解依赖关系和接口要求

## 编写工作流程

### 步骤 1：设计阶段

- 确定类名和方法名（遵循命名规范）
- 规划文件结构和依赖关系
- 应用单一职责原则
- 设计接口和抽象层（如需要）

### 步骤 2：代码实现

#### 基本要求检查清单
- [ ] 使用 `<?php` 开头标签
- [ ] 添加 `declare(strict_types=1);`
- [ ] 正确的命名空间声明
- [ ] 使用 `use` 语句导入依赖
- [ ] 类名使用 PascalCase
- [ ] 方法名使用 camelCase
- [ ] 常量使用 UPPER_CASE

#### 类型安全要求
- [ ] 为所有参数添加类型声明
- [ ] 为所有方法添加返回类型
- [ ] 使用 `?Type` 表示可空类型
- [ ] 避免混合类型（mixed）

#### 代码组织
- [ ] 文件不超过 800 行
- [ ] 方法不超过 50 行
- [ ] 嵌套不超过 3 层
- [ ] 按功能分组：常量 → 属性 → 构造函数 → 公共方法 → 私有方法

#### 错误处理
- [ ] 使用 try-catch 处理可恢复错误
- [ ] 抛出有意义的异常
- [ ] 记录错误上下文
- [ ] 永不静默吞掉异常

#### 安全实践
- [ ] 验证所有输入数据
- [ ] 使用预处理语句防 SQL 注入
- [ ] 转义输出内容
- [ ] 不硬编码敏感信息

### 步骤 3：文档注释

使用 PHPDoc 格式为类、方法和复杂逻辑添加注释：

```php
/**
 * 类或方法的简短描述
 * 
 * 详细描述（如需要）
 * 
 * @param Type $paramName 参数说明
 * @return Type 返回值说明
 * @throws ExceptionType 异常说明
 */
```

### 步骤 4：代码审查

实现完成后，验证：

#### 质量检查清单
- [ ] 代码可读且命名清晰
- [ ] 遵循 PSR-1/PSR-2/PSR-4 标准
- [ ] 无硬编码值（使用常量）
- [ ] 使用依赖注入
- [ ] 添加必要注释
- [ ] 无安全漏洞

#### 性能检查清单
- [ ] 无循环中的昂贵操作
- [ ] 使用适当的数据结构
- [ ] 无明显内存泄漏风险

## 代码模板

### 类文件模板

```php
<?php
declare(strict_types=1);

namespace App\{Module};

use App\{Dependency};

/**
 * {类描述}
 */
class {ClassName}
{
    private const {CONSTANT_NAME} = {value};

    private {Type} $property;

    public function __construct({Type} $dependency)
    {
        $this->property = $dependency;
    }

    public function {methodName}({Type} $param): {ReturnType}
    {
        // 实现
    }

    private function {helperMethod}(): void
    {
        // 辅助方法
    }
}
```

### 服务类模板

```php
<?php
declare(strict_types=1);

namespace App\Services;

use App\Repositories\{Entity}Repository;
use App\Exceptions\{Entity}NotFoundException;

/**
 * {实体}服务类
 */
class {Entity}Service
{
    public function __construct(
        private {Entity}Repository $repository
    ) {}

    public function getById(int $id): {Entity}
    {
        $entity = $this->repository->find($id);

        if ($entity === null) {
            throw new {Entity}NotFoundException(
                "{实体} ID {$id} 未找到"
            );
        }

        return $entity;
    }
}
```

### 控制器模板

```php
<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Services\{Entity}Service;
use App\Validators\{Entity}Validator;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * {实体}控制器
 */
class {Entity}Controller
{
    public function __construct(
        private {Entity}Service $service
    ) {}

    public function show(
        ServerRequestInterface $request,
        ResponseInterface $response,
        array $args
    ): ResponseInterface {
        $id = (int) $args['id'];
        $entity = $this->service->getById($id);

        // 返回响应
        return $response;
    }
}
```

## 常见模式

### 依赖注入

```php
// 构造函数注入（推荐）
public function __construct(
    private LoggerInterface $logger,
    private UserRepository $users
) {}

// 避免直接实例化
// ❌ $repository = new UserRepository();
// ✅ 通过构造函数注入
```

### 数据验证

```php
public function create(array $data): User
{
    // 验证必填字段
    $required = ['name', 'email', 'password'];
    foreach ($required as $field) {
        if (empty($data[$field])) {
            throw new InvalidArgumentException(
                "字段 '{$field}' 是必需的"
            );
        }
    }

    // 验证格式
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        throw new InvalidArgumentException('邮箱格式无效');
    }

    // 创建实体
    return $this->repository->create($data);
}
```

### 异常处理

```php
public function process(int $id): Result
{
    try {
        $data = $this->fetchData($id);
        return $this->transform($data);
    } catch (DatabaseException $e) {
        $this->logger->error('数据库错误', [
            'id' => $id,
            'error' => $e->getMessage()
        ]);
        throw new ServiceException(
            '数据处理失败',
            0,
            $e
        );
    }
}
```

## 完成标准

代码完成的标志：
1. ✅ 遵循所有 PSR 标准
2. ✅ 包含完整类型声明
3. ✅ 有适当的错误处理
4. ✅ 添加必要注释
5. ✅ 无安全漏洞
6. ✅ 通过所有检查清单项
