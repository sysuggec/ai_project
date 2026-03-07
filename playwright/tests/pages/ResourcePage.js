/**
 * 资源列表页面类
 */

import { BasePage } from './BasePage.js';

export class ResourcePage extends BasePage {
  constructor(page) {
    super(page);
    this.url = '/';
    
    // 页面元素选择器
    this.selectors = {
      resourceTab: 'button:has-text("资源列表")',
      searchInput: '.search-bar input',
      searchBtn: '.search-bar button',
      refreshBtn: 'button:has-text("刷新")',
      fileList: '.file-list',
      fileItems: '.file-item',
      fileName: '.file-name',
      directoryTree: '.directory-tree',
      treeItems: '.tree-item',
      downloadBtn: '.action-btn[title="下载"]',
      renameBtn: '.action-btn[title="重命名"]',
      deleteBtn: '.action-btn[title="删除"]',
      confirmDialog: '.confirm-dialog',
      confirmYesBtn: '.confirm-dialog button:has-text("确定")',
      confirmNoBtn: '.confirm-dialog button:has-text("取消")',
      renameDialog: '.rename-dialog',
      renameInput: '.rename-dialog input',
      renameConfirmBtn: '.rename-dialog button:has-text("确定")',
      emptyState: '.file-list .empty',
      loadingState: '.file-list .loading',
    };
  }

  /**
   * 导航到资源列表页面
   */
  async goto() {
    await this.navigate(this.url);
    await this.page.click(this.selectors.resourceTab);
    await this.waitForVisible(this.selectors.fileList);
  }

  /**
   * 搜索文件
   * @param {string} keyword - 搜索关键词
   */
  async search(keyword) {
    await this.fill(this.selectors.searchInput, keyword);
    await this.click(this.selectors.searchBtn);
    await this.page.waitForTimeout(500); // 等待搜索结果
  }

  /**
   * 刷新文件列表
   */
  async refresh() {
    await this.click(this.selectors.refreshBtn);
    await this.page.waitForTimeout(500);
  }

  /**
   * 获取文件列表
   * @returns {Promise<Array<{name: string, size: string}>>}
   */
  async getFiles() {
    const files = [];
    const items = await this.page.locator(this.selectors.fileItems).all();
    
    for (const item of items) {
      const name = await item.locator(this.selectors.fileName).textContent();
      const meta = await item.locator('.file-meta').textContent();
      files.push({ name: name?.trim(), meta: meta?.trim() });
    }
    
    return files;
  }

  /**
   * 获取文件数量
   * @returns {Promise<number>}
   */
  async getFileCount() {
    return await this.page.locator(this.selectors.fileItems).count();
  }

  /**
   * 检查文件是否存在
   * @param {string} filename - 文件名
   * @returns {Promise<boolean>}
   */
  async hasFile(filename) {
    const files = await this.getFiles();
    return files.some(f => f.name === filename);
  }

  /**
   * 选择目录
   * @param {string} path - 目录路径
   */
  async selectDirectory(path) {
    // 简化处理，点击第一个目录
    const treeItems = await this.page.locator(this.selectors.treeItems);
    if (await treeItems.count() > 0) {
      await treeItems.first().click();
    }
  }

  /**
   * 下载文件
   * @param {number} index - 文件索引
   */
  async downloadFile(index = 0) {
    const [download] = await Promise.all([
      this.page.waitForEvent('download'),
      this.page.locator(this.selectors.downloadBtn).nth(index).click(),
    ]);
    return download;
  }

  /**
   * 删除文件
   * @param {number} index - 文件索引
   */
  async deleteFile(index = 0) {
    await this.page.locator(this.selectors.deleteBtn).nth(index).click();
    
    // 等待确认对话框并确认
    await this.waitForVisible(this.selectors.confirmDialog);
    await this.click(this.selectors.confirmYesBtn);
    
    // 等待操作完成
    await this.page.waitForTimeout(500);
  }

  /**
   * 重命名文件
   * @param {number} index - 文件索引
   * @param {string} newName - 新文件名
   */
  async renameFile(index = 0, newName) {
    await this.page.locator(this.selectors.renameBtn).nth(index).click();
    
    // 等待重命名对话框
    await this.waitForVisible(this.selectors.renameDialog);
    
    // 清空输入框并输入新名称
    const input = this.page.locator(this.selectors.renameInput);
    await input.fill('');
    await input.fill(newName);
    
    // 确认重命名
    await this.click(this.selectors.renameConfirmBtn);
    
    // 等待操作完成
    await this.page.waitForTimeout(500);
  }

  /**
   * 检查是否显示空状态
   * @returns {Promise<boolean>}
   */
  async isEmpty() {
    const empty = await this.page.locator(this.selectors.emptyState);
    return await empty.isVisible().catch(() => false);
  }

  /**
   * 检查是否正在加载
   * @returns {Promise<boolean>}
   */
  async isLoading() {
    const loading = await this.page.locator(this.selectors.loadingState);
    return await loading.isVisible().catch(() => false);
  }
}
