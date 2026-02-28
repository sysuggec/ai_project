# 账号风控系统 PRD（产品需求文档）

## 1. 产品概述

### 1.1 产品定位
账号风控系统是一个跨 app 的风险控制服务，用于汇聚各应用的退款与账号信息，实现账号关联与风险判断，帮助业务系统在用户注册、登录、储值前识别风险用户。

### 1.2 核心价值
- 统一多 app 风控数据，打破应用间信息孤岛
- 通过多种账号标识类型（手机号、设备 ID、支付账号等）实现跨应用用户关联
- 提供实时风控查询接口，降低业务系统退款损失

### 1.3 目标用户
业务系统开发者、运营风控人员

## 2. 功能需求

### 2.1 核心功能

#### 2.1.1 账号关联
- 支持多种账号标识类型关联：
  - 手机号（phone）
  - 支付账号（payment_account）
  - Google 账号 ID（google_id）
  - Facebook Business ID（facebook_business_id）
  - 其他可扩展类型
- 记录 app 维度的用户信息：
  - uid
  - 昵称
  - 注册时间（秒级时间戳）
  - 注册 IP
  - 第三方昵称（Google 昵称、Facebook 昵称）
  - 关联时间（秒级时间戳）
- 账号合并策略：当多个标识关联到不同风险用户时，按策略合并并更新标识归属

#### 2.1.2 风控判断
- 支持通过任意一种或多种账号类型查询风险状态
- 风险判定标准：是否存在有效退款订单
- 查询维度：支持单一标识或多个标识组合查询
- 返回跨 app 退款详情：若为风险用户，返回其在各 app 的退款情况汇总

#### 2.1.3 HTTP 接口

| 接口名称 | 功能描述 |
|---------|---------|
| 退款上报 | 上报用户退款订单，自动关联账号信息 |
| 风控查询 | 根据账号标识查询用户风险状态，返回跨 app 退款情况 |
| 撤销退款 | 按订单号撤销退款记录 |

### 2.2 接口规范

#### 2.2.1 统一入口
- 路径：`/action/index.php`
- 请求方式：POST/GET
- 参数：`action=Risk.{方法名}`

#### 2.2.2 接口详细定义

**1. 退款上报接口**

- 方法名：`refundReport`
- 调用时机：用户发生退款时调用
- 输入参数：

| 参数名 | 类型 | 必填 | 说明 |
|-------|------|-----|------|
| app | string | 是 | 应用标识（如 17sing、wekara 等） |
| order_no | string | 是 | 订单号 |
| refund_amount | decimal | 是 | 退款金额 |
| refund_time | int | 是 | 退款时间（秒级时间戳） |
| payment_channel | string | 否 | 支付渠道（如 google_pay、apple_pay、paypal、stripe 等） |
| app_uid | string | 是 | app 内用户 uid |
| nickname | string | 否 | 用户在 app 内的昵称 |
| register_time | int | 否 | 注册时间（秒级时间戳） |
| register_ip | string | 否 | 注册 IP |
| phone | string | 否 | 手机号 |
| payment_account | string | 否 | 支付账号 |
| google_id | string | 否 | Google 账号 ID |
| google_nickname | string | 否 | Google 账号昵称 |
| facebook_business_id | string | 否 | Facebook Business ID |
| facebook_nickname | string | 否 | Facebook 账号昵称 |

- 请求示例：
```json
{
  "app": "17sing",
  "order_no": "ORD20260224001",
  "refund_amount": 99.00,
  "refund_time": 1708752000,
  "payment_channel": "google_pay",
  "app_uid": "12345678",
  "nickname": "歌唱达人",
  "register_time": 1700000000,
  "register_ip": "192.168.1.100",
  "phone": "13800138000",
  "payment_account": "paypal_user@example.com",
  "google_id": "google_12345",
  "google_nickname": "Google User",
  "facebook_business_id": "fb_67890",
  "facebook_nickname": "FB User"
}
```

- 输出：
```json
{
  "code": 0,
  "msg": "success",
  "data": {
    "risk_user_id": 1001
  }
}
```

- 业务逻辑：
  1. 根据上报的账号标识（phone、payment_account、google_id、facebook_business_id）查找或创建风险用户
  2. 若多个标识关联到不同风险用户，执行合并策略
  3. 记录 app 维度的用户信息（uid、昵称、注册信息、第三方昵称）
  4. 保存退款订单信息（包含退款金额、退款时间、支付渠道）
  5. 支持幂等上报（app + order_no 唯一）

**2. 风控查询接口**

- 方法名：`riskQuery`
- 输入参数：

| 参数名 | 类型 | 必填 | 说明 |
|-------|------|-----|------|
| phone | string | 否 | 手机号 |
| payment_account | string | 否 | 支付账号 |
| google_id | string | 否 | Google 账号 ID |
| facebook_business_id | string | 否 | Facebook Business ID |

