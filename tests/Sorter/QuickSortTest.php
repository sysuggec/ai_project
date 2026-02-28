<?php
declare(strict_types=1);

namespace Tests\Sorter;

use PHPUnit\Framework\TestCase;
use App\Sorter\QuickSort;

/**
 * 快速排序测试类
 */
class QuickSortTest extends TestCase
{
    /**
     * 测试对空数组进行排序
     */
    public function testSortEmptyArray(): void
    {
        $sorter = new QuickSort();
        $result = $sorter->sort([]);
        $this->assertSame([], $result);
    }

    /**
     * 测试对单个元素数组进行排序
     */
    public function testSortSingleElement(): void
    {
        $sorter = new QuickSort();
        $result = $sorter->sort([1]);
        $this->assertSame([1], $result);
    }

    /**
     * 测试对两个元素数组进行排序
     */
    public function testSortTwoElements(): void
    {
        $sorter = new QuickSort();
        $result = $sorter->sort([2, 1]);
        $this->assertSame([1, 2], $result);
    }

    /**
     * 测试对已排序数组进行排序
     */
    public function testSortAlreadySortedArray(): void
    {
        $sorter = new QuickSort();
        $result = $sorter->sort([1, 2, 3, 4, 5]);
        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    /**
     * 测试对逆序数组进行排序
     */
    public function testSortReverseOrderArray(): void
    {
        $sorter = new QuickSort();
        $result = $sorter->sort([5, 4, 3, 2, 1]);
        $this->assertSame([1, 2, 3, 4, 5], $result);
    }

    /**
     * 测试对随机顺序数组进行排序
     */
    public function testSortRandomOrderArray(): void
    {
        $sorter = new QuickSort();
        $result = $sorter->sort([3, 1, 4, 1, 5, 9, 2, 6]);
        $this->assertSame([1, 1, 2, 3, 4, 5, 6, 9], $result);
    }

    /**
     * 测试对包含负数的数组进行排序
     */
    public function testSortArrayWithNegativeNumbers(): void
    {
        $sorter = new QuickSort();
        $result = $sorter->sort([-3, -1, -4, 1, 5, -9, 2, -6]);
        $this->assertSame([-9, -6, -4, -3, -1, 1, 2, 5], $result);
    }

    /**
     * 测试排序不修改原数组
     */
    public function testSortDoesNotModifyOriginalArray(): void
    {
        $sorter = new QuickSort();
        $original = [3, 1, 2];
        $result = $sorter->sort($original);
        $this->assertSame([3, 1, 2], $original);
        $this->assertSame([1, 2, 3], $result);
    }

    /**
     * 测试对浮点数数组进行排序
     */
    public function testSortFloatNumbers(): void
    {
        $sorter = new QuickSort();
        $result = $sorter->sort([3.14, 1.41, 2.71, 0.58]);
        $this->assertSame([0.58, 1.41, 2.71, 3.14], $result);
    }

    /**
     * 测试对大数组进行排序
     */
    public function testSortLargeArray(): void
    {
        $sorter = new QuickSort();
        $input = range(100, 1);
        shuffle($input);
        $result = $sorter->sort($input);
        $this->assertSame(range(1, 100), $result);
    }
}
