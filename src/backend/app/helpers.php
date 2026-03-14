<?php
declare(strict_types=1);

use DateTime;

/**
 * 获取当前时间作为 DateTime 实例
 *
 * @return DateTime
 */
function now(): DateTime
{
    return new DateTime();
}

/**
 * 获取当前日期时间字符串
 *
 * @return string
 */
function currentDateTime(): string
{
    return date('Y-m-d H:i:s');
}
