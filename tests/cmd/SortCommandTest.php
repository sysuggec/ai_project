<?php
declare(strict_types=1);

namespace Tests\cmd;

use PHPUnit\Framework\TestCase;
use App\cmd\SortCommand;

/**
 * 排序命令行工具测试类
 */
class SortCommandTest extends TestCase
{
    /**
     * 测试使用冒泡排序
     */
    public function testRunWithBubbleSort(): void
    {
        $command = new SortCommand();
        $result = $command->run(['sort', '-t', 'bubble', '3,1,2']);
        $this->assertSame([1, 2, 3], $result);
    }

    /**
     * 测试使用快速排序
     */
    public function testRunWithQuickSort(): void
    {
        $command = new SortCommand();
        $result = $command->run(['sort', '-t', 'quick', '5,2,8,1,9']);
        $this->assertSame([1, 2, 5, 8, 9], $result);
    }

    /**
     * 测试默认排序方式（冒泡）
     */
    public function testRunWithDefaultSortType(): void
    {
        $command = new SortCommand();
        $result = $command->run(['sort', '3,1,2']);
        $this->assertSame([1, 2, 3], $result);
    }

    /**
     * 测试包含负数的排序
     */
    public function testRunWithNegativeNumbers(): void
    {
        $command = new SortCommand();
        $result = $command->run(['sort', '-t', 'quick', '-3,1,-5,2']);
        $this->assertSame([-5, -3, 1, 2], $result);
    }

    /**
     * 测试空输入
     */
    public function testRunWithEmptyInput(): void
    {
        $command = new SortCommand();
        $result = $command->run(['sort', '-t', 'bubble', '']);
        $this->assertSame([], $result);
    }

    /**
     * 测试单个数字
     */
    public function testRunWithSingleNumber(): void
    {
        $command = new SortCommand();
        $result = $command->run(['sort', '-t', 'quick', '42']);
        $this->assertSame([42], $result);
    }

    /**
     * 测试浮点数排序
     */
    public function testRunWithFloatNumbers(): void
    {
        $command = new SortCommand();
        $result = $command->run(['sort', '-t', 'bubble', '3.14,1.41,2.71']);
        $this->assertSame([1.41, 2.71, 3.14], $result);
    }

    /**
     * 测试无效的排序类型抛出异常
     */
    public function testRunWithInvalidSortType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown sort type: invalid');

        $command = new SortCommand();
        $command->run(['sort', '-t', 'invalid', '1,2,3']);
    }

    /**
     * 测试无参数时抛出异常
     */
    public function testRunWithNoArguments(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('No input data provided');

        $command = new SortCommand();
        $command->run(['sort']);
    }

    /**
     * 测试获取帮助信息
     */
    public function testGetHelp(): void
    {
        $help = SortCommand::getHelp();
        $this->assertStringContainsString('Usage:', $help);
        $this->assertStringContainsString('-t', $help);
        $this->assertStringContainsString('bubble', $help);
        $this->assertStringContainsString('quick', $help);
    }

    /**
     * 测试降序排序
     */
    public function testRunWithDescendingOrder(): void
    {
        $command = new SortCommand();
        $result = $command->run(['sort', '-o', 'desc', '3,1,2']);
        $this->assertSame([3, 2, 1], $result);
    }

    /**
     * 测试显式指定升序排序
     */
    public function testRunWithExplicitAscendingOrder(): void
    {
        $command = new SortCommand();
        $result = $command->run(['sort', '-o', 'asc', '3,1,2']);
        $this->assertSame([1, 2, 3], $result);
    }

    /**
     * 测试降序排序与排序类型组合
     */
    public function testRunWithDescendingOrderAndSortType(): void
    {
        $command = new SortCommand();
        $result = $command->run(['sort', '-t', 'quick', '-o', 'desc', '5,2,8,1,9']);
        $this->assertSame([9, 8, 5, 2, 1], $result);
    }

    /**
     * 测试默认排序顺序为升序
     */
    public function testDefaultOrderIsAscending(): void
    {
        $command = new SortCommand();
        $result = $command->run(['sort', '3,1,2']);
        $this->assertSame([1, 2, 3], $result);
    }

    /**
     * 测试无效的排序顺序抛出异常
     */
    public function testRunWithInvalidOrder(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown sort order: invalid');

        $command = new SortCommand();
        $command->run(['sort', '-o', 'invalid', '1,2,3']);
    }

    /**
     * 测试帮助信息包含排序顺序选项
     */
    public function testHelpContainsOrderOption(): void
    {
        $help = SortCommand::getHelp();
        $this->assertStringContainsString('-o', $help);
        $this->assertStringContainsString('asc', $help);
        $this->assertStringContainsString('desc', $help);
    }
}