> 注：至少提供一个账号标识

- 请求示例：
```json
{
  "phone": "13800138000",
  "google_id": "google_12345"
}
```

- 输出：
```json
{
  "code": 0,
  "msg": "success",
  "data": {
    "is_risk": true,
    "risk_user_id": 1001,
    "total_refund_count": 3,
    "total_refund_amount": "179.00",
    "refund_summary": [
      {
        "app": "17sing",
        "refund_count": 2,
        "refund_amount": "129.00",
        "app_uid": "12345678",
        "nickname": "歌唱达人"
      },
      {
        "app": "wekara",
        "refund_count": 1,
        "refund_amount": "50.00",
        "app_uid": "87654321",
        "nickname": "卡拉达人"
      }
    ]
  }
}
```

- 业务逻辑：
  1. 根据提供的账号标识查找关联的风险用户
  2. 查询该风险用户的所有有效退款订单
  3. 按 app 汇总退款情况（订单数、金额总计）
  4. 若非风险用户，is_risk 返回 false，refund_summary 为空数组

**3. 撤销退款接口**

- 方法名：`refundCancel`
- 输入参数：

| 参数名 | 类型 | 必填 | 说明 |
|-------|------|-----|------|
| app | string | 是 | 应用标识 |
| order_no | string | 是 | 订单号 |

- 请求示例：
```json
{
  "app": "17sing",
  "order_no": "ORD20260224001"
}
```

- 输出：
```json
{
  "code": 0,
  "msg": "success",
  "data": {
    "remaining_refund_count": 2
  }
}
```

- 业务逻辑：
  1. 根据 app + order_no 查找退款订单
  2. 更新订单状态为已撤销
  3. 返回剩余有效退款订单数（用于判断用户是否仍为风险用户）
  4. 若订单不存在，返回可追踪的错误码

## 3. 数据模型设计

### 3.1 核心数据表

| 表名 | 说明 | 主要字段 |
|-----|------|---------|
| t_risk_user | 风险用户表 | id, created_at, updated_at |
| t_risk_identifier | 账号标识表 | id, risk_user_id, app, type, value, created_at |
| t_risk_user_app | app 维度用户信息表 | id, risk_user_id, app, uid, nickname, register_time, register_ip, google_nickname, facebook_nickname, linked_at, created_at, updated_at |
| t_refund_order | 退款订单表 | id, risk_user_id, app, order_no, refund_amount, payment_channel, status, refunded_at, canceled_at, created_at, updated_at |

### 3.2 数据字典

**账号标识类型（type）**
| 值 | 说明 |
|---|------|
| phone | 手机号 |
| payment_account | 支付账号 |
| google_id | Google 账号 ID |
| facebook_business_id | Facebook Business ID |

**支付渠道（payment_channel）**
| 值 | 说明 |
|---|------|
| google_pay | Google Pay |
| apple_pay | Apple Pay |
| paypal | PayPal |
| stripe | Stripe |
| other | 其他 |

**退款订单状态（status）**
| 值 | 说明 |
|---|------|
| 1 | 有效 |
| 2 | 已撤销 |

## 4. 技术架构

### 4.1 技术选型
- 语言/运行时：PHP 7.4
- 数据库：
  - 生产环境：MySQL 5.7（utf8mb4）
  - 开发环境：SQLite（方便本地测试）
- ORM：Laravel ORM（Eloquent）

### 4.2 架构分层
- **Controller 层**：参数校验、调用服务、统一响应格式
- **Service 层**：账号关联、退款上报/撤销、风险查询核心逻辑
- **Model 层**：数据实体定义与数据库交互

### 4.3 设计原则
- 接口幂等性：退款订单以"app+订单号"唯一
- 可扩展性：账号类型通过 type 字段扩展，无需修改表结构
- 性能优化：核心查询使用索引，避免循环内多次 DB 操作
- 数据一致性：时间字段统一使用 bigint 秒级时间戳

## 5. 非功能性需求

### 5.1 性能要求
- 核心查询响应时间 < 100ms
- 支持高并发查询场景

### 5.2 可靠性要求
- 接口失败可追踪（错误码 + 日志）
- 数据库事务保证数据一致性

### 5.3 安全性要求
- 敏感信息记录（注册 IP）
- 防止 SQL 注入（使用 ORM）

### 5.4 可维护性要求
- 统一的 JSON 响应格式
- 错误信息从语言包获取
- 日志区分级别（info、warning、error）

## 6. 接口响应规范

### 6.1 成功响应
```json
{
  "code": 0,
  "msg": "success",
  "data": {}
}
```

