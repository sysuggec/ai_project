<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Services\TrashService;
use Symfony\Component\HttpFoundation\Request;

/**
 * 回收站控制器
 * 
 * 处理回收站相关请求
 */
class TrashController
{
    private TrashService $trashService;

    public function __construct()
    {
        $this->trashService = new TrashService();
    }

    /**
     * 获取回收站文件列表
     */
    public function index(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $files = $this->trashService->getList();

        return Response::success(['files' => $files]);
    }

    /**
     * 恢复文件
     */
    public function restore(Request $request, string $id): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $this->trashService->restore((int) $id);
            return Response::success(['message' => '文件已恢复到原位置']);
        } catch (\InvalidArgumentException $e) {
            return Response::error($e->getMessage(), 404);
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), 409);
        } catch (\Throwable $e) {
            return Response::error('恢复文件失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 彻底删除文件
     */
    public function deletePermanently(Request $request, string $id): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $this->trashService->deletePermanently((int) $id);
            return Response::success(['message' => '文件已彻底删除']);
        } catch (\InvalidArgumentException $e) {
            return Response::error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            return Response::error('删除文件失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 清空回收站
     */
    public function clear(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $count = $this->trashService->clear();
            return Response::success([
                'message' => '回收站已清空',
                'deleted_count' => $count,
            ]);
        } catch (\Throwable $e) {
            return Response::error('清空回收站失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 执行自动清理
     */
    public function autoCleanup(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $count = $this->trashService->autoCleanup();
            return Response::success([
                'message' => '自动清理完成',
                'cleaned_count' => $count,
            ]);
        } catch (\Throwable $e) {
            return Response::error('自动清理失败: ' . $e->getMessage(), 500);
        }
    }
}
