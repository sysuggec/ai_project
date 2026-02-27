---
description: 复杂任务的顺序agent工作流
---

# 编排命令

复杂任务的顺序agent工作流。

## 用法

`/orchestrate [工作流类型] [任务描述]`

## 工作流类型

### feature（功能）
完整功能实现工作流：
```
planner -> tdd-guide -> code-reviewer -> security-reviewer
```

### bugfix（修复）
Bug调查和修复工作流：
```
planner -> tdd-guide -> code-reviewer
```

### refactor（重构）
安全重构工作流：
```
architect -> code-reviewer -> tdd-guide
```

### security（安全）
安全聚焦审查：
```
security-reviewer -> code-reviewer -> architect
```

## 执行模式

对工作流中的每个agent：

1. **调用agent**，携带前一个agent的上下文
2. **收集输出**，作为结构化交接文档
3. **传递给下一个agent**
4. **汇总结果**到最终报告

## 交接文档格式

agent之间创建交接文档：

```markdown
## 交接：[前一个agent] -> [下一个agent]

### 上下文
[已做工作的摘要]

### 发现
[关键发现或决策]

### 修改的文件
[涉及的文件列表]

### 未解决问题
[需要下一个agent解决的未决项]

### 建议
[建议的后续步骤]
```

## 示例：功能工作流

```
/orchestrate feature "添加用户认证"
```

执行：

1. **规划Agent**
   - 分析需求
   - 创建实现计划
   - 识别依赖
   - 输出：`交接：planner -> tdd-guide`

2. **TDD引导Agent**
   - 读取规划交接
   - 先写测试
   - 实现以通过测试
   - 输出：`交接：tdd-guide -> code-reviewer`

3. **代码审查Agent**
   - 审查实现
   - 检查问题
   - 建议改进
   - 输出：`交接：code-reviewer -> security-reviewer`

4. **安全审查Agent**
   - 安全审计
   - 漏洞检查
   - 最终批准
   - 输出：最终报告

## 最终报告格式

```
编排报告
====================
工作流：feature
任务：添加用户认证
Agent：planner -> tdd-guide -> code-reviewer -> security-reviewer

摘要
-------
[一段话摘要]

Agent输出
-------------
规划：[摘要]
TDD引导：[摘要]
代码审查：[摘要]
安全审查：[摘要]

变更文件
-------------
[所有修改的文件列表]

测试结果
------------
[测试通过/失败摘要]

安全状态
---------------
[安全发现]

建议
--------------
[可发布 / 需要工作 / 阻塞]
```

## 并行执行

对于独立检查，并行运行agent：

```markdown
### 并行阶段
同时运行：
- code-reviewer（质量）
- security-reviewer（安全）
- architect（设计）

### 合并结果
将输出合并到单一报告
```

## 参数

$ARGUMENTS:
- `feature <描述>` - 完整功能工作流
- `bugfix <描述>` - Bug修复工作流
- `refactor <描述>` - 重构工作流
- `security <描述>` - 安全审查工作流
- `custom <agents> <描述>` - 自定义agent序列

## 自定义工作流示例

```
/orchestrate custom "architect,tdd-guide,code-reviewer" "重新设计缓存层"
```

## 提示

1. 复杂功能**从planner开始**
2. 合并前**始终包含code-reviewer**
3. 对于认证/支付/PII**使用security-reviewer**
4. **保持交接简洁** - 专注于下一个agent需要的内容
5. 如需要，在agent之间**运行验证**