### 6.2 失败响应
```json
{
  "code": 错误码,
  "msg": "错误信息（从语言包获取）",
  "data": null
}
```

### 6.3 错误码定义

| 错误码 | 说明 |
|-------|------|
| 0 | 成功 |
| 1001 | 参数缺失 |
| 1002 | 参数格式错误 |
| 2001 | 订单不存在 |
| 2002 | 订单已撤销 |
| 9999 | 系统错误 |

## 7. 业务流程

### 7.1 退款上报流程
1. 业务系统调用退款上报接口
2. 控制层参数校验
3. 服务层：
   - 根据 app + order_no 检查是否已存在（幂等处理）
   - 遍历账号标识（phone、payment_account、google_id、facebook_business_id），按 type + value 查找关联的风险用户
   - 若多个标识关联到不同风险用户，执行合并策略
   - 若无关联风险用户，创建新的风险用户
   - 保存账号标识记录（包含 app 字段，记录标识来源）
   - 保存/更新 app 维度用户信息
   - 创建退款订单记录
4. 返回上报结果

### 7.2 风控查询流程
1. 业务系统调用风控查询接口
2. 控制层参数校验（至少提供一个账号标识）
3. 服务层：
   - 根据提供的账号标识查找关联的风险用户
   - 若未找到风险用户，返回非风险用户
   - 若找到风险用户，查询其所有有效退款订单
   - 汇总退款情况，构建跨 app 退款详情列表
4. 返回查询结果

### 7.3 撤销退款流程
1. 业务系统调用撤销退款接口
2. 控制层参数校验
3. 服务层：
   - 根据 app + order_no 查找退款订单
   - 检查订单状态，若已撤销则返回错误
   - 更新订单状态为已撤销，记录撤销时间
   - 查询该风险用户剩余有效退款订单数
4. 返回撤销结果

## 8. 注意事项

### 8.1 退款上报时的重复标识处理

调用 refundReport 时，可能存在以下重复场景：

1. **同一订单重复上报**
   - 场景：业务系统重试或重复调用
   - 处理：以 app + order_no 为唯一键，若已存在则跳过或更新，保证幂等性

2. **同一用户不同订单上报**
   - 场景：同一用户在多次退款中上报相同的账号标识
   - 处理：
     - 账号标识表（t_risk_identifier）以 type + value + app 为唯一键
     - 同一标识可在多个 app 中记录，便于追踪第三方账号在各 app 的使用情况
     - 若标识已存在且关联到同一风险用户，跳过
     - 若同一标识在不同 app 中关联到不同风险用户，执行用户合并策略

3. **同一用户同一 app 不同信息上报**
   - 场景：同一用户在不同订单中上报的 app_uid、nickname 等信息可能不一致
   - 处理：
     - app 维度用户信息表（t_risk_user_app）以 risk_user_id + app 为唯一键
     - 每次上报更新最新信息（uid、昵称、注册信息、第三方昵称等）

4. **账号标识值为空或无效**
   - 场景：用户未绑定某些第三方账号，对应参数为空
   - 处理：仅处理非空且有效的账号标识，空值跳过不存储

### 8.2 用户合并策略

当多个账号标识关联到不同风险用户时，按以下策略合并：

1. 选择风险用户 ID 最小的作为主用户（或选择退款订单最多的作为主用户）
2. 将其他风险用户的账号标识、app 用户信息、退款订单迁移到主用户
3. 删除被合并的风险用户记录
4. 记录合并日志用于追溯

### 8.3 跨 App 退款详情返回

风控查询返回的退款汇总需包含：

- 总退款订单数（total_refund_count）
- 总退款金额（total_refund_amount）
- 按 app 分组汇总：
  - app：应用标识
  - refund_count：该 app 的退款订单数
  - refund_amount：该 app 的退款金额总计
  - app_uid：该 app 内的用户 uid
  - nickname：用户昵称
- 按退款金额降序排列

## 9. 风险与限制

### 9.1 已知风险
- 账号合并策略需明确，避免误合并
- 撤销退款后，若存在其他未撤销退款，用户仍为风险状态
- 跨 app 数据可能存在信息不一致的情况

### 9.2 限制条件
- 控制层不得直接写 SQL，所有 DB 访问通过 Laravel的orm
- 禁止 SELECT * 查询
- 时间字段统一使用 bigint 秒级时间戳
- php尽量使用自动加载，避免使用 require 和 include 引入文件

## 10. 部署说明

### 10.1 环境配置

