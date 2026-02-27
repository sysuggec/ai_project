---
description: 执行 git 提交并推送代码到远程仓库，遵循约定式提交规范
alwaysApply: false
enabled: true
---

# Git 提交推送命令

## 命令说明

当用户请求提交和推送代码时，执行标准化的 git 工作流程。

## 执行步骤

1. **检查状态** - 查看 git 状态和变更文件
2. **分析变更** - 确定合适的提交类型和描述
3. **生成消息** - 按照约定式提交格式生成提交消息
4. **执行提交** - 执行 git commit
5. **推送代码** - 推送到远程仓库

## 提交消息格式

```
<类型>: <描述>

<可选正文>
```

### 提交类型

| 类型 | 说明 |
|------|------|
| feat | 新功能 |
| fix | Bug修复 |
| refactor | 重构 |
| docs | 文档更新 |
| test | 测试相关 |
| chore | 构建/工具 |
| perf | 性能优化 |
| ci | CI配置 |

## 使用示例

```
用户: "提交代码"
用户: "push代码"
用户: "帮我提交这些变更"
```

## 规则引用

执行时引用规则文件：
- `.codebuddy/rules/common/git-workflow.md`

## 执行命令

```bash
# 检查状态
git status

# 添加变更（如需要）
git add .

# 提交
git commit -m "<类型>: <描述>"

# 推送（新分支使用 -u）
git push -u origin <branch-name>
```
