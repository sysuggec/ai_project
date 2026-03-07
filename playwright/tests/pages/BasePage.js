/**
 * 基础页面类
 * 提供通用的页面操作方法
 */

export class BasePage {
  constructor(page) {
    this.page = page;
  }

  /**
   * 导航到指定路径
   * @param {string} path - 路径
   */
  async navigate(path = '/') {
    await this.page.goto(path);
    await this.waitForPageLoad();
  }

  /**
   * 等待页面加载完成
   */
  async waitForPageLoad() {
    await this.page.waitForLoadState('networkidle');
  }

  /**
   * 点击元素
   * @param {string} selector - 选择器
   */
  async click(selector) {
    await this.page.click(selector);
  }

  /**
   * 输入文本
   * @param {string} selector - 选择器
   * @param {string} text - 文本
   */
  async fill(selector, text) {
    await this.page.fill(selector, text);
  }

  /**
   * 获取元素文本
   * @param {string} selector - 选择器
   * @returns {Promise<string>}
   */
  async getText(selector) {
    return await this.page.textContent(selector);
  }

  /**
   * 等待元素可见
   * @param {string} selector - 选择器
   */
  async waitForVisible(selector) {
    await this.page.waitForSelector(selector, { state: 'visible' });
  }

  /**
   * 截图
   * @param {string} name - 截图名称
   */
  async screenshot(name) {
    await this.page.screenshot({ 
      path: `./screenshots/${name}.png`,
      fullPage: true 
    });
  }

  /**
   * 获取 Toast 消息
   * @returns {Promise<string>}
   */
  async getToastMessage() {
    const toast = await this.page.locator('.toast');
    await toast.waitFor({ state: 'visible', timeout: 5000 });
    return await toast.textContent();
  }
}