**数据库配置**
- 开发环境默认使用 SQLite，无需安装 MySQL，方便快速部署测试
- 通过环境变量 `DB_CONNECTION` 快速切换数据库类型
- 配置示例：
```php
// config/database.php
'default' => env('DB_CONNECTION', 'sqlite'),

'connections' => [
    'sqlite' => [
        'driver' => 'sqlite',
        'database' => env('DB_DATABASE', __DIR__ . '/../database/riskctl.db'),
        'prefix' => '',
    ],
    'mysql' => [
        'driver' => 'mysql',
        'host' => env('DB_HOST', '127.0.0.1'),
        'port' => env('DB_PORT', '3306'),
        'database' => env('DB_DATABASE', 'riskctl'),
        'username' => env('DB_USERNAME', 'root'),
        'password' => env('DB_PASSWORD', ''),
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
    ],
],
```

**切换数据库**

通过修改 `.env` 文件切换数据库类型：

```bash
# .env 文件内容

# 使用 SQLite（默认）
DB_CONNECTION=sqlite

# 切换到 MySQL（修改为以下配置）
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=riskctl
# DB_USERNAME=root
# DB_PASSWORD=
```

### 10.2 CloudStudio 环境说明

当前项目已在 CloudStudio 环境中部署，相关说明：

**环境要求**
- PHP 7.4 运行环境
- 支持 SQLite 扩展（开发环境默认）
- 支持 PDO MySQL 扩展（生产环境切换时需要）
- 使用 Laravel的orm 框架访问 db

**初始化步骤**
1. 首次运行前，检查 `.env` 文件中的数据库配置
2. SQLite：首次运行自动创建数据库文件 `database/riskctl.db`
3. MySQL：执行建表脚本 `database/20260211_create_risk_tables.sql`

**快速测试**
```bash
# 测试风控查询接口
curl -X POST "http://your-domain/action/index.php" \
  -d "action=Risk.riskQuery" \
  -d "phone=13800138000"
```

## 11. 测试用例

### 11.1 退款上报接口测试用例

#### TC-001: 正常上报退款（完整参数）

**测试目的**：验证完整参数的退款上报功能

**前置条件**：数据库已初始化

**测试步骤**：
```bash
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.refundReport" \
  -d "app=17sing" \
  -d "order_no=ORD20260224001" \
  -d "refund_amount=99.00" \
  -d "refund_time=1708752000" \
  -d "payment_channel=google_pay" \
  -d "app_uid=12345678" \
  -d "nickname=歌唱达人" \
  -d "register_time=1700000000" \
  -d "register_ip=192.168.1.100" \
  -d "phone=13800138000" \
  -d "payment_account=paypal_user@example.com" \
  -d "google_id=google_12345" \
  -d "google_nickname=Google User" \
  -d "facebook_business_id=fb_67890" \
  -d "facebook_nickname=FB User"
```

**预期结果**：
```json
{
  "code": 0,
  "msg": "success",
  "data": {
    "risk_user_id": 1
  }
}
```

**验证点**：
- 返回 code=0
- 返回 risk_user_id
- 数据库 t_risk_user 新增 1 条记录
- 数据库 t_risk_identifier 新增 4 条记录（phone、payment_account、google_id、facebook_business_id）
- 数据库 t_risk_user_app 新增 1 条记录
- 数据库 t_refund_order 新增 1 条记录（payment_channel=google_pay）

---

#### TC-002: 上报退款（仅必填参数）

**测试目的**：验证仅有必填参数时的退款上报功能

**测试步骤**：
```bash
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.refundReport" \
  -d "app=wekara" \
  -d "order_no=ORD20260224002" \
  -d "refund_amount=50.00" \
  -d "refund_time=1708752000" \
  -d "payment_channel=apple_pay" \
  -d "app_uid=87654321" \
  -d "phone=13900139000"
```

**预期结果**：
```json
{
  "code": 0,
  "msg": "success",
  "data": {
    "risk_user_id": 2
  }
}
```

**验证点**：
- 返回 code=0
- t_risk_identifier 仅新增 1 条记录（phone）

---

#### TC-003: 重复上报同一订单（幂等性测试）

**测试目的**：验证同一订单重复上报的幂等性

**前置条件**：已执行 TC-001

**测试步骤**：
```bash
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.refundReport" \
  -d "app=17sing" \
  -d "order_no=ORD20260224001" \
  -d "refund_amount=99.00" \
  -d "refund_time=1708752000" \
  -d "payment_channel=google_pay" \
  -d "app_uid=12345678" \
  -d "phone=13800138000"
```

**预期结果**：
```json
{
  "code": 0,
  "msg": "success",
  "data": {
    "risk_user_id": 1
  }
}
```

**验证点**：
- 返回相同的 risk_user_id
- 数据库记录数不变

---

#### TC-004: 同一用户不同订单上报（账号关联）

**测试目的**：验证同一用户不同订单的账号关联功能

**前置条件**：已执行 TC-001

