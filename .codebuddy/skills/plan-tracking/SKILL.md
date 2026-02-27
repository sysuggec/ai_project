---
name: plan-tracking
description: 计划追踪技能，定义计划创建、进度更新、中断恢复的完整工作流程。被 plan-tracker agent 和 orchestrate 命令使用。
---

# 计划追踪技能

此技能定义计划追踪的完整工作流程，包括计划创建、步骤执行、依赖处理和中断恢复。

## 何时激活

- 创建新的实现计划
- 更新步骤执行进度
- 处理步骤失败和重试
- 从中断点恢复执行
- 查询计划状态和进度

---

## 核心工作流程

### 1. 创建计划流程

```
输入: title, requirements, steps
输出: plan_id

1. 生成 plan_id
   - 格式: YYYY-MM-DD-feature-name
   - 使用当前日期和功能名称
   
2. 创建需求文档
   - 路径: doc/requirements/{plan_id}.md
   - 内容: 需求描述、验收标准
   
3. 创建计划文件
   - 路径: doc/plans/active/{plan_id}.md
   - 初始化 front matter
   - 添加所有步骤（状态为 pending）
   
4. 初始化执行日志
   - 添加创建记录
```

### 2. 步骤执行流程

```
输入: plan_id
输出: step_info, execution_result

1. 获取下一步骤
   get_next_step(plan_id):
     a. 读取计划文件
     b. 找到第一个 pending 状态的步骤
     c. 检查依赖是否满足
     d. 返回步骤信息或 null

2. 开始执行步骤
   start_step(plan_id, step_id):
     a. 验证步骤状态为 pending
     b. 更新状态为 in_progress
     c. 记录 started_at 时间
     d. 添加执行日志 "开始"
     e. 更新 updated_at

3. 执行步骤内容
   - 由调用方（如 tdd-guide）执行具体任务
   
4. 完成或失败
   complete_step(plan_id, step_id, summary):
     a. 更新状态为 completed
     b. 记录 completed_at 时间
     c. 更新 completed_steps 计数
     d. 解锁依赖此步骤的其他步骤
     e. 添加执行日志 "完成"
     
   fail_step(plan_id, step_id, error):
     a. 更新状态为 failed
     b. 记录错误信息
     c. 标记依赖此步骤的步骤为 blocked
     d. 添加执行日志 "失败"
     e. 如果不可恢复，更新计划状态为 failed
```

### 3. 依赖处理逻辑

```
依赖检查 (执行步骤前):
  FOR EACH dependency IN step.dependencies:
    IF dependency.status != completed THEN
      IF dependency.status == failed THEN
        current_step.status = blocked
        current_step.blocked_by = dependency.id
        RETURN blocked
      ELSE
        RETURN waiting

依赖传播 (步骤失败时):
  当步骤 A 状态变为 failed:
    FOR EACH 步骤 B WHERE A IN B.dependencies:
      IF B.status == pending THEN
        B.status = blocked
        B.blocked_by = A.id

依赖解锁 (步骤完成时):
  当步骤 A 状态变为 completed:
    FOR EACH 步骤 B WHERE A IN B.dependencies:
      IF B.status == blocked THEN
        all_deps_completed = ALL(dep.status == completed FOR dep IN B.dependencies)
        IF all_deps_completed THEN
          B.status = pending
          B.blocked_by = null
```

### 4. 中断恢复流程

```
输入: plan_id (可选)
输出: resume_info

1. 查找中断的计划
   IF plan_id 未提供:
     扫描 doc/plans/active/
     找到 status == in_progress 的计划
     
2. 识别断点
   断点优先级:
   a. in_progress 状态的步骤 → 从该步骤重新开始
   b. 最后一个 completed 步骤 → 从下一个步骤继续
   c. 无完成步骤 → 从第一个步骤开始
   
3. 恢复检查
   - 验证依赖环境是否可用
   - 验证已修改文件是否存在
   - 验证工具配置是否有效
   
4. 生成恢复报告
   - 断点位置和状态
   - 已用时间
   - 恢复建议
```

### 5. 重试策略

