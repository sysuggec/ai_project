---
description: 启动NanoClaw agent REPL — 一个持久的、会话感知的AI助手，由claude CLI驱动
---

# Claw命令

启动交互式AI agent会话，将会话历史持久化到磁盘，可选加载ECC技能上下文。

## 用法

```bash
node scripts/claw.js
```

或通过npm：

```bash
npm run claw
```

## 环境变量

| 变量 | 默认值 | 描述 |
|------|--------|------|
| `CLAW_SESSION` | `default` | 会话名称（字母数字+连字符） |
| `CLAW_SKILLS` | *(空)* | 逗号分隔的技能名称，作为系统上下文加载 |

## REPL命令

在REPL内，直接在提示符输入这些命令：

```
/clear      清除当前会话历史
/history    打印完整对话历史
/sessions   列出所有保存的会话
/help       显示可用命令
exit        退出REPL
```

## 工作原理

1. 读取 `CLAW_SESSION` 环境变量选择命名会话（默认：`default`）
2. 从 `~/.claude/claw/{session}.md` 加载对话历史
3. 可选从 `CLAW_SKILLS` 环境变量加载ECC技能上下文
4. 进入阻塞提示循环 — 每条用户消息发送到 `claude -p` 并携带完整历史
5. 响应追加到会话文件以实现跨重启持久化

## 会话存储

会话以Markdown文件存储在 `~/.claude/claw/`：

```
~/.claude/claw/default.md
~/.claude/claw/my-project.md
```

每轮格式：

```markdown
### [2025-01-15T10:30:00.000Z] User
这个函数做什么？
---
### [2025-01-15T10:30:05.000Z] Assistant
这个函数计算...
---
```

## 示例

```bash
# 启动默认会话
node scripts/claw.js

# 命名会话
CLAW_SESSION=my-project node scripts/claw.js

# 带技能上下文
CLAW_SKILLS=tdd-workflow,security-review node scripts/claw.js
```
