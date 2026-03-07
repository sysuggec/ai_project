# 资源管理系统 E2E 测试

基于 Playwright 的端到端测试套件。

## 目录结构

```
playwright/
├── tests/
│   ├── specs/          # 测试用例
│   │   ├── smoke.spec.js      # 冒烟测试
│   │   ├── upload.spec.js     # 上传功能测试
│   │   └── resource.spec.js   # 资源管理测试
│   ├── pages/          # 页面对象模型
│   │   ├── BasePage.js
│   │   ├── UploadPage.js
│   │   └── ResourcePage.js
│   └── fixtures/       # 测试数据和工具
│       └── test-data.js
├── screenshots/        # 测试截图
├── reports/            # 测试报告
│   └── html-report/    # HTML 测试报告
├── test-results/       # 测试结果（视频、轨迹等）
├── playwright.config.js # Playwright 配置
└── package.json
```

## 安装

```bash
cd /workspace/playwright
npm install
npx playwright install
```

## 运行测试

```bash
# 运行所有测试
npm test

# 以 headed 模式运行（显示浏览器）
npm run test:headed

# 调试模式
npm run test:debug

# UI 模式
npm run test:ui

# 查看报告
npm run report
```

## 测试覆盖

### 冒烟测试 (smoke.spec.js)
- 应用加载验证
- 标签页切换
- API 响应检查
- 页面加载性能
- 响应式布局

### 上传功能测试 (upload.spec.js)
- 页面加载验证
- 文件选择功能
- 多文件上传
- 上传队列管理
- 文件上传成功验证
- 多种格式支持

### 资源管理测试 (resource.spec.js)
- 文件列表显示
- 搜索功能
- 文件下载
- 文件删除
- 文件重命名
- 目录树导航

## 配置说明

### 浏览器支持
- Chromium (Chrome)
- Firefox
- WebKit (Safari)
- Mobile Chrome

### 报告输出
- HTML 报告: `reports/html-report/`
- JUnit XML: `reports/junit-report.xml`
- 截图: `screenshots/`
- 视频和轨迹: `test-results/`

## 环境要求

- Node.js 18+
- 后端服务运行在 http://localhost:8080
