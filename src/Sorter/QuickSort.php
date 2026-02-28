<?php
declare(strict_types=1);

namespace App\Sorter;

/**
 * 快速排序类
 *
 * 实现经典的快速排序算法，对数组进行升序排序
 */
class QuickSort implements SortInterface
{
    /**
     * 对数组进行排序
     *
     * @param array $array 待排序的数组
     * @return array 排序后的数组（新数组，不修改原数组）
     */
    public function sort(array $array): array
    {
        // 复制数组以避免修改原数组
        $result = $array;

        // 空数组或单元素数组直接返回
        if (count($result) <= 1) {
            return $result;
        }

        return $this->quickSort($result);
    }

    /**
     * 快速排序递归实现
     *
     * @param array $array 待排序数组
     * @return array 排序后的数组
     */
    private function quickSort(array $array): array
    {
        $length = count($array);

        if ($length <= 1) {
            return $array;
        }

        // 选择中间元素作为基准
        $pivotIndex = (int) floor($length / 2);
        $pivot = $array[$pivotIndex];

        $left = [];
        $right = [];
        $equal = [];

        // 分区操作
        foreach ($array as $i => $value) {
            if ($value < $pivot) {
                $left[] = $value;
            } elseif ($value > $pivot) {
                $right[] = $value;
            } else {
                $equal[] = $value;
            }
        }

        // 递归排序并合并结果
        return array_merge(
            $this->quickSort($left),
            $equal,
            $this->quickSort($right)
        );
    }
}
