<?php

declare(strict_types=1);

namespace Tests\Converter;

use PHPUnit\Framework\TestCase;
use App\Converter\SimplifiedToTraditionalConverter;

/**
 * BDD 场景测试：简体字转繁体字转换器
 * 
 * 对应特性文件：features/simplified_to_traditional.feature
 */
class SimplifiedToTraditionalConverterTest extends TestCase
{
    private SimplifiedToTraditionalConverter $converter;

    /**
     * Background: 我有一个简繁转换器
     */
    protected function setUp(): void
    {
        $this->converter = new SimplifiedToTraditionalConverter();
    }

    /**
     * Scenario: 转换单个简体字
     */
    public function testConvertSingleCharacter(): void
    {
        // When 我将 "国" 转换为繁体
        $result = $this->converter->convert('国');
        
        // Then 我应该得到 "國"
        $this->assertSame('國', $result);
    }

    /**
     * Scenario: 转换简体字符串
     */
    public function testConvertString(): void
    {
        // When 我将 "中华人民共和国" 转换为繁体
        $result = $this->converter->convert('中华人民共和国');
        
        // Then 我应该得到 "中華人民共和國"
        $this->assertSame('中華人民共和國', $result);
    }

    /**
     * Scenario: 转换包含非中文字符的字符串
     */
    public function testConvertMixedCharacters(): void
    {
        // When 我将 "Hello 中国!" 转换为繁体
        $result = $this->converter->convert('Hello 中国!');
        
        // Then 我应该得到 "Hello 中國!"
        $this->assertSame('Hello 中國!', $result);
    }

    /**
     * Scenario: 转换空字符串
     */
    public function testConvertEmptyString(): void
    {
        // When 我将 "" 转换为繁体
        $result = $this->converter->convert('');
        
        // Then 我应该得到 ""
        $this->assertSame('', $result);
    }

    /**
     * Scenario: 转换已经是繁体的字符串
     */
    public function testConvertTraditionalCharacters(): void
    {
        // When 我将 "繁體字" 转换为繁体
        $result = $this->converter->convert('繁體字');
        
        // Then 我应该得到 "繁體字"
        $this->assertSame('繁體字', $result);
    }

    /**
     * Scenario Outline: 转换常用词汇
     * @dataProvider commonWordsProvider
     */
    public function testConvertCommonWords(string $simplified, string $traditional): void
    {
        // When 我将 "<简体>" 转换为繁体
        $result = $this->converter->convert($simplified);
        
        // Then 我应该得到 "<繁体>"
        $this->assertSame($traditional, $result);
    }

    /**
     * Examples 数据提供者
     */
    public static function commonWordsProvider(): array
    {
        return [
            '软件' => ['软件', '軟體'],
            '网络' => ['网络', '網絡'],
            '数据' => ['数据', '數據'],
            '电脑' => ['电脑', '電腦'],
            '信息' => ['信息', '信息'],
        ];
    }
}
