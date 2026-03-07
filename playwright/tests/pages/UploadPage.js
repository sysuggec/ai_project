/**
 * 上传页面类
 */

import { BasePage } from './BasePage.js';

export class UploadPage extends BasePage {
  constructor(page) {
    super(page);
    this.url = '/';
    
    // 页面元素选择器
    this.selectors = {
      uploadArea: '.upload-area',
      selectFilesBtn: '.upload-btn:has-text("选择文件")',
      selectFolderBtn: '.upload-btn:has-text("选择文件夹")',
      startUploadBtn: 'button:has-text("开始上传")',
      clearQueueBtn: 'button:has-text("清空队列")',
      uploadQueue: '.upload-queue',
      queueItems: '.queue-item',
      directoryPicker: '.directory-picker',
      resourceTab: 'button:has-text("资源列表")',
      uploadTab: 'button:has-text("上传资源")',
    };
  }

  /**
   * 导航到上传页面
   */
  async goto() {
    await this.navigate(this.url);
    await this.page.click(this.selectors.uploadTab);
    await this.waitForVisible(this.selectors.uploadArea);
  }

  /**
   * 选择文件上传
   * @param {string[]} filePaths - 文件路径数组
   */
  async selectFiles(filePaths) {
    const input = await this.page.locator('input[type="file"][multiple]').first();
    await input.setInputFiles(filePaths);
  }

  /**
   * 拖拽文件到上传区域
   * @param {string[]} filePaths - 文件路径数组
   */
  async dragAndDropFiles(filePaths) {
    const dataTransfer = await this.page.evaluateHandle((files) => {
      const dt = new DataTransfer();
      // 这里简化处理，实际项目中需要更复杂的处理
      return dt;
    }, filePaths);

    await this.page.dispatchEvent(this.selectors.uploadArea, 'drop', { dataTransfer });
  }

  /**
   * 开始上传
   */
  async startUpload() {
    await this.click(this.selectors.startUploadBtn);
  }

  /**
   * 清空上传队列
   */
  async clearQueue() {
    await this.click(this.selectors.clearQueueBtn);
  }

  /**
   * 获取上传队列数量
   * @returns {Promise<number>}
   */
  async getQueueCount() {
    const items = await this.page.locator(this.selectors.queueItems).count();
    return items;
  }

  /**
   * 等待上传完成
   */
  async waitForUploadComplete() {
    // 等待上传按钮变为可用状态（上传完成后 isUploading = false）
    await this.page.waitForFunction(() => {
      const btn = document.querySelector('.btn-primary');
      return btn && !btn.disabled && btn.textContent.includes('开始上传');
    }, { timeout: 30000 });
  }

  /**
   * 切换到资源列表页面
   */
  async switchToResourcePage() {
    await this.click(this.selectors.resourceTab);
    await this.page.waitForSelector('.file-list', { timeout: 5000 });
  }
}