**测试步骤**：
```bash
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.refundReport" \
  -d "app=17sing" \
  -d "order_no=ORD20260224002" \
  -d "refund_amount=30.00" \
  -d "refund_time=1709000000" \
  -d "payment_channel=paypal" \
  -d "app_uid=12345678" \
  -d "phone=13800138000"
```

**预期结果**：
```json
{
  "code": 0,
  "msg": "success",
  "data": {
    "risk_user_id": 1
  }
}
```

**验证点**：
- 返回相同的 risk_user_id
- t_refund_order 新增 1 条记录
- t_risk_identifier 不新增（已存在）

---

#### TC-005: 不同账号标识关联同一用户（用户合并）

**测试目的**：验证多个标识关联到不同风险用户时的合并策略

**前置条件**：
- 已执行 TC-001（phone=13800138000，risk_user_id=1）
- 已执行 TC-002（phone=13900139000，risk_user_id=2）

**测试步骤**：
```bash
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.refundReport" \
  -d "app=17sing" \
  -d "order_no=ORD20260224003" \
  -d "refund_amount=20.00" \
  -d "refund_time=1709200000" \
  -d "payment_channel=stripe" \
  -d "app_uid=99999999" \
  -d "phone=13800138000" \
  -d "google_id=google_12345"
```

**预期结果**：
```json
{
  "code": 0,
  "msg": "success",
  "data": {
    "risk_user_id": 1
  }
}
```

**验证点**：
- phone=13800138000 和 google_id=google_12345 都关联到 risk_user_id=1
- 不触发用户合并（因为两者已属于同一用户）

---

#### TC-006: 缺少必填参数

**测试目的**：验证缺少必填参数时的错误处理

**测试步骤**：
```bash
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.refundReport" \
  -d "app=17sing"
```

**预期结果**：
```json
{
  "code": 1001,
  "msg": "参数缺失：order_no, refund_amount, refund_time, app_uid",
  "data": null
}
```

**验证点**：
- 返回 code=1001
- 返回缺失参数列表

---

### 11.2 风控查询接口测试用例

#### TC-101: 查询风险用户（单标识）

**测试目的**：验证通过单个标识查询风险用户

**前置条件**：已执行 TC-001

**测试步骤**：
```bash
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.riskQuery" \
  -d "phone=13800138000"
```

**预期结果**：
```json
{
  "code": 0,
  "msg": "success",
  "data": {
    "is_risk": true,
    "risk_user_id": 1,
    "total_refund_count": 2,
    "total_refund_amount": "129.00",
    "refund_summary": [
      {
        "app": "17sing",
        "refund_count": 2,
        "refund_amount": "129.00",
        "app_uid": "12345678",
        "nickname": "歌唱达人"
      }
    ]
  }
}
```

**验证点**：
- is_risk=true
- total_refund_count 和 total_refund_amount 正确
- refund_summary 按 app 汇总正确

---

#### TC-102: 查询风险用户（多标识组合）

**测试目的**：验证通过多个标识组合查询风险用户

**前置条件**：已执行 TC-001

**测试步骤**：
```bash
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.riskQuery" \
  -d "phone=13800138000" \
  -d "google_id=google_12345"
```

**预期结果**：
```json
{
  "code": 0,
  "msg": "success",
  "data": {
    "is_risk": true,
    "risk_user_id": 1,
    "total_refund_count": 2,
    "total_refund_amount": "129.00",
    "refund_summary": [
      {
        "app": "17sing",
        "refund_count": 2,
        "refund_amount": "129.00",
        "app_uid": "12345678",
        "nickname": "歌唱达人"
      }
    ]
  }
}
```

**验证点**：
- 多个标识指向同一用户时，返回正确结果

---

#### TC-103: 查询非风险用户

**测试目的**：验证查询不存在风险记录的用户

**测试步骤**：
```bash
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.riskQuery" \
  -d "phone=19999999999"
```

**预期结果**：
```json
{
  "code": 0,
  "msg": "success",
  "data": {
    "is_risk": false,
    "risk_user_id": null,
    "total_refund_count": 0,
    "total_refund_amount": "0.00",
    "refund_summary": []
  }
}
```

**验证点**：
- is_risk=false
- refund_details 为空数组

---

#### TC-104: 查询缺少账号标识

**测试目的**：验证查询时不提供任何账号标识的错误处理

**测试步骤**：
```bash
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.riskQuery"
```

**预期结果**：
```json
{
  "code": 1001,
  "msg": "至少需要提供一个账号标识",
  "data": null
}
```

---

### 11.3 撤销退款接口测试用例

#### TC-201: 正常撤销退款

**测试目的**：验证正常撤销退款功能

**前置条件**：已执行 TC-001

