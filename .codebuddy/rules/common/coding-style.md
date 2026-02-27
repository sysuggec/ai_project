---
description: 通用代码风格描述，编写和 review代码的时候需要参考
alwaysApply: false
enabled: true
updatedAt: 2026-02-27T04:16:16.209Z
provider: 
---

# 通用编程风格规范

## 不可变性 (关键原则)

**始终创建新对象，永远不要修改现有对象：**

```
// 错误示例
modify(original, field, value) // 原地修改 original 对象

// 正确示例
update(original, field, value) // 返回带有变更的新副本
```

**理由：** 不可变数据可以防止隐藏的副作用，使调试更容易，并支持安全的并发操作。

## 文件组织

**多个小文件 > 少量大文件：**

- 高内聚，低耦合
- 典型文件 200-400 行，最大不超过 800 行
- 从大型模块中提取工具函数
- 按功能/领域组织，而非按类型组织
- 每个文件专注于单一职责

## 错误处理

**始终全面处理错误：**

- 在每一层显式处理错误
- 在面向 UI 的代码中提供用户友好的错误消息
- 在服务端记录详细的错误上下文
- 永远不要静默吞掉错误
- 使用适当的错误类型和错误码

```
// 错误示例
try {
    doSomething()
} catch (e) {
    // 什么都不做 - 禁止！
}

// 正确示例
try {
    doSomething()
} catch (e) {
    logger.error('操作失败', { context: params, error: e })
    throw new ApplicationError('操作失败，请稍后重试', e)
}
```

## 输入验证

**始终在系统边界进行验证：**

- 处理前验证所有用户输入
- 使用基于 Schema 的验证（如 Zod、Joi、Pydantic 等）
- 快速失败，提供清晰的错误消息
- 永远不要信任外部数据（API 响应、用户输入、文件内容）
- 验证数据类型、格式、范围和业务规则

```
// 验证示例
function processUser(data: unknown) {
    const schema = z.object({
        name: z.string().min(1).max(100),
        email: z.string().email(),
        age: z.number().min(0).max(150).optional()
    })
    
    const validated = schema.parse(data) // 无效时抛出错误
    // 继续处理验证后的数据
}
```

## 代码质量检查清单

在标记工作完成前检查：

- [ ] 代码可读且命名清晰
- [ ] 函数简小（<50 行）
- [ ] 文件聚焦（<800 行）
- [ ] 无深层嵌套（>4 层）
- [ ] 正确的错误处理
- [ ] 无硬编码值（使用常量或配置）
- [ ] 无可变操作（使用不可变模式）
- [ ] 添加了必要的注释和文档
- [ ] 测试覆盖关键路径

## 命名约定

### 通用原则
- 使用描述性名称，避免缩写
- 布尔变量使用 `is`、`has`、`can` 前缀
- 集合类型使用复数形式
- 回调函数使用动词形式

### 类型和接口
- 类型名使用 PascalCase：`UserAccount`、`PaymentMethod`
- 接口名使用 PascalCase，可加 `I` 前缀或 `Interface` 后缀
- 类型参数使用单字母或 PascalCase：`T`、`TKey`、`TValue`

### 常量和枚举
- 常量使用 UPPER_SNAKE_CASE：`MAX_RETRY_COUNT`
- 枚举使用 PascalCase：`enum Status { Active, Inactive }`

## 函数设计

### 单一职责
- 每个函数只做一件事
- 函数名应该描述其行为
- 避免副作用，纯函数优先

### 参数设计
- 参数数量不超过 3-4 个
- 多参数时使用对象参数
- 为可选参数提供默认值
- 使用具名参数提高可读性

```
// 错误示例
function createUser(name, email, age, address, phone, role) { ... }

// 正确示例
interface CreateUserOptions {
    name: string
    email: string
    age?: number
    address?: Address
    phone?: string
    role?: Role
}

function createUser(options: CreateUserOptions) { ... }
```

## 注释规范

### 何时注释
- 复杂业务逻辑的解释
- 非显而易见的算法说明
- API 和公共接口的文档
- 临时解决方案和 TODO 标记

### 何时不注释
- 显而易见的代码
- 可以通过命名解释的逻辑
- 版本控制中已记录的信息

### 注释格式
```
// TODO: [描述待完成的工作]
// FIXME: [描述需要修复的问题]
// NOTE: [重要的实现细节或注意事项]
// HACK: [临时解决方案，需要后续优化]
```

## 避免的反模式

1. **上帝对象** - 做太多事情的大类
2. **面条代码** - 过度复杂的控制流
3. **魔法数字** - 未命名且无解释的常量
4. **过早优化** - 在不需要时优化代码
5. **复制粘贴编程** - 重复代码而非提取通用逻辑
6. **深层嵌套** - 超过 4 层的条件嵌套
7. **全局状态** - 过度使用全局变量
8. **过度抽象** - 为简单的逻辑创建不必要的抽象层

## 性能考虑

- 避免在循环中进行昂贵的操作
- 使用适当的数据结构
- 延迟加载和按需计算
- 缓存合理的结果
- 注意内存泄漏和资源释放

## 安全实践

- 最小权限原则
- 输入验证和输出编码
- 避免拼接 SQL 和命令
- 敏感数据加密存储
- 安全的默认配置
- 定期更新依赖包