---
description: Go代码全面审查，检查地道模式、并发安全、错误处理和安全。调用go-reviewer agent
---

# Go代码审查

此命令调用 **go-reviewer** agent 进行全面的Go特定代码审查。

## 命令功能

1. **识别Go变更**：通过 `git diff` 查找修改的 `.go` 文件
2. **运行静态分析**：执行 `go vet`、`staticcheck`、`golangci-lint`
3. **安全扫描**：检查SQL注入、命令注入、竞态条件
4. **并发审查**：分析goroutine安全、channel使用、mutex模式
5. **地道Go检查**：验证代码遵循Go约定和最佳实践
6. **生成报告**：按严重程度分类问题

## 使用时机

在以下情况使用 `/go-review`：
- 编写或修改Go代码后
- 提交Go变更前
- 审查包含Go代码的Pull Request
- 接手新的Go代码库
- 学习地道Go模式

## 审查分类

### 严重（必须修复）
- SQL/命令注入漏洞
- 无同步的竞态条件
- Goroutine泄漏
- 硬编码凭证
- 不安全的指针使用
- 关键路径上忽略错误

### 高（应该修复）
- 缺少带上下文的错误包装
- 使用panic而非错误返回
- Context未传递
- 无缓冲channel导致死锁
- 接口未满足错误
- 缺少mutex保护

### 中（考虑）
- 非地道代码模式
- 导出项缺少godoc注释
- 低效字符串拼接
- 切片未预分配
- 未使用表驱动测试

## 运行的自动检查

```bash
# 静态分析
go vet ./...

# 高级检查（如已安装）
staticcheck ./...
golangci-lint run

# 竞态检测
go build -race ./...

# 安全漏洞
govulncheck ./...
```

## 批准标准

| 状态 | 条件 |
|------|------|
| ✅ 批准 | 无严重或高问题 |
| ⚠️ 警告 | 只有中等问题（谨慎合并） |
| ❌ 阻止 | 发现严重或高问题 |

## 与其他命令的集成

- 先使用 `/go-test` 确保测试通过
- 如发生构建错误使用 `/go-build`
- 提交前使用 `/go-review`
- 对非Go特定问题使用 `/code-review`
