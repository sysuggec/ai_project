---
description: 分析本地git历史提取编码模式并生成SKILL.md文件。Skill Creator GitHub App的本地版本
---

# 技能创建命令

分析仓库的git历史提取编码模式并生成SKILL.md文件，教Claude您团队的实践。

## 用法

```bash
/skill-create                    # 分析当前仓库
/skill-create --commits 100      # 分析最近100个提交
/skill-create --output ./skills  # 自定义输出目录
/skill-create --instincts        # 同时为continuous-learning-v2生成instincts
```

## 功能

1. **解析Git历史** - 分析提交、文件变更和模式
2. **检测模式** - 识别重复的工作流和约定
3. **生成SKILL.md** - 创建有效的Claude Code技能文件
4. **可选创建Instincts** - 为continuous-learning-v2系统

## 分析步骤

### 步骤1：收集Git数据

```bash
# 获取最近提交及文件变更
git log --oneline -n ${COMMITS:-200} --name-only --pretty=format:"%H|%s|%ad" --date=short

# 获取文件提交频率
git log --oneline -n 200 --name-only | grep -v "^$" | grep -v "^[a-f0-9]" | sort | uniq -c | sort -rn | head -20

# 获取提交消息模式
git log --oneline -n 200 | cut -d' ' -f2- | head -50
```

### 步骤2：检测模式

寻找这些模式类型：

| 模式 | 检测方法 |
|------|----------|
| **提交约定** | 提交消息正则（feat:、fix:、chore:） |
| **文件共变** | 总是一起变更的文件 |
| **工作流序列** | 重复的文件变更模式 |
| **架构** | 文件夹结构和命名约定 |
| **测试模式** | 测试文件位置、命名、覆盖率 |

### 步骤3：生成SKILL.md

输出格式：

```markdown
---
name: {仓库名}-patterns
description: 从{仓库名}提取的编码模式
version: 1.0.0
source: local-git-analysis
analyzed_commits: {数量}
---

# {仓库名}模式

## 提交约定
{检测到的提交消息模式}

## 代码架构
{检测到的文件夹结构和组织}

## 工作流
{检测到的重复文件变更模式}

## 测试模式
{检测到的测试约定}
```

## 示例输出

在TypeScript项目上运行 `/skill-create` 可能产生：

```markdown
---
name: my-app-patterns
description: my-app仓库的编码模式
version: 1.0.0
source: local-git-analysis
analyzed_commits: 150
---

# My App模式

## 提交约定

此项目使用**约定式提交**：
- `feat:` - 新功能
- `fix:` - Bug修复
- `chore:` - 维护任务
- `docs:` - 文档更新

## 代码架构

```
src/
├── components/     # React组件（PascalCase.tsx）
├── hooks/          # 自定义hooks（use*.ts）
├── utils/          # 工具函数
├── types/          # TypeScript类型定义
└── services/       # API和外部服务
```

## 工作流

### 添加新组件
1. 创建 `src/components/ComponentName.tsx`
2. 在 `src/components/__tests__/ComponentName.test.tsx` 添加测试
3. 从 `src/components/index.ts` 导出

### 数据库迁移
1. 修改 `src/db/schema.ts`
2. 运行 `pnpm db:generate`
3. 运行 `pnpm db:migrate`

## 测试模式

- 测试文件：`__tests__/` 目录或 `.test.ts` 后缀
- 覆盖率目标：80%+
- 框架：Vitest
```

## 相关命令

- `/instinct-import` - 导入生成的instincts
- `/instinct-status` - 查看学习的instincts
- `/evolve` - 将instincts聚类为技能/agents
