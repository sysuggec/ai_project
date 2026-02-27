---
description: 显示所有学习的instincts及其置信度级别
---

# Instinct状态命令

显示所有学习的instincts及其置信度分数，按领域分组。

## 用法

```
/instinct-status
/instinct-status --domain code-style
/instinct-status --low-confidence
```

## 执行操作

1. 从 `~/.claude/homunculus/instincts/personal/` 读取所有instinct文件
2. 从 `~/.claude/homunculus/instincts/inherited/` 读取继承的instincts
3. 按领域分组显示，带置信度条

## 输出格式

```
📊 Instinct状态
==================

## 代码风格（4个instincts）

### prefer-functional-style
触发：编写新函数时
动作：使用函数式模式而非类
置信度：████████░░ 80%
来源：session-observation | 最后更新：2025-01-22

### use-path-aliases
触发：导入模块时
动作：使用@/路径别名而非相对导入
置信度：██████░░░░ 60%
来源：repo-analysis (github.com/acme/webapp)

## 测试（2个instincts）

### test-first-workflow
触发：添加新功能时
动作：先写测试，再实现
置信度：█████████░ 90%
来源：session-observation

## 工作流（3个instincts）

### grep-before-edit
触发：修改代码时
动作：用Grep搜索，用Read确认，然后Edit
置信度：███████░░░ 70%
来源：session-observation

---
总计：9个instincts（4个个人，5个继承）
观察者：运行中（最后分析：5分钟前）
```

## 标志

- `--domain <名称>`：按领域过滤（code-style、testing、git等）
- `--low-confidence`：只显示置信度<0.5的instincts
- `--high-confidence`：只显示置信度>=0.7的instincts
- `--source <类型>`：按来源过滤（session-observation、repo-analysis、inherited）
- `--json`：输出JSON格式供程序使用
