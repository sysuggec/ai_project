---
description: 增量修复Go构建错误、go vet警告和linter问题。调用go-build-resolver agent进行最小、精确的修复
---

# Go构建修复

此命令调用 **go-build-resolver** agent 以最小变更增量修复Go构建错误。

## 命令功能

1. **运行诊断**：执行 `go build`、`go vet`、`staticcheck`
2. **解析错误**：按文件分组并按严重程度排序
3. **增量修复**：一次一个错误
4. **验证每个修复**：每次变更后重新构建
5. **报告摘要**：显示已修复和剩余内容

## 使用时机

在以下情况使用 `/go-build`：
- `go build ./...` 因错误失败
- `go vet ./...` 报告问题
- `golangci-lint run` 显示警告
- 模块依赖损坏
- 拉取的变更导致构建失败后

## 运行的诊断命令

```bash
# 主要构建检查
go build ./...

# 静态分析
go vet ./...

# 扩展lint（如可用）
staticcheck ./...
golangci-lint run

# 模块问题
go mod verify
go mod tidy -v
```

## 常见错误修复

| 错误 | 典型修复 |
|------|----------|
| `undefined: X` | 添加导入或修复拼写错误 |
| `cannot use X as Y` | 类型转换或修复赋值 |
| `missing return` | 添加return语句 |
| `X does not implement Y` | 添加缺失的方法 |
| `import cycle` | 重组包结构 |
| `declared but not used` | 删除或使用变量 |
| `cannot find package` | `go get` 或 `go mod tidy` |

## 修复策略

1. **先修复构建错误** - 代码必须能编译
2. **其次修复vet警告** - 修复可疑构造
3. **第三修复lint警告** - 风格和最佳实践
4. **一次修复一个** - 验证每个变更
5. **最小变更** - 不重构，只修复

## 停止条件

agent将在以下情况停止并报告：
- 同一错误持续3次
- 修复引入更多错误
- 需要架构变更
- 缺少外部依赖

## 相关命令

- `/go-test` - 构建成功后运行测试
- `/go-review` - 审查代码质量
- `/verify` - 完整验证循环
