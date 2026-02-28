# 需求文档：账号风控系统

## 文档信息

- **需求 ID**: 2026-02-28-risk-control-system
- **创建日期**: 2026-02-28
- **状态**: 待开发

## 产品定位

账号风控系统是一个跨 app 的风险控制服务，用于汇聚各应用的退款与账号信息，实现账号关联与风险判断，帮助业务系统在用户注册、登录、储值前识别风险用户。

## 核心价值

1. 统一多 app 风控数据，打破应用间信息孤岛
2. 通过多种账号标识类型实现跨应用用户关联
3. 提供实时风控查询接口，降低业务系统退款损失

## 功能需求

### 1. 退款上报接口 (refundReport)

**输入参数**：
| 参数名 | 类型 | 必填 | 说明 |
|-------|------|-----|------|
| app | string | 是 | 应用标识 |
| order_no | string | 是 | 订单号 |
| refund_amount | decimal | 是 | 退款金额 |
| refund_time | int | 是 | 退款时间（秒级时间戳） |
| payment_channel | string | 否 | 支付渠道 |
| app_uid | string | 是 | app 内用户 uid |
| nickname | string | 否 | 用户昵称 |
| register_time | int | 否 | 注册时间 |
| register_ip | string | 否 | 注册 IP |
| phone | string | 否 | 手机号 |
| payment_account | string | 否 | 支付账号 |
| google_id | string | 否 | Google 账号 ID |
| google_nickname | string | 否 | Google 昵称 |
| facebook_business_id | string | 否 | Facebook Business ID |
| facebook_nickname | string | 否 | Facebook 昵称 |

**输出**：
```json
{
  "code": 0,
  "msg": "success",
  "data": {
    "risk_user_id": 1001
  }
}
```

**业务规则**：
- 幂等性：app + order_no 唯一
- 根据账号标识查找/创建风险用户
- 多标识关联不同用户时执行合并策略

### 2. 风控查询接口 (riskQuery)

**输入参数**：
| 参数名 | 类型 | 必填 | 说明 |
|-------|------|-----|------|
| phone | string | 否 | 手机号 |
| payment_account | string | 否 | 支付账号 |
| google_id | string | 否 | Google 账号 ID |
| facebook_business_id | string | 否 | Facebook Business ID |

> 注：至少提供一个账号标识

**输出**：
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
      }
    ]
  }
}
```

**业务规则**：
- 根据任意标识查找风险用户
- 返回跨 App 退款汇总
- 按 App 分组，金额降序

### 3. 撤销退款接口 (refundCancel)

**输入参数**：
| 参数名 | 类型 | 必填 | 说明 |
|-------|------|-----|------|
| app | string | 是 | 应用标识 |
| order_no | string | 是 | 订单号 |

**输出**：
```json
{
  "code": 0,
  "msg": "success",
  "data": {
    "remaining_refund_count": 2
  }
}
```

**业务规则**：
- 订单不存在返回 2001
- 订单已撤销返回 2002
- 返回剩余有效退款数

## 数据模型

| 表名 | 说明 | 唯一键 |
|-----|------|-------|
| t_risk_user | 风险用户表 | id |
| t_risk_identifier | 账号标识表 | type + value + app |
| t_risk_user_app | App 维度用户信息表 | risk_user_id + app |
| t_refund_order | 退款订单表 | app + order_no |

## 技术要求

- 语言：PHP 7.4
- ORM：Laravel Eloquent（独立使用）
- 数据库：SQLite（开发）/ MySQL 5.7（生产）
- 时间字段：BIGINT 秒级时间戳
- 金额字段：DECIMAL(12,2)

## 性能要求

- 核心查询响应时间 < 100ms
- 支持高并发查询场景

## 安全要求

- 使用 ORM 防止 SQL 注入
- 敏感信息记录（注册 IP）

## 验收标准

1. 所有接口返回正确的 JSON 格式
2. 退款上报支持幂等
3. 用户合并策略正确执行
4. 跨 App 退款汇总正确
5. 所有 PRD 测试用例通过
6. 响应时间 < 100ms
