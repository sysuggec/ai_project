<?php
declare(strict_types=1);

namespace App\Sorter;

/**
 * 冒泡排序类
 * 
 * 实现经典的冒泡排序算法，对数组进行升序排序
 */
class BubbleSort implements SortInterface
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
        $length = count($result);

        // 空数组或单元素数组直接返回
        if ($length <= 1) {
            return $result;
        }

        // 冒泡排序核心逻辑
        for ($i = 0; $i < $length - 1; $i++) {
            // 优化：标记是否发生交换
            $swapped = false;

            for ($j = 0; $j < $length - 1 - $i; $j++) {
                if ($result[$j] > $result[$j + 1]) {
                    // 交换元素
                    $temp = $result[$j];
                    $result[$j] = $result[$j + 1];
                    $result[$j + 1] = $temp;
                    $swapped = true;
                }
            }

            // 如果没有发生交换，说明数组已经有序
            if (!$swapped) {
                break;
            }
        }

        return $result;
    }
}
