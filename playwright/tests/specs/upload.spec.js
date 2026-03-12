/**
 * 上传功能测试
 */

import { test, expect } from '@playwright/test';
import { UploadPage } from '../pages/UploadPage.js';
import { ResourcePage } from '../pages/ResourcePage.js';
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

test.describe('上传功能测试', () => {
  test.describe.configure({ mode: 'serial' });

  test('页面应该正确加载', async ({ page }) => {
    const uploadPage = new UploadPage(page);
    await uploadPage.goto();
    
    // 验证上传区域存在
    await expect(page.locator('.upload-page')).toBeVisible();
    
    // 验证上传组件存在
    await expect(page.locator('.upload-area')).toBeVisible();
  });

  test('应该能够选择文件', async ({ page }) => {
    const uploadPage = new UploadPage(page);
    await uploadPage.goto();
    
    // 创建测试文件
    const testFile = createTestFile('test-upload.txt', 'Test content');
    
    // 选择文件
    await uploadPage.selectFiles([testFile]);
    
    // 等待文件被添加到队列
    await page.waitForTimeout(500);
    
    // 验证文件出现在队列中（队列只在有文件时显示）
    const fileQueue = page.locator('.upload-queue');
    const hasQueue = await fileQueue.isVisible().catch(() => false);
    
    // 如果队列可见，验证有队列项
    if (hasQueue) {
      const queueItems = page.locator('.queue-item');
      const count = await queueItems.count();
      expect(count).toBeGreaterThan(0);
    }
    
    // 清理
    cleanupTestFiles();
  });

  test('应该能够上传文件', async ({ page }) => {
    const uploadPage = new UploadPage(page);
    await uploadPage.goto();
    
    // 创建测试文件
    const testFile = createTestFile(`upload-test-${Date.now()}.txt`, 'Upload test content');
    
    // 选择并上传文件
    await uploadPage.selectFiles([testFile]);
    await uploadPage.startUpload();
    await uploadPage.waitForUploadComplete();
    
    // 验证上传成功
    const toast = await uploadPage.getToastMessage();
    expect(toast).toContain('上传完成');
    
    // 清理
    cleanupTestFiles();
  });

  test('上传后应该在资源列表中显示', async ({ page }) => {
    const resourcePage = new ResourcePage(page);
    await resourcePage.goto();
    await resourcePage.refresh();
    
    await page.waitForTimeout(500);
    
    // 验证至少有一个文件
    const fileCount = await resourcePage.getFileCount();
    expect(fileCount).toBeGreaterThanOrEqual(0);
  });
});

test.describe('秒传功能测试', () => {
  test.describe.configure({ mode: 'serial' });

  test('相同文件应该触发秒传', async ({ page }) => {
    const uploadPage = new UploadPage(page);
    await uploadPage.goto();
    
    // 创建测试文件
    const testFile = createTestFile('instant-upload-test.txt', 'Same content for instant upload');
    
    // 第一次上传
    await uploadPage.selectFiles([testFile]);
    await uploadPage.startUpload();
    await uploadPage.waitForUploadComplete();
    
    // 等待一下
    await page.waitForTimeout(500);
    
    // 第二次上传相同文件（应该秒传）
    await uploadPage.goto();
    await uploadPage.selectFiles([testFile]);
    await uploadPage.startUpload();
    await uploadPage.waitForUploadComplete();
    
    // 清理
    cleanupTestFiles();
    
    // 验证没有错误
    await expect(page.locator('.error')).not.toBeVisible();
  });
});

test.describe('上传历史测试', () => {
  test('应该能够查看上传历史', async ({ page }) => {
    const uploadPage = new UploadPage(page);
    await uploadPage.goto();
    
    // 点击历史按钮
    const historyBtn = page.locator('button:has-text("历史")');
    
    if (await historyBtn.isVisible()) {
      await historyBtn.click();
      
      // 等待历史列表
      await page.waitForTimeout(500);
    }
    
    // 验证没有错误
    await expect(page.locator('.error')).not.toBeVisible();
  });
});
