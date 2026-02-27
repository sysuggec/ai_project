# 开发工作流

> 本文件扩展了 [git-workflow.md](git-workflow.md)，描述git操作之前的完整功能开发流程。

功能实现工作流描述了开发流水线：规划、TDD、代码审查，然后提交到git。

## 功能实现工作流

1. **先规划**
   - 使用 **planner** agent 创建实现计划
   - 识别依赖和风险
   - 分解为多个阶段

2. **TDD 方法**
   - 使用 **tdd-guide** agent
   - 先写测试（RED）
   - 实现以通过测试（GREEN）
   - 重构（IMPROVE）
   - 验证80%以上覆盖率

3. **代码审查**
   - 编写代码后立即使用 **code-reviewer** agent
   - 解决 CRITICAL 和 HIGH 级别问题
   - 尽可能修复 MEDIUM 级别问题

4. **提交和推送**
   - 详细的提交消息
   - 遵循约定式提交格式
   - 提交消息格式和PR流程参见 [git-workflow.md](./git-workflow.md)
