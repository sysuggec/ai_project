---
name: plan-tracking
description: 计划追踪规范，定义文件格式、状态管理和追踪标准。
alwaysApply: true
---

# 计划追踪规范

本规则定义实现计划的持久化存储和进度追踪的标准规范。

## 📁 文件结构

```
doc/
├── plans/
│   ├── active/              # 活跃计划（进行中或暂停）
│   │   └── YYYY-MM-DD-feature-name.md
│   └── completed/           # 已完成计划
│       └── YYYY-MM-DD-feature-name.md
└── requirements/            # 需求文档
    └── YYYY-MM-DD-feature-name.md
```

## 📋 计划文件格式

```yaml
---
plan_id: YYYY-MM-DD-feature-name
status: in_progress | completed | failed | paused
created_at: ISO8601
updated_at: ISO8601
estimated_hours: X
completed_steps: N
total_steps: M
---
```

```markdown
# 实现计划：[功能名称]

## 需求概述
[需求描述]

## 依赖关系
[外部依赖、前置条件]

## 实现步骤

### 阶段1：[阶段名称]
- [x] 1.1 步骤描述 (status: completed, at: HH:MM)
- [>] 1.2 步骤描述 (status: in_progress, started: HH:MM)
- [ ] 1.3 步骤描述 (status: pending)
- [!] 1.4 步骤描述 (status: failed, error: 错误信息)
- [-] 1.5 步骤描述 (status: blocked, blocked_by: 1.4)
- [~] 1.6 步骤描述 (status: skipped, reason: 原因)

## 执行日志
| 时间 | 步骤 | 状态 | 详情 |
|------|------|------|------|

## 恢复信息
- 断点步骤：X.X
- 当前文件：path/to/file
- 需要恢复：描述
```

## 🔄 状态定义

| 状态 | 符号 | 说明 | 可转换到 |
|------|------|------|----------|
| `pending` | `[ ]` | 待执行 | `in_progress`, `skipped` |
| `in_progress` | `[>]` | 进行中 | `completed`, `failed`, `pending` |
| `completed` | `[x]` | 已完成 | 终态 |
| `failed` | `[!]` | 失败 | `in_progress`, `skipped` |
| `blocked` | `[-]` | 被阻塞 | `pending`, `skipped` |
| `skipped` | `[~]` | 已跳过 | 终态 |

## 🚨 错误分类

| 类型 | 代码 | 可恢复 | 示例 |
|------|------|--------|------|
| 网络错误 | `NET` | ✅ | 连接超时、DNS解析失败 |
| 资源错误 | `RES` | ✅ | 文件不存在、权限不足 |
| 依赖错误 | `DEP` | ⚠️ | 包不存在、版本冲突 |
| 逻辑错误 | `LOG` | ❌ | 算法错误、业务逻辑问题 |
| 设计错误 | `DSN` | ❌ | 架构问题、需求理解错误 |
| 未知错误 | `UNK` | ❓ | 其他未分类错误 |

## ⏱️ 时间记录规则

| 操作 | 记录时间 |
|------|----------|
| 创建计划 | `created_at`, `updated_at` |
| 开始步骤 | 记录 `started_at` |
| 完成步骤 | 记录 `completed_at`，更新 `updated_at` |
| 任何状态变更 | 更新 `updated_at` |

## 🎯 质量检查清单

- [ ] 所有步骤都有唯一 ID
- [ ] 依赖关系已正确声明
- [ ] 每次状态变更都更新了 `updated_at`
- [ ] 执行日志完整记录了所有操作
- [ ] 失败步骤有清晰的错误信息
- [ ] 恢复信息准确反映了断点状态
