---
description: 
alwaysApply: false
enabled: true
updatedAt: 2026-02-27T08:37:02.394Z
provider: 
---

# 开发工作流

> 本文件扩展了 [common/git-workflow.md](./git-workflow.md)，包含 git 操作之前的完整功能开发流程。功能实现工作流描述了开发管道：规划、TDD、代码审查，然后提交到 git。

## 功能实现工作流

1. **规划优先**
   - 使用 **planner** agent 创建实现计划
   - 识别依赖和风险
   - 分阶段拆解任务

2. **TDD 开发方法**
   - 使用 **tdd-guide** agent
   - 先写测试（RED）
   - 实现代码通过测试（GREEN）
   - 重构优化（IMPROVE）
   - 确保测试覆盖率 80% 以上

3. **代码审查**
   - 编写代码后立即使用 **code-reviewer** agent
   - 解决 CRITICAL 和 HIGH 级别问题
   - 尽可能修复 MEDIUM 级别问题

4. **提交和推送**
   - 编写详细的提交信息
   - 遵循约定式提交格式
   - 参见 [git-workflow.md](./git-workflow.md) 了解提交信息格式和 PR 流程