# Code Review

使用 code-reviewer 技能对指定文件或目录进行代码审查，按照编码规范和安全最佳实践检查代码质量，并生成包含统计信息和行号的详细报告。

## 执行步骤

1. 加载 code-reviewer 技能
2. 使用技能对目标代码进行审查
3. 生成审查报告，包含：
   - 问题统计信息
   - 按严重程度分类的问题列表
   - 精确的行号定位
   - 改进建议

## 使用示例

- `code-review /path/to/file.php` - 审查单个文件
- `code-review app/Services/` - 审查目录下的代码