```
失败重试:
  IF error.type IN [NET, RES] THEN
    max_retries = 3
    retry_interval = [1s, 2s, 4s]  # 指数退避
    
    FOR i IN 0..max_retries:
      等待 retry_interval[i]
      记录重试日志
      重新执行步骤
      
      IF 成功 THEN
        complete_step()
        RETURN success
        
    # 重试耗尽
    fail_step(error, recoverable: false)
    
  ELSE IF error.type == DEP THEN
    询问用户: 修复依赖 / 跳过步骤 / 中止计划
    
  ELSE:
    fail_step(error, recoverable: false)
    询问用户下一步操作
```

---

## 方法接口

### plan-tracker 提供的方法

| 方法 | 输入 | 输出 | 说明 |
|------|------|------|------|
| `create_plan` | title, requirements, steps | plan_id | 创建新计划 |
| `get_next_step` | plan_id | step_info \| null | 获取下一个待执行步骤 |
| `start_step` | plan_id, step_id | - | 标记步骤开始 |
| `complete_step` | plan_id, step_id, summary | - | 标记步骤完成 |
| `fail_step` | plan_id, step_id, error | - | 标记步骤失败 |
| `skip_step` | plan_id, step_id, reason | - | 跳过步骤 |
| `get_progress` | plan_id | progress_info | 获取整体进度 |
| `resume_plan` | plan_id? | resume_info | 恢复中断的计划 |
| `complete_plan` | plan_id, summary | - | 归档计划 |
| `list_plans` | status? | plan_list | 列出所有计划 |

---

## 模板

### 计划文件模板

```markdown
---
plan_id: {plan_id}
status: in_progress
created_at: {timestamp}
updated_at: {timestamp}
estimated_hours: {hours}
completed_steps: 0
total_steps: {count}
---

# 实现计划：{title}

## 需求概述
{requirements}

## 依赖关系
{dependencies}

## 实现步骤

{# FOR EACH stage #}
### 阶段{N}：{stage_name}
{# FOR EACH step #}
- [ ] {step_id} {step_description} (status: pending{# IF deps #}, deps: [{deps}]{# ENDIF #})
{# ENDFOR #}

{# ENDFOR #}

## 执行日志
| 时间 | 步骤 | 状态 | 详情 |
|------|------|------|------|
| {time} | - | 📋 创建 | 创建计划文件 |

## 恢复信息
- 断点步骤：-
- 当前文件：-
- 需要恢复：-
```

### 进度报告模板

```markdown
📊 进度报告：{plan_id}
────────────────
状态：{status}
进度：{completed}/{total} ({percentage}%)
当前阶段：{current_stage}
已用时间：{elapsed_hours} 小时
预计剩余：{remaining_hours} 小时

步骤统计：
✅ 完成: {completed}
🔄 进行中: {in_progress}
⚪ 待执行: {pending}
❌ 失败: {failed}
🟡 阻塞: {blocked}
⏭️ 跳过: {skipped}

{# IF has_blocking_issues #}
阻塞问题：
{# FOR EACH issue #}
- 步骤 {step_id}: {issue}
{# ENDFOR #}
{# ENDIF #}
```

### 恢复报告模板

```markdown
📋 恢复计划：{plan_id}
────────────────
断点位置：步骤 {step_id}
断点状态：{step_status}
已用时间：{elapsed_minutes} 分钟

上次执行：
{# FOR EACH log IN recent_logs #}
| {log.time} | {log.step} | {log.status} | {log.detail} |
{# ENDFOR #}

恢复检查：
✅ 依赖环境: {env_status}
✅ 文件状态: {files_status}
✅ 工具配置: {tools_status}

恢复建议：
{suggestion}
```

---

## 与 orchestrate 的协作

```
orchestrate 执行流程:

1. planner 生成计划
   ↓
2. plan-tracker.create_plan() 保存计划
   ↓
3. LOOP:
   a. plan-tracker.get_next_step()
   b. plan-tracker.start_step()
   c. 执行步骤（调用 tdd-guide 等）
   d. plan-tracker.complete_step() / fail_step()
   ↓
4. plan-tracker.complete_plan() 归档
```

---

## 注意事项

1. **原子写入**：更新计划文件时使用完整替换
2. **状态锁定**：变更前检查当前状态，避免冲突
3. **完整日志**：每次操作都记录到执行日志
4. **错误详情**：失败时记录足够信息便于排查
5. **依赖检查**：状态变更时同步更新依赖关系
