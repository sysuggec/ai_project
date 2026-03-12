/**
 * 资源列表页面类
 */

import { BasePage } from './BasePage.js';

export class ResourcePage extends BasePage {
  constructor(page) {
    super(page);
    this.url = '/resources';
    
    this.selectors = {
      resourceTab: 'a:has-text("资源列表")',
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
      moveBtn: '.action-btn[title="移动"]',
      shareBtn: '.action-btn[title="分享"]',
      confirmDialog: '.modal-overlay',
      confirmYesBtn: '.modal-overlay button:has-text("确认")',
      confirmNoBtn: '.modal-overlay button:has-text("取消")',
      renameDialog: '.modal-overlay',
      renameInput: '.modal-overlay .input',
      renameConfirmBtn: '.modal-overlay button:has-text("确认")',
      emptyState: '.file-list .empty',
      loadingState: '.file-list .loading',
      selectAllCheckbox: '.select-all-bar input[type="checkbox"]',
      fileCheckbox: '.file-item input[type="checkbox"]',
      batchOperationBar: '.batch-operation-bar',
      batchDeleteBtn: '.batch-delete-btn',
      batchMoveBtn: '.batch-move-btn',
      moveDialog: '.move-dialog',
      shareDialog: '.share-dialog',
    };
  }

  async goto() {
    await this.navigate('/');
    await this.page.click(this.selectors.resourceTab);
    await this.waitForVisible(this.selectors.fileList);
  }

  async search(keyword) {
    await this.fill(this.selectors.searchInput, keyword);
    await this.click(this.selectors.searchBtn);
    await this.page.waitForTimeout(500);
  }

  async refresh() {
    const refreshBtn = this.page.locator(this.selectors.refreshBtn);
    if (await refreshBtn.isVisible()) {
      await refreshBtn.click();
      await this.page.waitForTimeout(500);
    }
  }

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

  async getFileCount() {
    return await this.page.locator(this.selectors.fileItems).count();
  }

  async hasFile(filename) {
    const files = await this.getFiles();
    return files.some(f => f.name === filename);
  }

  async selectDirectory(path) {
    const treeItems = await this.page.locator(this.selectors.treeItems);
    if (await treeItems.count() > 0) {
      await treeItems.first().click();
    }
  }

  async downloadFile(index = 0) {
    const downloadBtns = this.page.locator(this.selectors.downloadBtn);
    const count = await downloadBtns.count();
    if (count > index) {
      const [download] = await Promise.all([
        this.page.waitForEvent('download'),
        downloadBtns.nth(index).click(),
      ]);
      return download;
    }
    return null;
  }

  async deleteFile(index = 0) {
    await this.page.locator(this.selectors.deleteBtn).nth(index).click();
    await this.waitForVisible(this.selectors.confirmDialog);
    await this.click(this.selectors.confirmYesBtn);
    await this.page.waitForTimeout(500);
  }

  async renameFile(index = 0, newName) {
    await this.page.locator(this.selectors.renameBtn).nth(index).click();
    await this.waitForVisible(this.selectors.renameDialog);
    const input = this.page.locator(this.selectors.renameInput);
    await input.fill('');
    await input.fill(newName);
    await this.click(this.selectors.renameConfirmBtn);
    await this.page.waitForTimeout(500);
  }

  async moveFile(index = 0) {
    await this.page.locator(this.selectors.moveBtn).nth(index).click();
    await this.page.waitForTimeout(300);
  }

  async shareFile(index = 0) {
    await this.page.locator(this.selectors.shareBtn).nth(index).click();
    await this.page.waitForTimeout(300);
  }

  async selectAllFiles() {
    await this.page.locator(this.selectors.selectAllCheckbox).click();
    await this.page.waitForTimeout(300);
  }

  async selectFile(index = 0) {
    await this.page.locator(this.selectors.fileCheckbox).nth(index).click();
    await this.page.waitForTimeout(300);
  }

  async getSelectedCount() {
    const selected = await this.page.locator('.file-item.selected').count();
    return selected;
  }

  async isEmpty() {
    const empty = await this.page.locator(this.selectors.emptyState);
    return await empty.isVisible().catch(() => false);
  }

  async isLoading() {
    const loading = await this.page.locator(this.selectors.loadingState);
    return await loading.isVisible().catch(() => false);
  }
}
