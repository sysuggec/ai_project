/**
 * 分享管理页面类
 */

import { BasePage } from './BasePage.js';

export class SharePage extends BasePage {
  constructor(page) {
    super(page);
    this.url = '/shares';
    
    this.selectors = {
      shareTab: 'a:has-text("分享管理")',
      shareList: '.share-list',
      listItems: '.list-item',
      copyLinkBtn: '.btn-secondary:has-text("复制链接")',
      deleteBtn: '.btn-danger:has-text("删除")',
      refreshBtn: '.btn-secondary:has-text("刷新")',
      emptyState: '.share-page .empty',
      loadingState: '.share-page .loading',
    };
  }

  async goto() {
    await this.navigate('/');
    await this.page.click(this.selectors.shareTab);
    await this.page.waitForSelector('.share-page', { timeout: 5000 });
  }

  async getShareCount() {
    return await this.page.locator(this.selectors.listItems).count();
  }

  async copyShareLink(index = 0) {
    await this.page.locator(this.selectors.copyLinkBtn).nth(index).click();
    await this.page.waitForTimeout(500);
  }

  async deleteShare(index = 0) {
    await this.page.locator(this.selectors.deleteBtn).nth(index).click();
    await this.page.waitForTimeout(500);
  }

  async refresh() {
    await this.click(this.selectors.refreshBtn);
    await this.page.waitForTimeout(500);
  }

  async isEmpty() {
    const empty = await this.page.locator(this.selectors.emptyState);
    return await empty.isVisible().catch(() => false);
  }
}
