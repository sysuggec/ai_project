/**
 * 回收站页面类
 */

import { BasePage } from './BasePage.js';

export class TrashPage extends BasePage {
  constructor(page) {
    super(page);
    this.url = '/trash';
    
    this.selectors = {
      trashTab: 'a:has-text("回收站")',
      trashList: '.trash-list',
      listItems: '.list-item',
      restoreBtn: '.btn-primary:has-text("恢复")',
      deleteBtn: '.btn-danger:has-text("删除")',
      clearTrashBtn: '.btn-danger:has-text("清空回收站")',
      confirmDialog: '.modal-overlay',
      confirmYesBtn: '.modal-overlay button:has-text("确认")',
      confirmNoBtn: '.modal-overlay button:has-text("取消")',
      emptyState: '.trash-page .empty',
      loadingState: '.trash-page .loading',
    };
  }

  async goto() {
    await this.navigate('/');
    await this.page.click(this.selectors.trashTab);
    await this.page.waitForSelector('.trash-page', { timeout: 5000 });
  }

  async getTrashCount() {
    return await this.page.locator(this.selectors.listItems).count();
  }

  async restoreFile(index = 0) {
    await this.page.locator(this.selectors.restoreBtn).nth(index).click();
    await this.page.waitForTimeout(500);
  }

  async deletePermanently(index = 0) {
    await this.page.locator(this.selectors.deleteBtn).nth(index).click();
    await this.page.waitForTimeout(500);
  }

  async clearTrash() {
    await this.click(this.selectors.clearTrashBtn);
    await this.waitForVisible(this.selectors.confirmDialog);
    await this.click(this.selectors.confirmYesBtn);
    await this.page.waitForTimeout(500);
  }

  async isEmpty() {
    const empty = await this.page.locator(this.selectors.emptyState);
    return await empty.isVisible().catch(() => false);
  }
}
