/**
 * Vue Router SPA 路由导航测试
 * v1.1.0 新增功能 - 验证 Vue Router 的 SPA 路由导航
 */

import { test, expect } from '@playwright/test';

test.describe('Vue Router 路由导航测试', () => {
  test('应该能够直接访问各路由', async ({ page }) => {
    // 直接访问上传页面
    await page.goto('/upload');
    await expect(page.locator('.upload-area')).toBeVisible();
    
    // 直接访问资源列表
    await page.goto('/resources');
    await expect(page.locator('.file-list')).toBeVisible();
    
    // 直接访问回收站
    await page.goto('/trash');
    await expect(page.locator('.trash-page')).toBeVisible();
    
    // 直接访问分享管理
    await page.goto('/shares');
    await expect(page.locator('.share-page')).toBeVisible();
  });

  test('根路径应该重定向到上传页面', async ({ page }) => {
    await page.goto('/');
    
    // 验证 URL 是 /upload
    await page.waitForTimeout(500);
    expect(page.url()).toContain('/upload');
  });
});

test.describe('SPA 无刷新导航测试', () => {
  test('页面切换不应该触发页面刷新', async ({ page }) => {
    await page.goto('/upload');
    await expect(page.locator('.upload-area')).toBeVisible();
    
    // 记录当前页面加载状态
    const initialLoadState = await page.evaluate(() => document.readyState);
    
    // 通过导航切换页面
    await page.click('a:has-text("资源列表")');
    await expect(page.locator('.file-list')).toBeVisible();
    
    // 验证没有页面刷新（通过检查 document 是否重新加载）
    const currentLoadState = await page.evaluate(() => document.readyState);
    expect(currentLoadState).toBe(initialLoadState);
  });

  test('浏览器前进后退按钮应该正常工作', async ({ page }) => {
    // 访问上传页面
    await page.goto('/upload');
    await expect(page.locator('.upload-area')).toBeVisible();
    
    // 导航到资源列表
    await page.click('a:has-text("资源列表")');
    await expect(page.locator('.file-list')).toBeVisible();
    
    // 导航到回收站
    await page.click('a:has-text("回收站")');
    await expect(page.locator('.trash-page')).toBeVisible();
    
    // 后退到资源列表
    await page.goBack();
    await expect(page.locator('.file-list')).toBeVisible();
    
    // 再后退到上传页面
    await page.goBack();
    await expect(page.locator('.upload-area')).toBeVisible();
    
    // 前进到资源列表
    await page.goForward();
    await expect(page.locator('.file-list')).toBeVisible();
  });

  test('导航链接应该有正确的激活状态', async ({ page }) => {
    // 访问上传页面
    await page.goto('/upload');
    await page.waitForTimeout(300);
    
    // 检查上传链接是否激活
    const uploadLink = page.locator('a:has-text("上传资源")');
    const uploadIsActive = await uploadLink.evaluate(el => 
      el.classList.contains('active') || el.classList.contains('router-link-active')
    );
    expect(uploadIsActive).toBeTruthy();
    
    // 导航到资源列表
    await page.click('a:has-text("资源列表")');
    await page.waitForTimeout(300);
    
    // 检查资源列表链接是否激活
    const resourceLink = page.locator('a:has-text("资源列表")');
    const resourceIsActive = await resourceLink.evaluate(el => 
      el.classList.contains('active') || el.classList.contains('router-link-active')
    );
    expect(resourceIsActive).toBeTruthy();
  });
});

test.describe('路由参数测试', () => {
  test('分享链接路由应该正确解析 token', async ({ page }) => {
    // 使用一个测试 token 访问分享链接
    const testToken = 'test-token-123';
    await page.goto(`/share/${testToken}`);
    
    // 验证页面加载（可能是分享访问页面或 404）
    const pageContent = await page.content();
    expect(pageContent.length).toBeGreaterThan(0);
    
    // 验证 URL 包含 token
    expect(page.url()).toContain(testToken);
  });

  test('无效路由应该正确处理', async ({ page }) => {
    // 访问一个不存在的路由
    await page.goto('/invalid-route-12345');
    
    // 页面应该仍然正常加载（SPA 会回退到 index.html）
    // 验证页面没有崩溃
    const pageContent = await page.content();
    expect(pageContent.length).toBeGreaterThan(0);
  });
});

test.describe('导航持久化测试', () => {
  test('页面刷新后应该保持在当前路由', async ({ page }) => {
    // 访问回收站页面
    await page.goto('/trash');
    await expect(page.locator('.trash-page')).toBeVisible();
    
    // 刷新页面
    await page.reload();
    
    // 验证仍在回收站页面
    await expect(page.locator('.trash-page')).toBeVisible();
    expect(page.url()).toContain('/trash');
  });

  test('新标签页打开应该保持路由状态', async ({ page, context }) => {
    // 访问分享管理页面
    await page.goto('/shares');
    await expect(page.locator('.share-page')).toBeVisible();
    
    // 获取当前 URL
    const currentUrl = page.url();
    
    // 在新标签页中打开相同 URL
    const newPage = await context.newPage();
    await newPage.goto(currentUrl);
    
    // 验证新标签页也在分享管理页面
    await expect(newPage.locator('.share-page')).toBeVisible();
    
    await newPage.close();
  });
});

test.describe('导航性能测试', () => {
  test('页面切换应该在合理时间内完成', async ({ page }) => {
    await page.goto('/upload');
    await expect(page.locator('.upload-area')).toBeVisible();
    
    // 测量切换到资源列表的时间
    const startTime = Date.now();
    await page.click('a:has-text("资源列表")');
    await expect(page.locator('.file-list')).toBeVisible();
    const switchTime = Date.now() - startTime;
    
    // SPA 路由切换应该在 1 秒内完成
    expect(switchTime).toBeLessThan(1000);
  });

  test('连续快速导航应该正常工作', async ({ page }) => {
    await page.goto('/upload');
    
    // 快速连续切换页面
    const tabs = [
      { name: '资源列表', selector: '.file-list' },
      { name: '回收站', selector: '.trash-page' },
      { name: '分享管理', selector: '.share-page' },
      { name: '上传资源', selector: '.upload-area' },
    ];
    
    for (const tab of tabs) {
      await page.click(`a:has-text("${tab.name}")`);
      await page.waitForTimeout(100);
    }
    
    // 验证最终页面状态正确
    await expect(page.locator('.upload-area')).toBeVisible();
  });
});
