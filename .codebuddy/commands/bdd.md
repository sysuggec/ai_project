# bdd

使用 Behavior-Driven Development (BDD) 方法开发功能。

## 使用方式

```
bdd <目标目录> <功能描述>
```

## 参数

- `目标目录`: 代码存放目录（如 `src/Services`）
- `功能描述`: 要开发的功能描述（如 `用户登录服务`）

## 执行步骤

1. **加载 BDD 技能**
   - 使用 `bdd-skill` 技能加载 BDD 工作流程指南

2. **分析需求**
   - 理解业务需求和用户故事
   - 识别核心业务场景
   - 定义验收标准

3. **📝 DEFINE - 编写场景**
   - 创建特性文件 `features/<功能名>.feature`
   - 使用 Gherkin 语法编写场景（Given/When/Then）
   - 包含正常流程和边界情况

4. **⚙️ AUTOMATE - 实现步骤定义**
   - 创建上下文文件 `features/bootstrap/FeatureContext.php`
   - 为每个 Gherkin 步骤编写 PHP 实现
   - 运行场景确认步骤未实现或失败

5. **🔧 IMPLEMENT - 编写生产代码**
   - 创建生产代码文件 `src/<目标路径>/<类名>.php`
   - 编写让场景通过的代码
   - 运行场景确认通过

6. **循环迭代**
   - 对每个新场景重复 DEFINE-AUTOMATE-IMPLEMENT 循环

## 示例

```
bdd src/Service/AuthService 用户登录认证服务
bdd src/Service/PaymentService 支付处理服务，支持多种支付方式
bdd src/Validator 表单验证器，支持邮箱、手机号和身份证
```

## 编码规范

开发过程中必须遵循：
- PHP 编码规范（PSR-1/PSR-2/PSR-14）
- 使用 `declare(strict_types=1);`
- 场景使用业务语言，避免技术细节
- 步骤定义保持可复用

## 输出

在指定目录下创建：
```
features/
└── <功能名>.feature           # Gherkin 特性文件

features/bootstrap/
└── FeatureContext.php         # 步骤定义

src/<目标路径>/
├── <类名>.php                 # 生产代码
└── <接口名>.php               # 接口定义（如需要）
```

## Gherkin 语法模板

```gherkin
Feature: <功能名称>
  As a <角色>
  I want to <目标>
  So that <价值>

  Scenario: <场景名称>
    Given <前置条件>
    When <用户行为>
    Then <预期结果>
```

## BDD 原则

1. **业务驱动**: 从业务需求出发，使用业务语言描述行为
2. **协作沟通**: 场景应该让非技术人员也能理解
3. **活文档**: 特性文件既是测试也是文档
4. **单一职责**: 每个场景只验证一个行为
