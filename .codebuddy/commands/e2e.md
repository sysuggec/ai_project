---
description: 使用Playwright生成和运行端到端测试。创建测试旅程、运行测试、捕获截图/视频/追踪并上传工件
---

# E2E命令

此命令调用 **e2e-runner** agent 使用Playwright生成、维护和执行端到端测试。

## 命令功能

1. **生成测试旅程** - 为用户流程创建Playwright测试
2. **运行E2E测试** - 跨浏览器执行测试
3. **捕获工件** - 失败时的截图、视频、追踪
4. **上传结果** - HTML报告和JUnit XML
5. **识别不稳定测试** - 隔离不稳定的测试

## 使用时机

在以下情况使用 `/e2e`：
- 测试关键用户旅程（登录、交易、支付）
- 验证多步骤流程端到端工作
- 测试UI交互和导航
- 验证前端和后端的集成
- 准备生产部署

## 工作原理

e2e-runner agent 将：

1. **分析用户流程**并识别测试场景
2. **生成Playwright测试**使用Page Object Model模式
3. **跨多浏览器运行测试**（Chrome、Firefox、Safari）
4. **失败时捕获**截图、视频和追踪
5. **生成报告**包含结果和工件
6. **识别不稳定测试**并建议修复

## 测试工件

测试运行时，捕获以下工件：

**所有测试：**
- 带时间线和结果的HTML报告
- 用于CI集成的JUnit XML

**仅失败时：**
- 失败状态的截图
- 测试的视频录制
- 用于调试的追踪文件（逐步回放）
- 网络日志
- 控制台日志

## 查看工件

```bash
# 在浏览器中查看HTML报告
npx playwright show-report

# 查看特定追踪文件
npx playwright show-trace artifacts/trace-abc123.zip

# 截图保存在artifacts/目录
open artifacts/search-results.png
```

## 浏览器配置

默认在多浏览器上运行测试：
- ✅ Chromium（桌面Chrome）
- ✅ Firefox（桌面）
- ✅ WebKit（桌面Safari）
- ✅ Mobile Chrome（可选）

在 `playwright.config.ts` 中配置浏览器。

## CI/CD集成

添加到CI流水线：

```yaml
# .github/workflows/e2e.yml
- name: Install Playwright
  run: npx playwright install --with-deps

- name: Run E2E tests
  run: npx playwright test

- name: Upload artifacts
  if: always()
  uses: actions/upload-artifact@v3
  with:
    name: playwright-report
    path: playwright-report/
```

## 最佳实践

**应该：**
- ✅ 使用Page Object Model提高可维护性
- ✅ 使用data-testid属性作为选择器
- ✅ 等待API响应，而非任意超时
- ✅ 端到端测试关键用户旅程
- ✅ 合并到main前运行测试
- ✅ 测试失败时审查工件

**不应该：**
- ❌ 使用脆弱的选择器（CSS类可能变化）
- ❌ 测试实现细节
- ❌ 对生产环境运行测试
- ❌ 忽略不稳定的测试
- ❌ 失败时跳过工件审查
- ❌ 用E2E测试每个边缘情况（使用单元测试）

## 重要说明

**关键：**
- 涉及真实资金的E2E测试必须在testnet/staging上运行
- 永远不要对生产环境运行交易测试
- 对金融测试设置 `test.skip(process.env.NODE_ENV === 'production')`
- 仅使用带有小额测试资金的测试钱包

## 与其他命令的集成

- 使用 `/plan` 识别要测试的关键旅程
- 使用 `/tdd` 进行单元测试（更快、更细粒度）
- 使用 `/e2e` 进行集成和用户旅程测试
- 使用 `/code-review` 验证测试质量

## 快速命令

```bash
# 运行所有E2E测试
npx playwright test

# 运行特定测试文件
npx playwright test tests/e2e/markets/search.spec.ts

# 以有头模式运行（看到浏览器）
npx playwright test --headed

# 调试测试
npx playwright test --debug

# 生成测试代码
npx playwright codegen http://localhost:3000

# 查看报告
npx playwright show-report
```