**测试步骤**：
```bash
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.refundCancel" \
  -d "app=17sing" \
  -d "order_no=ORD20260224001"
```

**预期结果**：
```json
{
  "code": 0,
  "msg": "success",
  "data": {
    "remaining_refund_count": 1
  }
}
```

**验证点**：
- 返回剩余有效退款数
- 订单状态更新为"已撤销"

---

#### TC-202: 撤销不存在的订单

**测试目的**：验证撤销不存在订单时的错误处理

**测试步骤**：
```bash
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.refundCancel" \
  -d "app=17sing" \
  -d "order_no=NOT_EXIST_ORDER"
```

**预期结果**：
```json
{
  "code": 2001,
  "msg": "订单不存在",
  "data": null
}
```

---

#### TC-203: 重复撤销已撤销的订单

**测试目的**：验证重复撤销的错误处理

**前置条件**：已执行 TC-201

**测试步骤**：
```bash
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.refundCancel" \
  -d "app=17sing" \
  -d "order_no=ORD20260224001"
```

**预期结果**：
```json
{
  "code": 2002,
  "msg": "订单已撤销",
  "data": null
}
```

---

### 11.4 跨 App 场景测试用例

#### TC-301: 同一用户在不同 App 退款

**测试目的**：验证同一用户在不同 App 的退款记录汇总

**前置条件**：
- 已执行 TC-001（phone=13800138000，app=17sing）

**测试步骤**：
```bash
# 在 wekara 上报退款
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.refundReport" \
  -d "app=wekara" \
  -d "order_no=WEK_ORD001" \
  -d "refund_amount=50.00" \
  -d "refund_time=1709500000" \
  -d "payment_channel=apple_pay" \
  -d "app_uid=wekara_123" \
  -d "phone=13800138000"

# 查询风控
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.riskQuery" \
  -d "phone=13800138000"
```

**预期结果**：
```json
{
  "code": 0,
  "msg": "success",
  "data": {
    "is_risk": true,
    "risk_user_id": 1,
    "total_refund_count": 3,
    "total_refund_amount": "179.00",
    "refund_summary": [
      {
        "app": "17sing",
        "refund_count": 2,
        "refund_amount": "129.00",
        "app_uid": "12345678",
        "nickname": "歌唱达人"
      },
      {
        "app": "wekara",
        "refund_count": 1,
        "refund_amount": "50.00",
        "app_uid": "wekara_123",
        "nickname": ""
      }
    ]
  }
}
```

**验证点**：
- 正确汇总不同 App 的退款记录
- 按 app 分组统计订单数和金额

---

### 11.5 边界场景测试用例

#### TC-401: 空 NULL 参数处理

**测试目的**：验证空字符串和 NULL 值的处理

**测试步骤**：
```bash
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.refundReport" \
  -d "app=17sing" \
  -d "order_no=ORD20260224099" \
  -d "refund_amount=10.00" \
  -d "refund_time=1708752000" \
  -d "payment_channel=" \
  -d "app_uid=12345" \
  -d "phone=" \
  -d "google_id="
```

**预期结果**：
```json
{
  "code": 0,
  "msg": "success",
  "data": {
    "risk_user_id": 3
  }
}
```

**验证点**：
- 空字符串的账号标识不存储
- 不影响正常流程

---

#### TC-402: 特殊字符处理

**测试目的**：验证特殊字符的安全性处理

**测试步骤**：
```bash
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.refundReport" \
  -d "app=17sing" \
  -d "order_no=ORD'SPECIAL\"CHAR" \
  -d "refund_amount=10.00" \
  -d "refund_time=1708752000" \
  -d "payment_channel=google_pay" \
  -d "app_uid=12345" \
  -d "nickname=<script>alert('xss')</script>"
```

**预期结果**：
```json
{
  "code": 0,
  "msg": "success",
  "data": {
    "risk_user_id": 4
  }
}
```

**验证点**：
- 特殊字符正确转义存储
- 无 SQL 注入风险

---

#### TC-403: 并发上报测试

**测试目的**：验证同一用户并发上报的数据一致性

**测试步骤**：
```bash
# 并发执行两个请求（使用相同账号标识，不同订单）
# 请求 1
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.refundReport" \
  -d "app=17sing" \
  -d "order_no=ORD_CONCURRENT_1" \
  -d "refund_amount=10.00" \
  -d "refund_time=1708752000" \
  -d "payment_channel=google_pay" \
  -d "app_uid=12345" \
  -d "phone=18800188000" &

# 请求 2
curl -X POST "http://localhost/action/index.php" \
  -d "action=Risk.refundReport" \
  -d "app=17sing" \
  -d "order_no=ORD_CONCURRENT_2" \
  -d "refund_amount=20.00" \
  -d "refund_time=1708752000" \
  -d "payment_channel=apple_pay" \
  -d "app_uid=12345" \
  -d "phone=18800188000" &
```

