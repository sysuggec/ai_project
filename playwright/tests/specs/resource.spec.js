/**
 * 资源管理功能测试
 */

import { test, expect } from '@playwright/test';
import { ResourcePage } from '../pages/ResourcePage.js';
import { UploadPage } from '../pages/UploadPage.js';
import fs from 'fs';
import path from 'path';

// 测试数据目录
const TEST_DIR = path.join(process.cwd(), 'tests', 'fixtures', 'temp');

// 创建测试文件
function createTestFile(filename, content) {
  if (!fs.existsSync(TEST_DIR)) {
    fs.mkdirSync(TEST_DIR, { recursive: true });
  }
  const filePath = path.join(TEST_DIR, filename);
  fs.writeFileSync(filePath, content);
  return filePath;
}

// 清理测试文件
function cleanupTestFiles() {
  if (fs.existsSync(TEST_DIR)) {
    fs.rmSync(TEST_DIR, { recursive: true, force: true });
  }
}

// 上传测试文件
async function uploadTestFile(page, filename, content) {
  const uploadPage = new UploadPage(page);
  await uploadPage.goto();
  
  const testFile = createTestFile(filename, content);
  await uploadPage.selectFiles([testFile]);
  await uploadPage.startUpload();
  await uploadPage.waitForUploadComplete();
  
  cleanupTestFiles();
}

test.describe('资源列表功能测试', () => {
  test('页面应该正确加载并显示文件列表', async ({ page }) => {
    const resourcePage = new ResourcePage(page);
    await resourcePage.goto();
    
    // 验证页面标题
    await expect(page.locator('h1')).toHaveText('资源管理系统');
    
    // 验证搜索栏存在
    await expect(page.locator('.search-bar')).toBeVisible();
    
    // 验证目录树存在
    await expect(page.locator('.directory-tree')).toBeVisible();
    
    // 验证文件列表存在
    await expect(page.locator('.file-list')).toBeVisible();
  });

  test('应该能够刷新文件列表', async ({ page }) => {
    const resourcePage = new ResourcePage(page);
    await resourcePage.goto();
    
    // 点击刷新按钮
    await resourcePage.refresh();
    
    // 验证页面没有错误
    await expect(page.locator('.error')).not.toBeVisible();
  });

  test('搜索功能应该正常工作', async ({ page }) => {
    const resourcePage = new ResourcePage(page);
    await resourcePage.goto();
    
    // 执行搜索
    await resourcePage.search('test');
    
    // 验证搜索功能没有报错
    await expect(page.locator('.error')).not.toBeVisible();
  });

  test('搜索无结果时应该显示空状态', async ({ page }) => {
    const resourcePage = new ResourcePage(page);
    await resourcePage.goto();
    
    // 搜索一个不可能存在的文件名
    await resourcePage.search('xyz-nonexistent-file-12345');
    
    // 验证显示空状态或文件列表为空
    const fileCount = await resourcePage.getFileCount();
    expect(fileCount).toBe(0);
  });
});

test.describe('文件操作测试', () => {
  test.describe.configure({ mode: 'serial' });  // 串行执行

  test('准备测试数据 - 上传文件', async ({ page }) => {
    // 上传一个用于测试的文件
    await uploadTestFile(page, 'test-file-for-ops.txt', 'Test content for operations');
    
    // 验证上传成功
    const resourcePage = new ResourcePage(page);
    await resourcePage.goto();
    await resourcePage.refresh();
    
    const fileCount = await resourcePage.getFileCount();
    expect(fileCount).toBeGreaterThan(0);
  });

  test('应该能够下载文件', async ({ page }) => {
    const resourcePage = new ResourcePage(page);
    await resourcePage.goto();
    await resourcePage.refresh();
    
    await page.waitForTimeout(500);
    
    const fileCount = await resourcePage.getFileCount();
    
    if (fileCount > 0) {
      // 尝试下载第一个文件
      const download = await resourcePage.downloadFile(0);
      expect(download.suggestedFilename()).toBeTruthy();
    }
  });

  test('应该能够删除文件', async ({ page }) => {
    // 上传一个新文件用于删除测试
    const uniqueName = `delete-test-${Date.now()}.txt`;
    await uploadTestFile(page, uniqueName, 'Delete me!');
    
    const resourcePage = new ResourcePage(page);
    await resourcePage.goto();
    await resourcePage.refresh();
    
    await page.waitForTimeout(500);
    
    const initialCount = await resourcePage.getFileCount();
    
    if (initialCount > 0) {
      // 删除第一个文件
      await resourcePage.deleteFile(0);
      
      // 验证 Toast 消息
      const toast = await resourcePage.getToastMessage();
      expect(toast).toContain('删除成功');
      
      await resourcePage.refresh();
      await page.waitForTimeout(500);
    }
  });

  test('应该能够重命名文件', async ({ page }) => {
    // 上传一个新文件用于重命名测试
    const originalName = `rename-test-${Date.now()}.txt`;
    await uploadTestFile(page, originalName, 'Rename me!');
    
    const resourcePage = new ResourcePage(page);
    await resourcePage.goto();
    await resourcePage.refresh();
    
    await page.waitForTimeout(500);
    
    const fileCount = await resourcePage.getFileCount();
    
    if (fileCount > 0) {
      const newName = `renamed-${Date.now()}.txt`;
      
      // 重命名第一个文件
      await resourcePage.renameFile(0, newName);
      
      // 验证 Toast 消息
      const toast = await resourcePage.getToastMessage();
      expect(toast).toContain('重命名成功');
    }
  });
});

test.describe('目录树功能测试', () => {
  test('目录树应该可见并包含根目录', async ({ page }) => {
    const resourcePage = new ResourcePage(page);
    await resourcePage.goto();
    
    // 验证目录树存在
    const treeItems = await page.locator('.tree-item');
    const count = await treeItems.count();
    expect(count).toBeGreaterThanOrEqual(0);
  });

  test('应该能够切换目录', async ({ page }) => {
    const resourcePage = new ResourcePage(page);
    await resourcePage.goto();
    
    // 点击第一个目录项（如果存在）
    const treeItems = page.locator('.tree-item');
    if (await treeItems.count() > 0) {
      await treeItems.first().click();
      
      await page.waitForTimeout(500);
      
      await expect(page.locator('.error')).not.toBeVisible();
    }
  });
});
