---
description: 从队友、Skill Creator或其他来源导入instincts
---

# Instinct导入命令

从以下来源导入instincts：
- 队友的导出
- Skill Creator（仓库分析）
- 社区集合
- 之前机器的备份

## 用法

```
/instinct-import team-instincts.yaml
/instinct-import https://github.com/org/repo/instincts.yaml
/instinct-import --from-skill-creator acme/webapp
```

## 执行操作

1. 获取instinct文件（本地路径或URL）
2. 解析和验证格式
3. 检查与现有instincts的重复
4. 合并或添加新instincts
5. 保存到 `~/.claude/homunculus/instincts/inherited/`

## 导入过程

```
📥 从以下位置导入instincts：team-instincts.yaml
================================================

发现12个instincts待导入。

分析冲突...

## 新Instincts（8个）
将添加：
  ✓ use-zod-validation (置信度: 0.7)
  ✓ prefer-named-exports (置信度: 0.65)
  ✓ test-async-functions (置信度: 0.8)
  ...

## 重复Instincts（3个）
已有类似instincts：
  ⚠️ prefer-functional-style
     本地：0.8 置信度，12次观察
     导入：0.7 置信度
     → 保留本地（更高置信度）

  ⚠️ test-first-workflow
     本地：0.75 置信度
     导入：0.9 置信度
     → 更新为导入（更高置信度）

## 冲突Instincts（1个）
与本地instincts矛盾：
  ❌ use-classes-for-services
     与以下冲突：avoid-classes
     → 跳过（需手动解决）

---
导入8个新、更新1个、跳过3个？
```

## 合并策略

### 重复项
导入与现有匹配的instinct时：
- **更高置信度胜出**：保留置信度更高的
- **合并证据**：合并观察次数
- **更新时间戳**：标记为最近验证

### 冲突项
导入与现有矛盾的instinct时：
- **默认跳过**：不导入冲突的instincts
- **标记审查**：两个都标记为需要关注
- **手动解决**：用户决定保留哪个

## 来源追踪

导入的instincts标记为：
```yaml
source: "inherited"
imported_from: "team-instincts.yaml"
imported_at: "2025-01-22T10:30:00Z"
original_source: "session-observation"  # 或 "repo-analysis"
```

## 标志

- `--dry-run`：预览而不导入
- `--force`：即使有冲突也导入
- `--merge-strategy <higher|local|import>`：如何处理重复
- `--from-skill-creator <owner/repo>`：从Skill Creator分析导入
- `--min-confidence <n>`：只导入高于阈值的instincts

## 输出

导入后：
```
✅ 导入完成！

添加：8个instincts
更新：1个instinct
跳过：3个instincts（2个重复，1个冲突）

新instincts保存到：~/.claude/homunculus/instincts/inherited/

运行 /instinct-status 查看所有instincts。
```
