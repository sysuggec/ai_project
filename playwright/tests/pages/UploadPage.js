/**
 * 上传页面类
 */

import { BasePage } from './BasePage.js';

export class UploadPage extends BasePage {
  constructor(page) {
    super(page);
    this.url = '/';
    
    this.selectors = {
      uploadArea: '.upload-area',
      selectFilesBtn: 'input[type="file"][multiple]',
      startUploadBtn: 'button:has-text("开始上传")',
      clearQueueBtn: 'button:has-text("清空队列")',
      uploadQueue: '.upload-queue',
      queueItems: '.queue-item',
      directoryPicker: '.directory-picker',
      resourceTab: 'a:has-text("资源列表")',
      uploadTab: 'a:has-text("上传资源")',
    };
  }

  async goto() {
    await this.navigate(this.url);
    await this.page.click(this.selectors.uploadTab);
    await this.waitForVisible(this.selectors.uploadArea);
  }

  async selectFiles(filePaths) {
    const input = await this.page.locator('input[type="file"][multiple]').first();
    await input.setInputFiles(filePaths);
  }

  async startUpload() {
    await this.click(this.selectors.startUploadBtn);
  }

  async clearQueue() {
    const clearBtn = this.page.locator(this.selectors.clearQueueBtn);
    if (await clearBtn.isVisible()) {
      await clearBtn.click();
      await this.page.waitForTimeout(300);
    }
  }

  async getQueueCount() {
    const items = await this.page.locator(this.selectors.queueItems).count();
    return items;
  }

  async waitForUploadComplete() {
    await this.page.waitForFunction(() => {
      const btn = document.querySelector('button.btn-primary');
      return btn && !btn.disabled && btn.textContent.includes('开始上传');
    }, { timeout: 30000 });
  }

  async switchToResourcePage() {
    await this.page.click(this.selectors.resourceTab);
    await this.page.waitForSelector('.file-list', { timeout: 5000 });
  }
}