**验证点**：
- 两个请求返回相同的 risk_user_id
- 数据一致性正确

## 12. 数据库建表脚本

```sql
-- =====================================================
-- 账号风控系统建表脚本
-- 数据库：MySQL 5.7 / SQLite 兼容
-- 字符集：utf8mb4
-- 时间字段：统一使用 bigint 秒级时间戳
-- =====================================================

-- ---------------------------------------------------
-- 1. 风险用户表 t_risk_user
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS t_risk_user (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    created_at BIGINT NOT NULL DEFAULT 0,
    updated_at BIGINT NOT NULL DEFAULT 0
);

-- MySQL 版本
-- CREATE TABLE IF NOT EXISTS t_risk_user (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     created_at BIGINT NOT NULL DEFAULT 0 COMMENT '创建时间（秒级时间戳）',
--     updated_at BIGINT NOT NULL DEFAULT 0 COMMENT '更新时间（秒级时间戳）',
--     INDEX idx_created_at (created_at)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='风险用户表';

-- ---------------------------------------------------
-- 2. 账号标识表 t_risk_identifier
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS t_risk_identifier (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    risk_user_id INTEGER NOT NULL,
    app VARCHAR(32) NOT NULL,
    type VARCHAR(32) NOT NULL,
    value VARCHAR(255) NOT NULL,
    created_at BIGINT NOT NULL DEFAULT 0
);

-- 创建唯一索引（type + value + app）
CREATE UNIQUE INDEX IF NOT EXISTS uk_type_value_app ON t_risk_identifier(type, value, app);
-- 创建用户ID索引
CREATE INDEX IF NOT EXISTS idx_risk_user_id ON t_risk_identifier(risk_user_id);
-- 创建标识查询索引
CREATE INDEX IF NOT EXISTS idx_type_value ON t_risk_identifier(type, value);

-- MySQL 版本
-- CREATE TABLE IF NOT EXISTS t_risk_identifier (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     risk_user_id INT UNSIGNED NOT NULL COMMENT '风险用户ID',
--     app VARCHAR(32) NOT NULL COMMENT '应用标识',
--     type VARCHAR(32) NOT NULL COMMENT '标识类型：phone/payment_account/google_id/facebook_business_id',
--     value VARCHAR(255) NOT NULL COMMENT '标识值',
--     created_at BIGINT NOT NULL DEFAULT 0 COMMENT '创建时间（秒级时间戳）',
--     UNIQUE KEY uk_type_value_app (type, value, app),
--     INDEX idx_risk_user_id (risk_user_id),
--     INDEX idx_type_value (type, value)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='账号标识表';

-- ---------------------------------------------------
-- 3. App维度用户信息表 t_risk_user_app
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS t_risk_user_app (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    risk_user_id INTEGER NOT NULL,
    app VARCHAR(32) NOT NULL,
    uid VARCHAR(64) NOT NULL DEFAULT '',
    nickname VARCHAR(128) NOT NULL DEFAULT '',
    register_time BIGINT NOT NULL DEFAULT 0,
    register_ip VARCHAR(45) NOT NULL DEFAULT '',
    google_nickname VARCHAR(128) NOT NULL DEFAULT '',
    facebook_nickname VARCHAR(128) NOT NULL DEFAULT '',
    linked_at BIGINT NOT NULL DEFAULT 0,
    created_at BIGINT NOT NULL DEFAULT 0,
    updated_at BIGINT NOT NULL DEFAULT 0
);

-- 创建唯一索引（risk_user_id + app）
CREATE UNIQUE INDEX IF NOT EXISTS uk_user_app ON t_risk_user_app(risk_user_id, app);

-- MySQL 版本
-- CREATE TABLE IF NOT EXISTS t_risk_user_app (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     risk_user_id INT UNSIGNED NOT NULL COMMENT '风险用户ID',
--     app VARCHAR(32) NOT NULL COMMENT '应用标识',
--     uid VARCHAR(64) NOT NULL DEFAULT '' COMMENT 'App内用户UID',
--     nickname VARCHAR(128) NOT NULL DEFAULT '' COMMENT '用户昵称',
--     register_time BIGINT NOT NULL DEFAULT 0 COMMENT '注册时间（秒级时间戳）',
--     register_ip VARCHAR(45) NOT NULL DEFAULT '' COMMENT '注册IP',
--     google_nickname VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'Google账号昵称',
--     facebook_nickname VARCHAR(128) NOT NULL DEFAULT '' COMMENT 'Facebook账号昵称',
--     linked_at BIGINT NOT NULL DEFAULT 0 COMMENT '关联时间（秒级时间戳）',
--     created_at BIGINT NOT NULL DEFAULT 0 COMMENT '创建时间（秒级时间戳）',
--     updated_at BIGINT NOT NULL DEFAULT 0 COMMENT '更新时间（秒级时间戳）',
--     UNIQUE KEY uk_user_app (risk_user_id, app),
--     INDEX idx_app_uid (app, uid)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='App维度用户信息表';

-- ---------------------------------------------------
-- 4. 退款订单表 t_refund_order
-- ---------------------------------------------------
CREATE TABLE IF NOT EXISTS t_refund_order (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    risk_user_id INTEGER NOT NULL,
    app VARCHAR(32) NOT NULL,
    order_no VARCHAR(64) NOT NULL,
    refund_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    payment_channel VARCHAR(32) NOT NULL DEFAULT '',
    status TINYINT NOT NULL DEFAULT 1,
    refunded_at BIGINT NOT NULL DEFAULT 0,
    canceled_at BIGINT NOT NULL DEFAULT 0,
    created_at BIGINT NOT NULL DEFAULT 0,
    updated_at BIGINT NOT NULL DEFAULT 0
);

-- 创建唯一索引（app + order_no）
CREATE UNIQUE INDEX IF NOT EXISTS uk_app_order ON t_refund_order(app, order_no);
-- 创建用户ID索引
CREATE INDEX IF NOT EXISTS idx_risk_user_id ON t_refund_order(risk_user_id);
-- 创建状态索引
CREATE INDEX IF NOT EXISTS idx_status ON t_refund_order(status);

-- MySQL 版本
-- CREATE TABLE IF NOT EXISTS t_refund_order (
--     id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
--     risk_user_id INT UNSIGNED NOT NULL COMMENT '风险用户ID',
--     app VARCHAR(32) NOT NULL COMMENT '应用标识',
--     order_no VARCHAR(64) NOT NULL COMMENT '订单号',
--     refund_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00 COMMENT '退款金额',
--     payment_channel VARCHAR(32) NOT NULL DEFAULT '' COMMENT '支付渠道：google_pay/apple_pay/paypal/stripe/other',
--     status TINYINT NOT NULL DEFAULT 1 COMMENT '状态：1-有效 2-已撤销',
--     refunded_at BIGINT NOT NULL DEFAULT 0 COMMENT '退款时间（秒级时间戳）',
--     canceled_at BIGINT NOT NULL DEFAULT 0 COMMENT '撤销时间（秒级时间戳）',
--     created_at BIGINT NOT NULL DEFAULT 0 COMMENT '创建时间（秒级时间戳）',
--     updated_at BIGINT NOT NULL DEFAULT 0 COMMENT '更新时间（秒级时间戳）',
--     UNIQUE KEY uk_app_order (app, order_no),
--     INDEX idx_risk_user_id (risk_user_id),
--     INDEX idx_status (status)
-- ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='退款订单表';
```

