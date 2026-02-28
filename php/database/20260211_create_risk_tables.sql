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
