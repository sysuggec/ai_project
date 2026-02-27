---
description: 自动分析项目并生成PM2服务命令
---

# PM2初始化

自动分析项目并生成PM2服务命令。

## 工作流

1. 检查PM2（如缺少通过 `npm install -g pm2` 安装）
2. 扫描项目识别服务（前端/后端/数据库）
3. 生成配置文件和单独命令文件

## 服务检测

| 类型 | 检测方式 | 默认端口 |
|------|----------|----------|
| Vite | vite.config.* | 5173 |
| Next.js | next.config.* | 3000 |
| Nuxt | nuxt.config.* | 3000 |
| CRA | package.json中react-scripts | 3000 |
| Express/Node | server/backend/api目录 + package.json | 3000 |
| FastAPI/Flask | requirements.txt / pyproject.toml | 8000 |
| Go | go.mod / main.go | 8080 |

**端口检测优先级**：用户指定 > .env > 配置文件 > 脚本参数 > 默认端口

## 生成的文件

```
project/
├── ecosystem.config.cjs              # PM2配置
├── {backend}/start.cjs               # Python包装器（如适用）
└── .claude/
    ├── commands/
    │   ├── pm2-all.md                # 启动全部 + 监控
    │   ├── pm2-all-stop.md           # 停止全部
    │   ├── pm2-all-restart.md        # 重启全部
    │   ├── pm2-{port}.md             # 启动单个 + 日志
    │   ├── pm2-{port}-stop.md        # 停止单个
    │   ├── pm2-{port}-restart.md     # 重启单个
    │   ├── pm2-logs.md               # 查看所有日志
    │   └── pm2-status.md             # 查看状态
    └── scripts/
        ├── pm2-logs-{port}.ps1       # 单服务日志
        └── pm2-monit.ps1             # PM2监控
```

## Windows配置（重要）

### ecosystem.config.cjs

**必须使用 `.cjs` 扩展名**

```javascript
module.exports = {
  apps: [
    // Node.js (Vite/Next/Nuxt)
    {
      name: 'project-3000',
      cwd: './packages/web',
      script: 'node_modules/vite/bin/vite.js',
      args: '--port 3000',
      interpreter: 'C:/Program Files/nodejs/node.exe',
      env: { NODE_ENV: 'development' }
    },
    // Python
    {
      name: 'project-8000',
      cwd: './backend',
      script: 'start.cjs',
      interpreter: 'C:/Program Files/nodejs/node.exe',
      env: { PYTHONUNBUFFERED: '1' }
    }
  ]
}
```

**框架脚本路径：**

| 框架 | script | args |
|------|--------|------|
| Vite | `node_modules/vite/bin/vite.js` | `--port {port}` |
| Next.js | `node_modules/next/dist/bin/next` | `dev -p {port}` |
| Nuxt | `node_modules/nuxt/bin/nuxt.mjs` | `dev --port {port}` |
| Express | `src/index.js` 或 `server.js` | - |

### Python包装脚本（start.cjs）

```javascript
const { spawn } = require('child_process');
const proc = spawn('python', ['-m', 'uvicorn', 'app.main:app', '--host', '0.0.0.0', '--port', '8000', '--reload'], {
  cwd: __dirname, stdio: 'inherit', windowsHide: true
});
proc.on('close', (code) => process.exit(code));
```

## 关键规则

1. **配置文件**：`ecosystem.config.cjs`（不是.js）
2. **Node.js**：直接指定bin路径 + interpreter
3. **Python**：Node.js包装脚本 + `windowsHide: true`
4. **打开新窗口**：`start wt.exe -d "{path}" pwsh -NoExit -c "command"`
5. **最小内容**：每个命令文件只有1-2行描述 + bash块
6. **直接执行**：无需AI解析，只需运行bash命令

## 执行

根据 `$ARGUMENTS`，执行初始化：

1. 扫描项目服务
2. 生成 `ecosystem.config.cjs`
3. 为Python服务生成 `{backend}/start.cjs`（如适用）
4. 在 `.claude/commands/` 生成命令文件
5. 在 `.claude/scripts/` 生成脚本文件
6. **更新项目CLAUDE.md**添加PM2信息
7. **显示完成摘要**及终端命令

## 初始化后：显示摘要

```
## PM2初始化完成

**服务：**

| 端口 | 名称 | 类型 |
|------|------|------|
| {port} | {name} | {type} |

**Claude命令：** /pm2-all, /pm2-all-stop, /pm2-{port}, /pm2-{port}-stop, /pm2-logs, /pm2-status

**终端命令：**
## 首次（使用配置文件）
pm2 start ecosystem.config.cjs && pm2 save

## 之后（简化）
pm2 start all          # 启动全部
pm2 stop all           # 停止全部
pm2 restart all        # 重启全部
pm2 start {name}       # 启动单个
pm2 stop {name}        # 停止单个
pm2 logs               # 查看日志
pm2 monit              # 监控面板
pm2 resurrect          # 恢复保存的进程

**提示：** 首次启动后运行 `pm2 save` 以启用简化命令。
```
