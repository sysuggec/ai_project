<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Response;
use App\Services\ShareService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * 文件分享控制器
 * 
 * 处理文件分享相关请求
 */
class ShareController
{
    private ShareService $shareService;

    public function __construct()
    {
        $this->shareService = new ShareService();
    }

    /**
     * 创建分享链接
     */
    public function create(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $data = json_decode($request->getContent(), true);
        $fileId = $data['file_id'] ?? null;
        $expiresIn = $data['expires_in'] ?? 86400; // 默认24小时
        $password = $data['password'] ?? null;

        if ($fileId === null) {
            return Response::error('文件ID是必需的');
        }

        try {
            $share = $this->shareService->create((int) $fileId, (int) $expiresIn, $password);
            return Response::success($share);
        } catch (\InvalidArgumentException $e) {
            return Response::error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            return Response::error('创建分享链接失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 获取分享列表
     */
    public function index(Request $request): \Symfony\Component\HttpFoundation\Response
    {
        $page = (int) ($request->query->get('page') ?? 1);
        $perPage = (int) ($request->query->get('perPage') ?? 20);

        // 验证参数
        if ($page < 1) $page = 1;
        if ($perPage < 1 || $perPage > 100) $perPage = 20;

        $result = $this->shareService->getList($page, $perPage);

        return Response::success($result);
    }

    /**
     * 获取分享文件信息
     */
    public function show(Request $request, string $token): \Symfony\Component\HttpFoundation\Response
    {
        $data = json_decode($request->getContent(), true);
        $password = $data['password'] ?? $request->query->get('password');

        try {
            $fileInfo = $this->shareService->getByToken($token, $password);
            return Response::success($fileInfo);
        } catch (\InvalidArgumentException $e) {
            return Response::error($e->getMessage(), 404);
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), 403);
        } catch (\Throwable $e) {
            return Response::error('获取分享信息失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 下载分享文件
     */
    public function download(Request $request, string $token): \Symfony\Component\HttpFoundation\Response
    {
        $password = $request->query->get('password');

        try {
            $fileInfo = $this->shareService->getDownloadPath($token, $password);

            if (!file_exists($fileInfo['path'])) {
                return Response::error('文件不存在', 404);
            }

            $response = new BinaryFileResponse($fileInfo['path']);
            $response->setContentDisposition('attachment', $fileInfo['name']);
            $response->headers->set('Content-Type', $fileInfo['mime_type'] ?: 'application/octet-stream');

            return $response;
        } catch (\InvalidArgumentException $e) {
            return Response::error($e->getMessage(), 404);
        } catch (\RuntimeException $e) {
            return Response::error($e->getMessage(), 403);
        } catch (\Throwable $e) {
            return Response::error('下载文件失败: ' . $e->getMessage(), 500);
        }
    }

    /**
     * 删除分享
     */
    public function delete(Request $request, string $id): \Symfony\Component\HttpFoundation\Response
    {
        try {
            $this->shareService->delete((int) $id);
            return Response::success(['message' => '分享已删除']);
        } catch (\InvalidArgumentException $e) {
            return Response::error($e->getMessage(), 404);
        } catch (\Throwable $e) {
            return Response::error('删除分享失败: ' . $e->getMessage(), 500);
        }
    }
}