**建表说明**：

| 表名 | 说明 | 唯一键 | 主要索引 |
|-----|------|-------|---------|
| t_risk_user | 风险用户表 | id | created_at |
| t_risk_identifier | 账号标识表 | type + value + app | risk_user_id, type + value |
| t_risk_user_app | App维度用户信息表 | risk_user_id + app | app + uid |
| t_refund_order | 退款订单表 | app + order_no | risk_user_id, status |

**注意事项**：
1. SQLite 版本使用 `INTEGER PRIMARY KEY AUTOINCREMENT`，MySQL 版本使用 `INT UNSIGNED AUTO_INCREMENT PRIMARY KEY`
2. 时间字段统一使用 `BIGINT` 存储秒级时间戳
3. 金额字段使用 `DECIMAL(12,2)` 精确存储
4. t_risk_identifier 表增加 app 字段，可追踪第三方账号在哪些 app 中被使用
4. 提供 SQLite 和 MySQL 两种版本的建表语句

## 13. 目录结构

```
/workspace/php
├── action/
│   ├── index.php                  # 统一入口与路由分发，解析 action=Risk.xxx
│   └── RiskControl.php            # 退款上报/风控查询/撤销接口控制器
├── services/
│   └── RiskService.php            # 账号关联、退款处理、风险判断核心逻辑
├── model/
│   ├── RiskUserModel.php          # 统一风险用户实体
│   ├── RiskUserAppModel.php       # app 维度用户信息（uid、昵称、注册信息）
│   ├── RiskIdentifierModel.php    # 账号标识表（type+value，可扩展）
│   └── RefundOrderModel.php       # 退款订单表（有效/撤销状态）
├── config/
│   └── database.php               # 数据库连接配置（ORM 使用）
└── database/
    └── 20260211_create_risk_tables.sql  # 建表脚本（t_ 前缀、索引、注释）
```
