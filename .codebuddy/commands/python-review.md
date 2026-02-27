---
description: Python代码全面审查，检查PEP 8合规性、类型提示、安全和Python惯用法。调用python-reviewer agent
---

# Python代码审查

此命令调用 **python-reviewer** agent 进行全面的Python特定代码审查。

## 命令功能

1. **识别Python变更**：通过 `git diff` 查找修改的 `.py` 文件
2. **运行静态分析**：执行 `ruff`、`mypy`、`pylint`、`black --check`
3. **安全扫描**：检查SQL注入、命令注入、不安全反序列化
4. **类型安全审查**：分析类型提示和mypy错误
5. **Pythonic代码检查**：验证代码遵循PEP 8和Python最佳实践
6. **生成报告**：按严重程度分类问题

## 使用时机

在以下情况使用 `/python-review`：
- 编写或修改Python代码后
- 提交Python变更前
- 审查包含Python代码的Pull Request
- 接手新的Python代码库
- 学习Pythonic模式和惯用法

## 审查分类

### 严重（必须修复）
- SQL/命令注入漏洞
- 不安全的eval/exec使用
- Pickle不安全反序列化
- 硬编码凭证
- YAML不安全加载
- 隐藏错误的裸except子句

### 高（应该修复）
- 公共函数缺少类型提示
- 可变默认参数
- 静默吞掉异常
- 资源未使用上下文管理器
- 使用C风格循环而非推导式
- 使用type()而非isinstance()
- 无锁的竞态条件

### 中（考虑）
- PEP 8格式违规
- 公共函数缺少文档字符串
- 使用print而非logging
- 低效字符串操作
- 没有命名常量的魔法数字
- 未使用f-string格式化
- 不必要的列表创建

## 运行的自动检查

```bash
# 类型检查
mypy .

# Lint和格式化
ruff check .
black --check .
isort --check-only .

# 安全扫描
bandit -r .

# 依赖审计
pip-audit
safety check

# 测试
pytest --cov=app --cov-report=term-missing
```

## 批准标准

| 状态 | 条件 |
|------|------|
| ✅ 批准 | 无严重或高问题 |
| ⚠️ 警告 | 只有中等问题（谨慎合并） |
| ❌ 阻止 | 发现严重或高问题 |

## 常见修复

### 添加类型提示
```python
# 之前
def calculate(x, y):
    return x + y

# 之后
from typing import Union

def calculate(x: Union[int, float], y: Union[int, float]) -> Union[int, float]:
    return x + y
```

### 使用上下文管理器
```python
# 之前
f = open("file.txt")
data = f.read()
f.close()

# 之后
with open("file.txt") as f:
    data = f.read()
```

### 使用列表推导式
```python
# 之前
result = []
for item in items:
    if item.active:
        result.append(item.name)

# 之后
result = [item.name for item in items if item.active]
```

### 修复可变默认值
```python
# 之前
def append(value, items=[]):
    items.append(value)
    return items

# 之后
def append(value, items=None):
    if items is None:
        items = []
    items.append(value)
    return items
```

## 与其他命令的集成

- 先使用 `/tdd` 确保测试通过
- 对非Python特定问题使用 `/code-review`
- 提交前使用 `/python-review`
- 如静态分析工具失败使用 `/build-fix`
