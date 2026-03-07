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
test.afterEach(() => {
  if (fs.existsSync(TEST_DIR)) {
    fs.rmSync(TEST_DIR, { recursive: true, force: true });
  }
});

test.describe('上传功能测试', () => {
  test('页面应该正确加载', async ({ page }) => {
    const uploadPage = new UploadPage(page);
    await uploadPage.goto();
    
    // 验证页面标题
    await expect(page.locator('h1')).toHaveText('资源管理系统');
    
    // 验证上传区域可见
    await expect(page.locator('.upload-area')).toBeVisible();
    
    // 验证上传按钮存在
    await expect(page.locator('.upload-btn')).toHaveCount(2);
  });

  test('应该能够选择文件到上传队列', async ({ page }) => {
    const uploadPage = new UploadPage(page);
    await uploadPage.goto();
    
    // 创建测试文件
    const testFile = createTestFile('test1.txt', 'Hello, Playwright!');
    
    // 选择文件
    await uploadPage.selectFiles([testFile]);
    
    // 验证文件已添加到队列
    const queueCount = await uploadPage.getQueueCount();
    expect(queueCount).toBe(1);
  });

  test('应该能够选择多个文件', async ({ page }) => {
    const uploadPage = new UploadPage(page);
    await uploadPage.goto();
    
    // 创建多个测试文件
    const testFiles = [
      createTestFile('file1.txt', 'Content 1'),
      createTestFile('file2.txt', 'Content 2'),
      createTestFile('file3.txt', 'Content 3'),
    ];
    
    // 选择多个文件
    await uploadPage.selectFiles(testFiles);
    
    // 验证所有文件已添加到队列
    const queueCount = await uploadPage.getQueueCount();
    expect(queueCount).toBe(3);
  });

  test('应该能够清空上传队列', async ({ page }) => {
    const uploadPage = new UploadPage(page);
    await uploadPage.goto();
    
    // 创建测试文件
    const testFile = createTestFile('test.txt', 'Test content');
    
    // 选择文件
    await uploadPage.selectFiles([testFile]);
    
    // 验证文件已添加
    expect(await uploadPage.getQueueCount()).toBe(1);
    
    // 清空队列
    await uploadPage.clearQueue();
    
    // 验证队列为空
    expect(await uploadPage.getQueueCount()).toBe(0);
  });

  test('应该能够上传文件并在资源列表中显示', async ({ page }) => {
    const uploadPage = new UploadPage(page);
    const resourcePage = new ResourcePage(page);
    
    await uploadPage.goto();
    
    // 创建测试文件
    const testFile = createTestFile('upload-test.txt', 'Test upload content');
    
    // 选择并上传文件
    await uploadPage.selectFiles([testFile]);
    await uploadPage.startUpload();
    
    // 等待上传完成
    await uploadPage.waitForUploadComplete();
    
    // 切换到资源列表页面
    await uploadPage.switchToResourcePage();
    
    // 验证文件出现在列表中
    await resourcePage.refresh();
    const hasFile = await resourcePage.hasFile('upload-test.txt');
    expect(hasFile).toBe(true);
  });

  test('应该支持不同格式的文件上传', async ({ page }) => {
    const uploadPage = new UploadPage(page);
    await uploadPage.goto();
    
    // 创建不同格式的测试文件
    const files = [
      createTestFile('document.txt', 'Text content'),
      createTestFile('data.json', '{"key": "value"}'),
      createTestFile('config.ini', 'setting=value'),
    ];
    
    // 选择所有文件
    await uploadPage.selectFiles(files);
    
    // 验证所有文件都已添加到队列
    const queueCount = await uploadPage.getQueueCount();
    expect(queueCount).toBe(3);
  });
});

test.describe('上传页面 UI 测试', () => {
  test('上传区域应该有正确的样式和提示', async ({ page }) => {
    await page.goto('/');
    await page.click('button:has-text("上传资源")');
    
    const uploadArea = page.locator('.upload-area');
    
    // 验证上传区域包含正确的文本
    await expect(uploadArea).toContainText('拖拽文件到此处上传');
    await expect(uploadArea).toContainText('或点击选择文件');
    await expect(uploadArea).toContainText('选择文件');
    await expect(uploadArea).toContainText('选择文件夹');
  });

  test('目录选择器应该可见', async ({ page }) => {
    const uploadPage = new UploadPage(page);
    await uploadPage.goto();
    
    // 验证目录选择器存在
    await expect(page.locator('.directory-picker')).toBeVisible();
  });

  test('上传历史区域应该可见', async ({ page }) => {
    const uploadPage = new UploadPage(page);
    await uploadPage.goto();
    
    // 验证上传历史区域存在
    await expect(page.locator('.history-section')).toBeVisible();
  });
});
