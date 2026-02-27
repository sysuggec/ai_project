---
description: 分析测试覆盖率，识别差距，并生成缺失的测试以达到80%以上覆盖率
---

# 测试覆盖率

分析测试覆盖率，识别差距，并生成缺失的测试以达到80%以上覆盖率。

## 步骤1：检测测试框架

| 指示器 | 覆盖率命令 |
|--------|-----------|
| `jest.config.*` 或 `package.json` jest | `npx jest --coverage --coverageReporters=json-summary` |
| `vitest.config.*` | `npx vitest run --coverage` |
| `pytest.ini` / `pyproject.toml` pytest | `pytest --cov=src --cov-report=json` |
| `Cargo.toml` | `cargo llvm-cov --json` |
| `pom.xml` with JaCoCo | `mvn test jacoco:report` |
| `go.mod` | `go test -coverprofile=coverage.out ./...` |

## 步骤2：分析覆盖率报告

1. 运行覆盖率命令
2. 解析输出（JSON摘要或终端输出）
3. 列出**低于80%覆盖率**的文件，按最差优先排序
4. 对每个覆盖不足的文件，识别：
   - 未测试的函数或方法
   - 缺失的分支覆盖（if/else、switch、错误路径）
   - 膨胀分母的死代码

## 步骤3：生成缺失的测试

对每个覆盖不足的文件，按以下优先级生成测试：

1. **正常路径** — 有效输入的核心功能
2. **错误处理** — 无效输入、缺失数据、网络失败
3. **边缘情况** — 空数组、null/undefined、边界值（0、-1、MAX_INT）
4. **分支覆盖** — 每个if/else、switch case、三元表达式

### 测试生成规则

- 测试放在源文件旁边：`foo.ts` → `foo.test.ts`（或项目约定）
- 使用项目现有的测试模式（导入风格、断言库、mock方法）
- Mock外部依赖（数据库、API、文件系统）
- 每个测试应独立 — 测试之间无共享可变状态
- 描述性命名测试：`test_create_user_with_duplicate_email_returns_409`

## 步骤4：验证

1. 运行完整测试套件 — 所有测试必须通过
2. 重新运行覆盖率 — 验证改进
3. 如仍低于80%，对剩余差距重复步骤3

## 步骤5：报告

显示前后对比：

```
覆盖率报告
──────────────────────────────
文件                   前    后
src/services/auth.ts   45%    88%
src/utils/validation.ts 32%   82%
──────────────────────────────
整体：                 67%    84%  ✅
```

## 关注领域

- 复杂分支的函数（高圈复杂度）
- 错误处理程序和catch块
- 代码库中使用的工具函数
- API端点处理程序（请求 → 响应流程）
- 边缘情况：null、undefined、空字符串、空数组、零、负数
