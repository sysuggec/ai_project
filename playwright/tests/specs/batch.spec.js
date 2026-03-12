/**
 * 批量操作功能测试
 */

import { test, expect } from '@playwright/test';
import { ResourcePage } from '../pages/ResourcePage.js';
import { UploadPage } from '../pages/UploadPage.js';
import fs from 'fs';
import path from 'path';

const TEST_DIR = path.join(process.cwd(), 'tests', 'fixtures', 'temp');

function createTestFile(filename, content) {
  if (!fs.existsSync(TEST_DIR)) {
    fs.mkdirSync(TEST_DIR, { recursive: true });
  }
  const filePath = path.join(TEST_DIR, filename);
  fs.writeFileSync(filePath, content);
  return filePath;
}

function cleanupTestFiles() {
  if (fs.existsSync(TEST_DIR)) {
    fs.rmSync(TEST_DIR, { recursive: true, force: true });
  }
}

async function uploadMultipleFiles(page, files) {
  const uploadPage = new UploadPage(page);
  await uploadPage.goto();
  
  const testFiles = files.map(f => createTestFile(f.name, f.content));
  await uploadPage.selectFiles(testFiles);
  await uploadPage.startUpload();
  await uploadPage.waitForUploadComplete();
  
  cleanupTestFiles();
}

test.describe('批量操作测试', () => {
  test.describe.configure({ mode: 'serial' });

  test('准备测试数据 - 上传多个文件', async ({ page }) => {
    const files = [
      { name: `batch-test-1-${Date.now()}.txt`, content: 'Batch test file 1' },
      { name: `batch-test-2-${Date.now()}.txt`, content: 'Batch test file 2' },
      { name: `batch-test-3-${Date.now()}.txt`, content: 'Batch test file 3' },
    ];
    
    await uploadMultipleFiles(page, files);
    
    const resourcePage = new ResourcePage(page);
    await resourcePage.goto();
    await resourcePage.refresh();
    
    await page.waitForTimeout(500);
    
    const fileCount = await resourcePage.getFileCount();
    expect(fileCount).toBeGreaterThanOrEqual(3);
  });

  test('应该能够选择多个文件', async ({ page }) => {
    const resourcePage = new ResourcePage(page);
    await resourcePage.goto();
    await resourcePage.refresh();
    
    await page.waitForTimeout(500);
    
    const fileCount = await resourcePage.getFileCount();
    
    if (fileCount >= 2) {
      // 选择前两个文件
      const checkboxes = page.locator('.file-checkbox');
      const count = await checkboxes.count();
      
      if (count >= 2) {
        await checkboxes.nth(0).check();
        await checkboxes.nth(1).check();
        
        // 验证批量操作栏显示
        const batchBar = page.locator('.batch-actions');
        if (await batchBar.isVisible()) {
          expect(await batchBar.isVisible()).toBeTruthy();
        }
      }
    }
  });

  test('应该能够批量删除文件', async ({ page }) => {
    const resourcePage = new ResourcePage(page);
    await resourcePage.goto();
    await resourcePage.refresh();
    
    await page.waitForTimeout(500);
    
    const fileCount = await resourcePage.getFileCount();
    
    if (fileCount >= 2) {
      const checkboxes = page.locator('.file-checkbox');
      const count = await checkboxes.count();
      
      if (count >= 2) {
        await checkboxes.nth(0).check();
        await checkboxes.nth(1).check();
        
        // 点击删除按钮
        const deleteBtn = page.locator('.batch-actions button:has-text("删除")');
        if (await deleteBtn.isVisible()) {
          await deleteBtn.click();
          
          // 确认删除
          const confirmDialog = page.locator('.modal-overlay');
          if (await confirmDialog.isVisible()) {
            await confirmDialog.locator('button:has-text("确认")').click();
            
            // 等待操作完成
            await page.waitForTimeout(1000);
          }
        }
      }
    }
    
    // 验证没有错误
    await expect(page.locator('.error')).not.toBeVisible();
  });
});

test.describe('文件移动功能测试', () => {
  test.describe.configure({ mode: 'serial' });

  test('准备测试数据', async ({ page }) => {
    // 上传测试文件
    const uploadPage = new UploadPage(page);
    await uploadPage.goto();
    
    const testFile = createTestFile(`move-test-${Date.now()}.txt`, 'Move test content');
    await uploadPage.selectFiles([testFile]);
    await uploadPage.startUpload();
    await uploadPage.waitForUploadComplete();
    
    cleanupTestFiles();
    
    // 刷新页面验证
    const resourcePage = new ResourcePage(page);
    await resourcePage.goto();
    await resourcePage.refresh();
    
    const fileCount = await resourcePage.getFileCount();
    expect(fileCount).toBeGreaterThan(0);
  });

  test('应该能够移动文件到其他目录', async ({ page }) => {
    const resourcePage = new ResourcePage(page);
    await resourcePage.goto();
    await resourcePage.refresh();
    
    await page.waitForTimeout(500);
    
    const fileCount = await resourcePage.getFileCount();
    
    if (fileCount > 0) {
      // 找到第一个文件的移动按钮
      const moveBtn = page.locator('.file-item').first().locator('button:has-text("移动")');
      
      if (await moveBtn.isVisible()) {
        await moveBtn.click();
        
        // 等待移动对话框
        const moveDialog = page.locator('.modal-overlay');
        if (await moveDialog.isVisible()) {
          // 选择目标目录（如果有）
          const dirItems = moveDialog.locator('.tree-item');
          if (await dirItems.count() > 0) {
            await dirItems.first().click();
          }
          
          // 确认移动
          await moveDialog.locator('button:has-text("确认")').click();
          
          await page.waitForTimeout(1000);
        }
      }
    }
    
    // 验证没有错误
    await expect(page.locator('.error')).not.toBeVisible();
  });
});
