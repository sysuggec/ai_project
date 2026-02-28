<?php

declare(strict_types=1);

namespace Tests\Converter;

use PHPUnit\Framework\TestCase;
use App\Converter\TraditionalToSimplifiedConverter;

/**
 * BDD 场景测试：繁体字转简体字转换器
 * 
 * 对应特性文件：features/traditional_to_simplified.feature
 */
class TraditionalToSimplifiedConverterTest extends TestCase
{
    private TraditionalToSimplifiedConverter $converter;

    /**
     * Background: 我有一个繁简转换器
     */
    protected function setUp(): void
    {
        $this->converter = new TraditionalToSimplifiedConverter();
    }

    /**
     * Scenario: 转换单个繁体字
     */
    public function testConvertSingleCharacter(): void
    {
        // When 我将 "國" 转换为简体
        $result = $this->converter->convert('國');
        
        // Then 我应该得到 "国"
        $this->assertSame('国', $result);
    }

    /**
     * Scenario: 转换繁体字符串
     */
    public function testConvertString(): void
    {
        // When 我将 "中華人民共和國" 转换为简体
        $result = $this->converter->convert('中華人民共和國');
        
        // Then 我应该得到 "中华人民共和国"
        $this->assertSame('中华人民共和国', $result);
    }

    /**
     * Scenario: 转换包含非中文字符的字符串
     */
    public function testConvertMixedCharacters(): void
    {
        // When 我将 "Hello 中國!" 转换为简体
        $result = $this->converter->convert('Hello 中國!');
        
        // Then 我应该得到 "Hello 中国!"
        $this->assertSame('Hello 中国!', $result);
    }

    /**
     * Scenario: 转换空字符串
     */
    public function testConvertEmptyString(): void
    {
        // When 我将 "" 转换为简体
        $result = $this->converter->convert('');
        
        // Then 我应该得到 ""
        $this->assertSame('', $result);
    }

    /**
     * Scenario: 转换已经是简体的字符串
     */
    public function testConvertSimplifiedCharacters(): void
    {
        // When 我将 "简体字" 转换为简体
        $result = $this->converter->convert('简体字');
        
        // Then 我应该得到 "简体字"
        $this->assertSame('简体字', $result);
    }

    /**
     * Scenario Outline: 转换常用词汇
     * @dataProvider commonWordsProvider
     */
    public function testConvertCommonWords(string $traditional, string $simplified): void
    {
        // When 我将 "<繁体>" 转换为简体
        $result = $this->converter->convert($traditional);
        
        // Then 我应该得到 "<简体>"
        $this->assertSame($simplified, $result);
    }

    /**
     * Examples 数据提供者
     */
    public static function commonWordsProvider(): array
    {
        return [
            '軟體' => ['軟體', '软件'],
            '網絡' => ['網絡', '网络'],
            '數據' => ['數據', '数据'],
            '電腦' => ['電腦', '电脑'],
            '信息' => ['信息', '信息'],
        ];
    }
}
