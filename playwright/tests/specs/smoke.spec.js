/**
 * 冒烟测试 - 验证应用基本功能
 */

import { test, expect } from '@playwright/test';

test.describe('冒烟测试', () => {
  test('应用应该能够正常加载', async ({ page }) => {
    await page.goto('/');
    
    // 验证页面标题
    await expect(page.locator('h1')).toHaveText('资源管理系统');
    
    // 验证导航栏存在
    await expect(page.locator('.tabs')).toBeVisible();
    
    // 验证有两个标签页
    const tabs = await page.locator('.tab').count();
    expect(tabs).toBe(2);
  });

  test('应该能够在两个标签页之间切换', async ({ page }) => {
    await page.goto('/');
    
    // 默认应该在上传页面
    await expect(page.locator('.upload-area')).toBeVisible();
    
    // 切换到资源列表页面
    await page.click('button:has-text("资源列表")');
    await expect(page.locator('.file-list')).toBeVisible();
    
    // 切换回上传页面
    await page.click('button:has-text("上传资源")');
    await expect(page.locator('.upload-area')).toBeVisible();
  });

  test('API 应该响应正常', async ({ page }) => {
    // 测试获取目录列表 API
    const directoriesResponse = await page.request.get('/api/directories');
    expect(directoriesResponse.status()).toBe(200);
    
    const directoriesData = await directoriesResponse.json();
    expect(directoriesData).toHaveProperty('success');
    
    // 测试获取文件列表 API
    const filesResponse = await page.request.get('/api/files');
    expect(filesResponse.status()).toBe(200);
    
    const filesData = await filesResponse.json();
    expect(filesData).toHaveProperty('success');
  });

  test('页面应该在合理时间内加载完成', async ({ page }) => {
    const startTime = Date.now();
    
    await page.goto('/');
    await page.waitForLoadState('networkidle');
    
    const loadTime = Date.now() - startTime;
    
    // 页面加载时间应该小于 5 秒
    expect(loadTime).toBeLessThan(5000);
  });

  test('响应式布局应该正常工作', async ({ page }) => {
    // 桌面视口
    await page.setViewportSize({ width: 1280, height: 720 });
    await page.goto('/');
    
    await expect(page.locator('.header')).toBeVisible();
    await expect(page.locator('.main')).toBeVisible();
    
    // 移动设备视口
    await page.setViewportSize({ width: 375, height: 667 });
    await page.reload();
    
    await expect(page.locator('.header')).toBeVisible();
    await expect(page.locator('.main')).toBeVisible();
  });
});
