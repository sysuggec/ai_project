---
description: 将相关instincts聚类为技能、命令或agents
---

# Evolve命令

分析instincts并将相关的聚类为更高级别的结构：
- **命令**：当instincts描述用户调用的动作时
- **技能**：当instincts描述自动触发的行为时
- **Agents**：当instincts描述复杂、多步骤过程时

## 用法

```
/evolve                    # 分析所有instincts并建议进化
/evolve --domain testing   # 只进化测试领域的instincts
/evolve --dry-run          # 显示会创建什么但不创建
/evolve --threshold 5      # 需要5+相关instincts才能聚类
```

## 进化规则

### → 命令（用户调用）
当instincts描述用户会明确请求的动作时：
- 多个关于"当用户请求..."的instincts
- 带有"当创建新X时"触发的instincts
- 遵循可重复序列的instincts

示例：
- `new-table-step1`："添加数据库表时，创建迁移"
- `new-table-step2`："添加数据库表时，更新schema"
- `new-table-step3`："添加数据库表时，重新生成类型"

→ 创建：**new-table** 命令

### → 技能（自动触发）
当instincts描述应该自动发生的行为时：
- 模式匹配触发
- 错误处理响应
- 代码风格强制

示例：
- `prefer-functional`："编写函数时，偏好函数式风格"
- `use-immutable`："修改状态时，使用不可变模式"
- `avoid-classes`："设计模块时，避免基于类的设计"

→ 创建：`functional-patterns` 技能

### → Agent（需要深度/隔离）
当instincts描述受益于隔离的复杂、多步骤过程时：
- 调试工作流
- 重构序列
- 研究任务

示例：
- `debug-step1`："调试时，先检查日志"
- `debug-step2`："调试时，隔离失败组件"
- `debug-step3`："调试时，创建最小复现"
- `debug-step4`："调试时，用测试验证修复"

→ 创建：**debugger** agent

## 执行操作

1. 从 `~/.claude/homunculus/instincts/` 读取所有instincts
2. 按以下方式分组instincts：
   - 领域相似性
   - 触发模式重叠
   - 动作序列关系
3. 对每组3+相关instincts：
   - 确定进化类型（command/skill/agent）
   - 生成适当的文件
   - 保存到 `~/.claude/homunculus/evolved/{commands,skills,agents}/`
4. 将进化结构链接回源instincts

## 输出格式

```
🧬 进化分析
==================

发现3个聚类准备进化：

## 聚类1：数据库迁移工作流
Instincts：new-table-migration, update-schema, regenerate-types
类型：Command
置信度：85%（基于12次观察）

将创建：/new-table 命令
文件：
  - ~/.claude/homunculus/evolved/commands/new-table.md

## 聚类2：函数式代码风格
Instincts：prefer-functional, use-immutable, avoid-classes, pure-functions
类型：Skill
置信度：78%（基于8次观察）

将创建：functional-patterns 技能
文件：
  - ~/.claude/homunculus/evolved/skills/functional-patterns.md

## 聚类3：调试过程
Instincts：debug-check-logs, debug-isolate, debug-reproduce, debug-verify
类型：Agent
置信度：72%（基于6次观察）

将创建：debugger agent
文件：
  - ~/.claude/homunculus/evolved/agents/debugger.md

---
运行 `/evolve --execute` 创建这些文件。
```

## 标志

- `--execute`：实际创建进化结构（默认为预览）
- `--dry-run`：预览而不创建
- `--domain <名称>`：只进化指定领域的instincts
- `--threshold <n>`：形成聚类所需的最小instincts数（默认：3）
- `--type <command|skill|agent>`：只创建指定类型
