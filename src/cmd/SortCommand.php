<?php
declare(strict_types=1);

namespace App\cmd;

use App\Sorter\BubbleSort;
use App\Sorter\QuickSort;
use App\Sorter\SortInterface;

/**
 * 排序命令行工具
 *
 * 使用示例: php sort.php -t bubble 3,1,2
 */
class SortCommand
{
    /**
     * 排序器映射
     *
     * @var array<string, class-string<SortInterface>>
     */
    private const SORTERS = [
        'bubble' => BubbleSort::class,
        'quick' => QuickSort::class,
    ];

    /**
     * 默认排序类型
     */
    private const DEFAULT_SORT_TYPE = 'bubble';

    /**
     * 执行排序命令
     *
     * @param array $argv 命令行参数
     * @return array 排序后的数组
     * @throws \InvalidArgumentException 当参数无效时
     */
    public function run(array $argv): array
    {
        // 检查是否提供了参数
        if (count($argv) < 2) {
            throw new \InvalidArgumentException('No input data provided');
        }

        // 解析参数
        $parsed = $this->parseArguments($argv);

        // 解析输入数据
        $data = $this->parseInput($parsed['input']);

        // 获取排序器并执行排序
        $sorter = $this->getSorter($parsed['type']);

        return $sorter->sort($data);
    }

    /**
     * 解析命令行参数
     *
     * @param array $argv 命令行参数
     * @return array{type: string, input: string}
     */
    private function parseArguments(array $argv): array
    {
        $type = self::DEFAULT_SORT_TYPE;
        $input = '';

        $argc = count($argv);
        for ($i = 1; $i < $argc; $i++) {
            if ($argv[$i] === '-t' && isset($argv[$i + 1])) {
                $type = $argv[$i + 1];
                $i++;
            } elseif ($argv[$i] !== '-t') {
                $input = $argv[$i];
            }
        }

        return [
            'type' => $type,
            'input' => $input,
        ];
    }

    /**
     * 解析输入数据
     *
     * @param string $input 逗号分隔的字符串
     * @return array 解析后的数字数组
     */
    private function parseInput(string $input): array
    {
        if (trim($input) === '') {
            return [];
        }

        $parts = explode(',', $input);
        $result = [];

        foreach ($parts as $part) {
            $trimmed = trim($part);
            if ($trimmed !== '') {
                // 自动判断整数或浮点数
                $value = strpos($trimmed, '.') !== false
                    ? (float) $trimmed
                    : (int) $trimmed;
                $result[] = $value;
            }
        }

        return $result;
    }

    /**
     * 获取排序器实例
     *
     * @param string $type 排序类型
     * @return SortInterface 排序器实例
     * @throws \InvalidArgumentException 当排序类型无效时
     */
    private function getSorter(string $type): SortInterface
    {
        if (!isset(self::SORTERS[$type])) {
            throw new \InvalidArgumentException("Unknown sort type: {$type}");
        }

        $sorterClass = self::SORTERS[$type];
        return new $sorterClass();
    }

    /**
     * 获取帮助信息
     *
     * @return string 帮助文本
     */
    public static function getHelp(): string
    {
        $types = implode(', ', array_keys(self::SORTERS));

        return <<<HELP
Usage: php sort.php [-t <type>] <numbers>

Options:
  -t <type>    Sort algorithm type (default: bubble)
               Available types: {$types}

Arguments:
  <numbers>    Comma-separated numbers to sort

Examples:
  php sort.php -t bubble 3,1,2
  php sort.php -t quick 5,2,8,1,9
  php sort.php 1,5,3,2

HELP;
    }
}
