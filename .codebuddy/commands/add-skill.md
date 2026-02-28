# add-skill

创建一个新的 Claude 技能（Skill）。

## 使用方式

```
add-skill <skill-name>
```

## 参数

- `skill-name`: 技能名称，使用 hyphen-case 格式（如 `my-api-helper`）

## 执行步骤

1. **验证技能名称**
   - 检查名称是否符合 hyphen-case 格式
   - 仅允许小写字母、数字和连字符
   - 最大 40 个字符

2. **使用 skill-factory 技能初始化**
   ```bash
   python3 /workspace/.codebuddy/skills/skill-factory/scripts/init_skill.py <skill-name> --path /workspace/.codebuddy/skills
   ```

3. **提示后续操作**
   - 编辑 SKILL.md 完成技能定义
   - 添加必要的脚本、引用和资源文件
   - 验证并打包技能

## 示例

```
add-skill pdf-editor
add-skill data-analyzer
add-skill api-client
```

## 输出

在 `/workspace/.codebuddy/skills/<skill-name>/` 目录下创建以下结构：

```
<skill-name>/
├── SKILL.md          # 技能定义文件
├── scripts/          # 可执行脚本
├── references/       # 参考文档
└── assets/           # 资源文件
```
