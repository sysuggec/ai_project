---
description: 强制执行测试驱动开发工作流。先搭建接口、先生成测试，然后实现最小代码以通过。确保80%以上覆盖率。
---

# TDD命令

此命令调用 **tdd-guide** agent 强制执行测试驱动开发方法论。

## 命令功能

1. **搭建接口** - 先定义类型/接口
2. **先生成测试** - 编写失败测试（RED）
3. **实现最小代码** - 编写刚好够用的代码以通过（GREEN）
4. **重构** - 在保持测试通过的同时改进代码（REFACTOR）
5. **验证覆盖率** - 确保80%以上测试覆盖率

## 使用时机

在以下情况使用 `/tdd`：
- 实现新功能
- 添加新函数/组件
- 修复Bug（先编写重现Bug的测试）
- 重构现有代码
- 构建关键业务逻辑

## 工作原理

tdd-guide agent 将：

1. **定义接口**用于输入/输出
2. **编写将会失败的测试**（因为代码还不存在）
3. **运行测试**并验证它们因正确原因失败
4. **编写最小实现**使测试通过
5. **运行测试**并验证它们通过
6. **重构**代码同时保持测试通过
7. **检查覆盖率**，如低于80%则添加更多测试

## TDD循环

```
RED → GREEN → REFACTOR → REPEAT

RED：      编写失败测试
GREEN：    编写最小代码以通过
REFACTOR： 改进代码，保持测试通过
REPEAT：   下一个功能/场景
```

## 使用示例

```
用户：/tdd 我需要一个计算市场流动性评分的函数

Agent (tdd-guide):
# TDD会话：市场流动性评分计算器

## 步骤1：定义接口（搭建）

```typescript
// lib/liquidity.ts
export interface MarketData {
  totalVolume: number
  bidAskSpread: number
  activeTraders: number
  lastTradeTime: Date
}

export function calculateLiquidityScore(market: MarketData): number {
  // TODO: 实现
  throw new Error('未实现')
}
```

## 步骤2：编写失败测试（RED）

```typescript
// lib/liquidity.test.ts
import { calculateLiquidityScore } from './liquidity'

describe('calculateLiquidityScore', () => {
  it('应为高流动性市场返回高评分', () => {
    const market = {
      totalVolume: 100000,
      bidAskSpread: 0.01,
      activeTraders: 500,
      lastTradeTime: new Date()
    }

    const score = calculateLiquidityScore(market)

    expect(score).toBeGreaterThan(80)
    expect(score).toBeLessThanOrEqual(100)
  })

  it('应为低流动性市场返回低评分', () => {
    const market = {
      totalVolume: 100,
      bidAskSpread: 0.5,
      activeTraders: 2,
      lastTradeTime: new Date(Date.now() - 86400000) // 1天前
    }

    const score = calculateLiquidityScore(market)

    expect(score).toBeLessThan(30)
    expect(score).toBeGreaterThanOrEqual(0)
  })
})
```

## 步骤3：运行测试 - 验证失败

```bash
npm test lib/liquidity.test.ts

FAIL lib/liquidity.test.ts
  ✕ 应为高流动性市场返回高评分 (2 ms)
    Error: 未实现

1个测试失败，0个通过
```

✅ 测试按预期失败。准备实现。

## 步骤4：实现最小代码（GREEN）

...实现代码...

## 步骤5：运行测试 - 验证通过

```bash
npm test lib/liquidity.test.ts

PASS lib/liquidity.test.ts
  ✓ 应为高流动性市场返回高评分 (3 ms)
  ✓ 应为低流动性市场返回低评分 (2 ms)

3个测试通过
```

✅ 所有测试通过！

## 步骤6：重构（改进）

...重构代码...

✅ TDD会话完成！
```

## TDD最佳实践

**应该：**
- ✅ 先写测试，再写任何实现
- ✅ 实现后运行测试并验证失败
- ✅ 编写最小代码使测试通过
- ✅ 测试通过后才重构
- ✅ 添加边缘情况和错误场景
- ✅ 目标80%以上覆盖率（关键代码100%）

**不应该：**
- ❌ 测试前写实现
- ❌ 每次变更后跳过运行测试
- ❌ 一次写太多代码
- ❌ 忽略失败的测试
- ❌ 测试实现细节（测试行为）
- ❌ Mock一切（优先集成测试）

## 覆盖率要求

- **最低80%**用于所有代码
- **要求100%**用于：
  - 金融计算
  - 认证逻辑
  - 安全关键代码
  - 核心业务逻辑

## 重要说明

**强制**：必须在实现之前编写测试。TDD循环是：

1. **RED** - 编写失败测试
2. **GREEN** - 实现以通过
3. **REFACTOR** - 改进代码

永不跳过RED阶段。永不在测试前写代码。

## 与其他命令的集成

- 先使用 `/plan` 理解要构建什么
- 使用 `/tdd` 进行测试实现
- 如发生构建错误使用 `/build-fix`
- 使用 `/code-review` 审查实现
- 使用 `/test-coverage` 验证覆盖率

## 相关Agent

此命令调用位于以下位置的 `tdd-guide` agent：
`~/.claude/agents/tdd-guide.md`
