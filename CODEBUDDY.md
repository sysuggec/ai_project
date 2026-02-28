# CODEBUDDY.md This file provides guidance to CodeBuddy when working with code in this repository.

## 常用命令

### 数据库操作
```bash
# SQLite 初始化（开发环境默认）
touch database/riskctl.db
sqlite3 database/riskctl.db < database/20260211_create_risk_tables.sql

# 切换到 MySQL（修改 .env）
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=riskctl
```

### 接口测试
```bash
# 退款上报
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.refundReport" \
  -d "app=17sing" \
  -d "order_no=ORD001" \
  -d "refund_amount=99.00" \
  -d "refund_time=$(date +%s)" \
  -d "app_uid=12345678" \
  -d "phone=13800138000"

# 风控查询
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.riskQuery" \
  -d "phone=13800138000"

# 撤销退款
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.refundCancel" \
  -d "app=17sing" \
  -d "order_no=ORD001"
```

## 项目架构

### 技术栈
- **语言**: PHP 7.4
- **ORM**: Laravel Eloquent（独立使用，非完整 Laravel 框架）
- **数据库**: 生产 MySQL 5.7 / 开发 SQLite

### 分层架构
```
Controller 层 (action/RiskControl.php)
    ↓ 参数校验、响应格式化
Service 层 (services/RiskService.php)
    ↓ 账号关联、用户合并、风控判断
Model 层 (model/*.php)
    ↓ 数据实体、Eloquent ORM
```

### 统一入口路由
- 入口文件: `action/index.php`
- 路由格式: `?action=Risk.{方法名}`
- 支持方法: `refundReport`, `riskQuery`, `refundCancel`

### 核心数据模型
| 表名 | 用途 | 唯一键 |
|-----|------|-------|
| t_risk_user | 风险用户 | id |
| t_risk_identifier | 账号标识（手机号、Google ID 等） | type + value + app |
| t_risk_user_app | App 维度用户信息 | risk_user_id + app |
| t_refund_order | 退款订单 | app + order_no |

### 关键业务逻辑

**账号关联策略**: 多种标识（phone、google_id、payment_account、facebook_business_id）可关联到同一风险用户，实现跨 App 用户追踪。

**用户合并**: 当多个标识关联到不同风险用户时，选择 ID 最小的作为主用户，迁移所有关联数据。

**幂等性保证**: 退款订单以 `app + order_no` 为唯一键，重复上报返回相同结果。

### 开发规范
- 时间字段统一使用 `BIGINT` 秒级时间戳
- 金额字段使用 `DECIMAL(12,2)`
- 禁止 SELECT *，禁止直接写 SQL，使用 Eloquent ORM
- PHP 使用自动加载，避免 require/include
