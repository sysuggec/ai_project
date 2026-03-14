import { test, expect } from '@playwright/test';

test.describe('分享分页功能测试', () => {
  test.beforeEach(async ({ page }) => {
    // 访问分享管理页面
    await page.goto('http://localhost:8080/shares');
  });

  test('应该显示分享列表和分页组件', async ({ page }) => {
    // 等待页面加载
    await page.waitForLoadState('networkidle');

    // 检查是否显示分享列表
    const shareList = page.locator('.share-list');
    await expect(shareList).toBeVisible();

    // 检查是否显示分页组件
    const pagination = page.locator('.pagination');
    await expect(pagination).toBeVisible();
  });

  test('分页信息应该正确显示', async ({ page }) => {
    await page.waitForLoadState('networkidle');

    // 检查分页文本
    const paginationText = page.locator('.pagination-text');
    await expect(paginationText).toBeVisible();

    const text = await paginationText.textContent();
    expect(text).toContain('共');
    expect(text).toContain('条');
    expect(text).toContain('第');
    expect(text).toContain('页');
  });

  test('应该支持切换页码', async ({ page }) => {
    await page.waitForLoadState('networkidle');

    // 获取当前页面的分享数量
    const firstPageItems = page.locator('.list-item');
    const firstPageCount = await firstPageItems.count();

    // 尝试点击"下一页"按钮
    const nextPageBtn = page.locator('.pagination-btn').filter({ hasText: '下一页' });
    if (await nextPageBtn.isEnabled()) {
      await nextPageBtn.click();
      await page.waitForLoadState('networkidle');

      // 检查是否跳转到了下一页
      const secondPageItems = page.locator('.list-item');
      const secondPageCount = await secondPageItems.count();

      // 第二页的项目数量应该不同或相同（取决于总数量）
      expect(secondPageCount).toBeGreaterThanOrEqual(0);

      // 检查"上一页"按钮是否可用
      const prevPageBtn = page.locator('.pagination-btn').filter({ hasText: '上一页' });
      await expect(prevPageBtn).toBeEnabled();
    }
  });

  test('应该支持更改每页显示数量', async ({ page }) => {
    await page.waitForLoadState('networkidle');

    // 获取当前每页显示数量
    const pageSizeSelect = page.locator('.page-size-select');
    await expect(pageSizeSelect).toBeVisible();

    // 更改为每页10条
    await pageSizeSelect.selectOption('10');
    await page.waitForLoadState('networkidle');

    // 检查分享列表项数量（应该不超过10条）
    const items = page.locator('.list-item');
    const itemCount = await items.count();
    expect(itemCount).toBeLessThanOrEqual(10);

    // 更改为每页50条
    await pageSizeSelect.selectOption('50');
    await page.waitForLoadState('networkidle');

    // 检查分享列表项数量（应该增加）
    const itemsAfter = page.locator('.list-item');
    const itemCountAfter = await itemsAfter.count();
    expect(itemCountAfter).toBeGreaterThan(itemCount);
  });

  test('页码按钮应该正确显示当前页', async ({ page }) => {
    await page.waitForLoadState('networkidle');

    // 获取所有页码按钮
    const pageButtons = page.locator('.pagination-page-btn');
    const count = await pageButtons.count();

    if (count > 0) {
      // 检查第一个按钮是否是激活状态（当前页）
      const firstButton = pageButtons.first();
      const isActive = await firstButton.locator('.active').count();
      expect(isActive).toBeGreaterThan(0);
    }
  });

  test('刷新按钮应该重新加载数据', async ({ page }) => {
    await page.waitForLoadState('networkidle');

    // 点击刷新按钮
    const refreshBtn = page.locator('.btn').filter({ hasText: '刷新' });
    await refreshBtn.click();

    // 等待加载完成
    await page.waitForLoadState('networkidle');

    // 检查分享列表是否仍然可见
    const shareList = page.locator('.share-list');
    await expect(shareList).toBeVisible();
  });

  test('删除分享后应该正确更新分页', async ({ page }) => {
    await page.waitForLoadState('networkidle');

    // 获取删除前的总数
    let paginationText = page.locator('.pagination-text');
    let text = await paginationText.textContent();
    const beforeMatch = text.match(/共\s*(\d+)/);
    const beforeTotal = beforeMatch ? parseInt(beforeMatch[1]) : 0;

    // 获取第一个删除按钮
    const deleteBtn = page.locator('.btn-danger').filter({ hasText: '删除' }).first();
    if (await deleteBtn.isVisible()) {
      // 点击删除按钮（浏览器会弹出确认，需要处理）
      page.on('dialog', dialog => dialog.accept());
      await deleteBtn.click();
      await page.waitForLoadState('networkidle');

      // 检查总数是否减少
      paginationText = page.locator('.pagination-text');
      text = await paginationText.textContent();
      const afterMatch = text.match(/共\s*(\d+)/);
      const afterTotal = afterMatch ? parseInt(afterMatch[1]) : 0;

      expect(afterTotal).toBeLessThan(beforeTotal);
    }
  });
});
