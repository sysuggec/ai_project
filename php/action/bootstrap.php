<?php
/**
 * 应用引导文件
 */

// 加载自动加载
require_once __DIR__ . '/vendor/autoload.php';

// 初始化数据库
App\Config\Database::init();
