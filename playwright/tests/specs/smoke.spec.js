/**
 * 冒烟测试
 */

import { test, expect } from '@playwright/test';

test.describe('冒烟测试', () => {
  test('应用应该能够正常加载', async ({ page }) => {
    await page.goto('/');
    
    // 验证标题
    await expect(page.locator('h1')).toHaveText('资源管理系统');
    
    // 验证导航标签
    await expect(page.locator('a:has-text("上传资源")')).toBeVisible();
    await expect(page.locator('a:has-text("资源列表")')).toBeVisible();
    await expect(page.locator('a:has-text("回收站")')).toBeVisible();
    await expect(page.locator('a:has-text("分享管理")')).toBeVisible();
  });

  test('应该能够在标签页之间切换', async ({ page }) => {
    await page.goto('/');
    
    // 切换到上传页面
    await page.click('a:has-text("上传资源")');
    await expect(page.locator('.upload-area')).toBeVisible();
    
    // 切换到资源列表
    await page.click('a:has-text("资源列表")');
    await expect(page.locator('.file-list')).toBeVisible();
    
    // 切换到回收站
    await page.click('a:has-text("回收站")');
    await expect(page.locator('.trash-page')).toBeVisible();
    
    // 切换到分享管理
    await page.click('a:has-text("分享管理")');
    await expect(page.locator('.share-page')).toBeVisible();
  });

  test('API 应该响应正常', async ({ page }) => {
    const response = await page.request.get('http://localhost:8080/api/directories');
    expect(response.status()).toBe(200);
    
    const data = await response.json();
    expect(data).toHaveProperty('success');
  });

  test('页面应该在合理时间内加载完成', async ({ page }) => {
    const startTime = Date.now();
    
    await page.goto('/');
    await page.waitForSelector('h1');
    
    const loadTime = Date.now() - startTime;
    expect(loadTime).toBeLessThan(5000); // 5秒内加载完成
  });

  test('响应式布局应该正常工作', async ({ page }) => {
    // 设置移动端视口
    await page.setViewportSize({ width: 375, height: 667 });
    
    await page.goto('/');
    
    // 验证页面仍然可以正常显示
    await expect(page.locator('h1')).toBeVisible();
    await expect(page.locator('.tabs')).toBeVisible();
  });
});
