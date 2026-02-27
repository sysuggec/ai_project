---
description: 强制Go的TDD工作流。先写表驱动测试，然后实现。用go test -cover验证80%以上覆盖率
---

# Go TDD命令

此命令使用地道Go测试模式强制执行Go代码的测试驱动开发方法论。

## 命令功能

1. **定义类型/接口**：先搭建函数签名
2. **编写表驱动测试**：创建全面的测试用例（RED）
3. **运行测试**：验证测试因正确原因失败
4. **实现代码**：编写最小代码以通过（GREEN）
5. **重构**：在保持测试绿色的同时改进
6. **检查覆盖率**：确保80%以上覆盖率

## 使用时机

在以下情况使用 `/go-test`：
- 实现新的Go函数
- 为现有代码添加测试覆盖
- 修复Bug（先写失败测试）
- 构建关键业务逻辑
- 学习Go中的TDD工作流

## TDD循环

```
RED     → 编写失败的表驱动测试
GREEN   → 实现最小代码以通过
REFACTOR → 改进代码，测试保持绿色
REPEAT  → 下一个测试用例
```

## 测试模式

### 表驱动测试
```go
tests := []struct {
    name     string
    input    InputType
    want     OutputType
    wantErr  bool
}{
    {"case 1", input1, want1, false},
    {"case 2", input2, want2, true},
}

for _, tt := range tests {
    t.Run(tt.name, func(t *testing.T) {
        got, err := Function(tt.input)
        // 断言
    })
}
```

### 并行测试
```go
for _, tt := range tests {
    tt := tt // 捕获
    t.Run(tt.name, func(t *testing.T) {
        t.Parallel()
        // 测试体
    })
}
```

## 覆盖率命令

```bash
# 基本覆盖率
go test -cover ./...

# 覆盖率配置文件
go test -coverprofile=coverage.out ./...

# 在浏览器中查看
go tool cover -html=coverage.out

# 按函数覆盖率
go tool cover -func=coverage.out

# 带竞态检测
go test -race -cover ./...
```

## 覆盖率目标

| 代码类型 | 目标 |
|----------|------|
| 关键业务逻辑 | 100% |
| 公共API | 90%+ |
| 一般代码 | 80%+ |
| 生成代码 | 排除 |

## TDD最佳实践

**应该：**
- 在任何实现之前先写测试
- 每次变更后运行测试
- 使用表驱动测试获得全面覆盖
- 测试行为，而非实现细节
- 包含边缘情况（空、nil、最大值）

**不应该：**
- 在测试前写实现
- 跳过RED阶段
- 直接测试私有函数
- 在测试中使用 `time.Sleep`
- 忽略不稳定的测试

## 相关命令

- `/go-build` - 修复构建错误
- `/go-review` - 实现后审查代码
- `/verify` - 运行完整验证循环
