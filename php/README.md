# 账号风控系统

跨 App 风险控制服务，用于汇聚各应用的退款与账号信息。

## 目录结构

```
php/
├── action/           # 控制器层
│   ├── index.php     # 统一入口
│   └── RiskControl.php
├── services/         # 服务层
│   └── RiskService.php
├── model/            # 数据模型层
│   ├── RiskUserModel.php
│   ├── RiskIdentifierModel.php
│   ├── RiskUserAppModel.php
│   └── RefundOrderModel.php
├── config/           # 配置文件
│   └── database.php
├── database/         # 数据库脚本
│   └── 20260211_create_risk_tables.sql
├── vendor/           # 依赖包
├── composer.json     # 依赖配置
├── .env              # 环境配置
└── .env.example      # 环境配置示例
```

## 安装

```bash
cd php
composer install
```

## 配置

复制 `.env.example` 为 `.env` 并修改配置：

```bash
cp .env.example .env
```

## 数据库初始化

```bash
# SQLite
touch database/riskctl.db
sqlite3 database/riskctl.db < database/20260211_create_risk_tables.sql

# MySQL
mysql -u root -p riskctl < database/20260211_create_risk_tables.sql
```

## 接口调用

```bash
# 退款上报
curl -X POST "http://localhost/php/action/index.php" \
  -d "action=Risk.refundReport" \
  -d "app=17sing" \
  -d "order_no=ORD001" \
  -d "refund_amount=99.00" \
  -d "refund_time=$(date +%s)" \
  -d "app_uid=12345678" \
  -d "phone=13800138000"

# 风控查询
curl -X POST "http://localhost/php/action/index.php" \
  -d "action=Risk.riskQuery" \
  -d "phone=13800138000"

# 撤销退款
curl -X POST "http://localhost/php/action/index.php" \
  -d "action=Risk.refundCancel" \
  -d "app=17sing" \
  -d "order_no=ORD001"
```
