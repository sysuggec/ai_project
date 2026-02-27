---
description: 
alwaysApply: false
enabled: true
updatedAt: 2026-02-27T08:19:32.559Z
provider: 
---

# Git 工作流

## 提交信息格式

```
<类型>: <描述>
```

类型: feat, fix, refactor, docs, test, chore, perf, ci

## Pull Request 工作流程

创建 PR 时：
1. 分析完整的提交历史（不仅仅是最新提交）
2. 使用 `git diff [base-branch]...HEAD` 查看所有变更
3. 编写全面的 PR 摘要
4. 包含测试计划和待办事项
5. 新分支使用 `-u` 标志推送