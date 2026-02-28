<?php

declare(strict_types=1);

namespace App\Converter;

/**
 * 繁体字转简体字转换器
 * 
 * 将繁体中文字符串转换为简体中文
 */
class TraditionalToSimplifiedConverter
{
    /**
     * 词汇级别转换表（台湾用语 => 大陆用语）
     * 优先级高于字符级转换
     */
    private const WORD_MAP = [
        '軟體' => '软件',
        '網絡' => '网络',
        '數據' => '数据',
        '電腦' => '电脑',
    ];

    /**
     * 繁简对照表（常用字）
     */
    private const CONVERSION_MAP = [
        // 单字
        '國' => '国',
        '中' => '中',
        '華' => '华',
        '人' => '人',
        '民' => '民',
        '共' => '共',
        '和' => '和',
        
        // 常用字符
        '軟' => '软',
        '體' => '体',
        '網' => '网',
        '絡' => '络',
        '數' => '数',
        '據' => '据',
        '電' => '电',
        '腦' => '脑',
        
        // 其他常用繁简对照
        '個' => '个',
        '們' => '们',
        '學' => '学',
        '習' => '习',
        '書' => '书',
        '話' => '话',
        '門' => '门',
        '車' => '车',
        '馬' => '马',
        '鳥' => '鸟',
        '魚' => '鱼',
        '龍' => '龙',
        '風' => '风',
        '雲' => '云',
        '雨' => '雨',
        '雪' => '雪',
        '時' => '时',
        '間' => '间',
        '東' => '东',
        '西' => '西',
        '南' => '南',
        '北' => '北',
        '後' => '后',
        '前' => '前',
        '左' => '左',
        '右' => '右',
        '裡' => '里',
        '面' => '面',
        '頭' => '头',
        '手' => '手',
        '眼' => '眼',
        '見' => '见',
        '聽' => '听',
        '說' => '说',
        '讀' => '读',
        '寫' => '写',
        '字' => '字',
        '詞' => '词',
        '語' => '语',
        '言' => '言',
        '計' => '计',
        '劃' => '划',
        '設' => '设',
        '開' => '开',
        '發' => '发',
        '關' => '关',
        '閉' => '闭',
        '進' => '进',
        '出' => '出',
        '入' => '入',
        '來' => '来',
        '去' => '去',
        '變' => '变',
        '化' => '化',
        '新' => '新',
        '舊' => '旧',
        '長' => '长',
        '短' => '短',
        '大' => '大',
        '小' => '小',
        '多' => '多',
        '少' => '少',
        '高' => '高',
        '低' => '低',
        '快' => '快',
        '慢' => '慢',
        '好' => '好',
        '壞' => '坏',
        '對' => '对',
        '錯' => '错',
        '是' => '是',
        '非' => '非',
        '有' => '有',
        '無' => '无',
        '真' => '真',
        '假' => '假',
        '繁' => '繁',
        '簡' => '简',
    ];

    /**
     * 将繁体中文字符串转换为简体中文
     *
     * @param string $text 繁体中文文本
     * @return string 简体中文文本
     */
    public function convert(string $text): string
    {
        if ($text === '') {
            return '';
        }

        // 先进行词汇级别转换
        $result = $this->convertWords($text);

        // 再进行字符级别转换
        return $this->convertChars($result);
    }

    /**
     * 词汇级别转换
     *
     * @param string $text 输入文本
     * @return string 转换后的文本
     */
    private function convertWords(string $text): string
    {
        return strtr($text, self::WORD_MAP);
    }

    /**
     * 字符级别转换
     *
     * @param string $text 输入文本
     * @return string 转换后的文本
     */
    private function convertChars(string $text): string
    {
        $result = '';
        $length = mb_strlen($text, 'UTF-8');

        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($text, $i, 1, 'UTF-8');
            $result .= $this->convertChar($char);
        }

        return $result;
    }

    /**
     * 转换单个字符
     *
     * @param string $char 单个字符
     * @return string 转换后的字符
     */
    private function convertChar(string $char): string
    {
        return self::CONVERSION_MAP[$char] ?? $char;
    }
}
