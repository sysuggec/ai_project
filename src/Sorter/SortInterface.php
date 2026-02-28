<?php
declare(strict_types=1);

namespace App\Sorter;

/**
 * 排序算法接口
 */
interface SortInterface
{
    /**
     * 对数组进行排序
     *
     * @param array $array 待排序的数组
     * @return array 排序后的数组
     */
    public function sort(array $array): array;
}
