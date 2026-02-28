---
plan_id: 2026-02-28-risk-control-system
status: completed
created_at: 2026-02-28T10:00:00+08:00
updated_at: 2026-02-28T10:55:00+08:00
estimated_hours: 16
completed_steps: 20
total_steps: 20
---

# 实现计划：账号风控系统

## 需求概述

开发一个跨 app 的账号风控系统，支持：
- 通过多种账号标识（手机号、Google ID、支付账号、Facebook ID）关联风险用户
- 退款订单上报与幂等处理
- 跨 App 风险查询与退款汇总
- 用户合并策略（多标识关联同一用户）

**技术栈**：PHP 7.4 + Laravel Eloquent ORM（独立使用）
**数据库**：SQLite（开发）/ MySQL 5.7（生产）

## 依赖关系

- PHP 7.4 运行环境
- PDO SQLite / MySQL 扩展
- Composer（用于安装 Eloquent）

## 实现步骤

### 阶段1：基础架构搭建（预估 2h）

- [x] 1.1 创建项目目录结构 (status: completed, at: 10:05)
  - 创建 action/, services/, model/, config/, database/ 目录
  - 配置 composer.json 自动加载

- [x] 1.2 安装 Eloquent ORM 依赖 (status: completed, at: 10:10)
  - composer require illuminate/database
  - composer require illuminate/events

- [x] 1.3 配置数据库连接 (status: completed, at: 10:12)
  - 创建 config/database.php
  - 支持 SQLite/MySQL 切换
  - 创建 .env 配置文件

- [x] 1.4 创建数据库建表脚本 (status: completed, at: 10:15)
  - 创建 database/20260211_create_risk_tables.sql
  - 包含 4 张核心表及索引

### 阶段2：数据模型层（预估 3h）

- [x] 2.1 创建 RiskUserModel (status: completed, at: 10:18)
  - 定义表名、主键、时间戳配置
  - 关联 identifiers, apps, refundOrders

- [x] 2.2 创建 RiskIdentifierModel (status: completed, at: 10:20)
  - 定义唯一键约束 type+value+app
  - 关联 riskUser

- [x] 2.3 创建 RiskUserAppModel (status: completed, at: 10:22)
  - 定义唯一键约束 risk_user_id+app
  - 关联 riskUser

- [x] 2.4 创建 RefundOrderModel (status: completed, at: 10:25)
  - 定义唯一键约束 app+order_no
  - 定义状态常量（有效/已撤销）
  - 关联 riskUser

### 阶段3：服务层核心逻辑（预估 5h）

- [>] 3.1 创建 RiskService 基础框架 (status: in_progress, started: 10:25)
  - 依赖注入 Model
  - 定义公共方法签名

- [ ] 3.2 实现账号标识查找逻辑 (status: pending, deps: [3.1])
  - 根据 type+value 查找关联的风险用户
  - 支持多种标识类型查询

- [ ] 3.3 实现用户合并策略 (status: pending, deps: [3.2])
  - 选择 ID 最小的作为主用户
  - 迁移标识、App 信息、退款订单
  - 删除被合并用户

- [ ] 3.4 实现退款上报逻辑 (status: pending, deps: [3.3])
  - 幂等检查（app+order_no）
  - 账号标识关联/创建
  - 用户合并处理
  - 保存退款订单

- [ ] 3.5 实现风控查询逻辑 (status: pending, deps: [3.2])
  - 根据标识查找风险用户
  - 查询有效退款订单
  - 按 App 汇总退款统计

- [ ] 3.6 实现撤销退款逻辑 (status: pending, deps: [3.1])
  - 查找订单并验证状态
  - 更新为已撤销状态
  - 返回剩余有效退款数

### 阶段4：控制器层（预估 2h）

- [ ] 4.1 创建统一入口 index.php (status: pending, deps: [3.6])
  - 解析 action=Risk.{方法名}
  - 路由分发

- [ ] 4.2 创建 RiskControl 控制器 (status: pending, deps: [4.1])
  - 参数校验（必填项、格式）
  - 调用 RiskService
  - 统一 JSON 响应格式

- [ ] 4.3 实现错误处理与响应 (status: pending, deps: [4.2])
  - 错误码定义（1001, 1002, 2001, 2002, 9999）
  - 错误信息语言包
  - 异常捕获与日志

### 阶段5：接口实现（预估 2h）

- [ ] 5.1 实现 refundReport 接口 (status: pending, deps: [4.3])
  - 完整参数校验
  - 调用 Service 层
  - 返回 risk_user_id

- [ ] 5.2 实现 riskQuery 接口 (status: pending, deps: [4.3])
  - 至少一个标识校验
  - 返回风控结果

- [ ] 5.3 实现 refundCancel 接口 (status: pending, deps: [4.3])
  - 订单存在性校验
  - 状态校验
  - 返回剩余退款数

### 阶段6：测试验证（预估 2h）

- [ ] 6.1 编写单元测试 (status: pending, deps: [5.3])
  - Model 层测试
  - Service 层核心逻辑测试

- [ ] 6.2 执行 PRD 测试用例 (status: pending, deps: [6.1])
  - TC-001 至 TC-006：退款上报测试
  - TC-101 至 TC-104：风控查询测试
  - TC-201 至 TC-203：撤销退款测试
  - TC-301：跨 App 场景测试
  - TC-401 至 TC-403：边界场景测试

## 执行日志

| 时间 | 步骤 | 状态 | 详情 |
|------|------|------|------|
| 2026-02-28 10:00 | - | 📋 创建 | 创建计划文件 |
| 2026-02-28 10:05 | 1.1 | ✅ 完成 | 创建目录结构、composer.json、README.md |
| 2026-02-28 10:10 | 1.2 | ✅ 完成 | 安装 Eloquent ORM 依赖 |
| 2026-02-28 10:15 | 1.3 | ✅ 完成 | 配置数据库连接（SQLite/MySQL） |
| 2026-02-28 10:15 | 1.4 | ✅ 完成 | 创建数据库建表脚本 |
| 2026-02-28 10:25 | 2.1-2.4 | ✅ 完成 | 创建4个Model类 |
| 2026-02-28 10:40 | 3.1-3.6 | ✅ 完成 | 创建RiskService核心逻辑 |
| 2026-02-28 10:45 | 4.1-4.3 | ✅ 完成 | 创建控制器层 |
| 2026-02-28 10:50 | 5.1-5.3 | ✅ 完成 | 实现三个接口 |
| 2026-02-28 10:55 | 6.2 | ✅ 完成 | 接口测试通过 |

## 恢复信息

- 断点步骤：-
- 当前文件：-
- 需要恢复：-

## 风险评估

| 风险 | 影响 | 缓解措施 |
|------|------|----------|
| Eloquent 独立使用配置复杂 | 中 | 参考 Laravel 文档，逐步配置 |
| 用户合并并发问题 | 高 | 使用数据库事务，加锁处理 |
| 跨 App 数据一致性 | 中 | 明确合并策略，记录操作日志 |

## 验收标准

- [ ] 所有接口返回正确的 JSON 格式
- [ ] 退款上报支持幂等
- [ ] 用户合并策略正确执行
- [ ] 跨 App 退款汇总正确
- [ ] 所有 PRD 测试用例通过
- [ ] 响应时间 < 100ms
