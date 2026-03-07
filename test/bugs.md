# 开发测试过程中发现的 Bug 及修复方法

## 1. CloudStudio 分享链接无法访问

### 问题描述
服务在本地运行正常，但通过 CloudStudio 的分享链接无法访问页面。

### 原因分析
CloudStudio 环境下，分享链接只能映射单一端口。原设计前端(Vite)和后端(PHP)分别运行在不同端口，导致分享链接只能访问其中一个服务。

### 解决方案
将前端构建输出到后端 public 目录，实现单端口同时提供前端静态文件和 API 服务：

1. **修改 `vite.config.js`**:
```javascript
build: {
  outDir: '../backend/public',
  emptyOutDir: true,
}
```

2. **修改 `index.php`** 添加静态文件路由:
```php
// 静态资源
if (preg_match('#^/assets/(.+)$#', $requestPath, $matches)) {
    $file = __DIR__ . '/assets/' . $matches[1];
    if (file_exists($file)) {
        $mime = mime_content_type($file);
        header('Content-Type: ' . $mime);
        readfile($file);
        exit;
    }
}
```

---

## 2. 前端页面白屏

### 问题描述
访问页面时显示白屏，浏览器控制台报 404 错误加载 JS/CSS 资源。

### 原因分析
Vite 构建配置中 `outDir` 误设为 `../backend/public/assets`，导致资源文件路径嵌套错误：
- 实际输出: `public/assets/assets/index-xxx.js`
- 期望输出: `public/assets/index-xxx.js`

### 解决方案
修正 `vite.config.js` 中的 `outDir` 配置：
```javascript
// 错误配置
build: {
  outDir: '../backend/public/assets',
}

// 正确配置
build: {
  outDir: '../backend/public',
}
```

---

## 3. PHP 缺少 SQLite 驱动

### 问题描述
```
could not find driver
```

### 原因分析
PHP 8.3 默认未安装 SQLite 扩展。

### 解决方案
```bash
sudo apt-get update
sudo apt-get install php8.3-sqlite3
sudo service php8.3-fpm restart  # 或重启 CLI
```

---

## 4. 缺少数据库文件

### 问题描述
```
SQLSTATE[HY000]: unable to open database file
```

### 原因分析
SQLite 数据库文件 `storage/database.sqlite` 不存在，且 storage 目录未创建。

### 解决方案
```bash
mkdir -p /workspace/src/backend/storage
touch /workspace/src/backend/storage/database.sqlite
chmod 666 /workspace/src/backend/storage/database.sqlite
```

---

## 5. 缺少 Symfony Mime 组件

### 问题描述
```
Class "Symfony\Component\Mime\MimeTypes" not found
```

### 原因分析
文件下载功能使用 `symfony/mime` 组件检测 MIME 类型，但未安装。

### 解决方案
```bash
cd /workspace/src/backend
composer require symfony/mime
```

---

## 6. Eloquent DB Facade 不工作

### 问题描述
```
Call to undefined method Illuminate\Support\Facades\DB::transaction()
```

### 原因分析
使用独立 Eloquent ORM 时，Laravel Facades 不可用。`DB::transaction()` 是 Laravel 特有功能。

### 解决方案
使用 Capsule 的原生连接方法：
```php
// 错误用法
DB::transaction(function() { ... });

// 正确用法
use Illuminate\Database\Capsule\Manager as Capsule;

Capsule::connection()->beginTransaction();
try {
    // ... 业务逻辑
    Capsule::connection()->commit();
} catch (\Exception $e) {
    Capsule::connection()->rollBack();
    throw $e;
}
```

---

## 7. 根目录创建错误

### 问题描述
创建根目录 `/` 时，`parent_id` 设置为自身 ID 导致外键约束错误：
```
SQLSTATE[23000]: Integrity constraint violation: 19 FOREIGN KEY constraint failed
```

### 原因分析
`getOrCreateDirectory()` 方法处理路径时，对根目录 `/` 的 `parent_id` 计算错误，尝试设置为自身 ID。

### 解决方案
在 `UploadService.php` 中添加根目录特殊处理：
```php
public function getOrCreateDirectory(string $path): DirectoryModel
{
    // 处理根目录
    if ($path === '/' || $path === '') {
        $directory = DirectoryModel::where('path', '/')->first();
        if ($directory) {
            return $directory;
        }
        return DirectoryModel::create([
            'name' => 'root',
            'path' => '/',
            'parent_id' => null  // 根目录无父级
        ]);
    }
    // ... 其他路径处理
}
```

---

## 8. DateTime 格式化错误

### 问题描述
```
Call to a member function format() on string
```

### 原因分析
Eloquent 模型的日期字段可能是 `DateTime` 对象或字符串，取决于模型配置和获取方式。直接调用 `format()` 方法时类型不一致。

### 解决方案
在 `FileService.php` 中添加类型检查：
```php
$uploadTime = $file->upload_time;
if ($uploadTime instanceof \DateTimeInterface) {
    $uploadTime = $uploadTime->format('Y-m-d H:i:s');
}
```

---

## 9. 根目录重复显示

### 问题描述
目录列表中同时显示 "根目录" 和 "root" 两个条目，实际上是同一个根目录。

### 原因分析
1. 后端创建根目录时使用名称 "root"
2. 前端 `DirectoryTree.vue` 中硬编码了一个 "根目录" 条目（`path='/'`）
3. 同时 API 返回的目录列表中也包含了名为 "root" 的根目录

### 解决方案
1. **后端**: 将根目录名称从 "root" 改为 "根目录"
2. **前端**: 移除硬编码的 "根目录" 条目，只从 API 返回数据渲染
3. **数据**: 更新数据库中已有记录的 name 字段

```php
// UploadService.php
return DirectoryModel::create([
    'name' => '根目录',  // 原为 'root'
    'path' => '/',
    'parent_id' => null,
]);
```

```vue
<!-- DirectoryTree.vue - 移除硬编码根目录 -->
<template v-for="dir in directories" :key="dir.id">
  <TreeItem :item="dir" :selected="selected" :level="0" />
</template>
```

---

## 总结

| 序号 | Bug 类型 | 根本原因 | 修复耗时 |
|------|----------|----------|----------|
| 1 | 部署架构 | 双端口不兼容分享链接 | 中 |
| 2 | 配置错误 | Vite 输出路径配置错误 | 低 |
| 3 | 环境依赖 | PHP 扩展未安装 | 低 |
| 4 | 文件系统 | 数据库文件不存在 | 低 |
| 5 | 依赖缺失 | Composer 包未安装 | 低 |
| 6 | 框架差异 | Eloquent 独立模式限制 | 中 |
| 7 | 边界条件 | 根目录特殊情况未处理 | 中 |
| 8 | 类型安全 | 日期类型处理不一致 | 低 |
| 9 | 数据一致 | 前后端根目录命名不统一 | 低 |
| 10 | 构建配置 | vite emptyOutDir 删除 index.php | 低 |
| 11 | 数据修复 | 目录 parent_id 为空导致嵌套错误 | 低 |

### 经验教训

1. **CloudStudio 部署**: 单端口架构是分享链接的必要条件
2. **Vite 构建配置**: `outDir` 是相对于项目根目录，不是配置文件所在目录
3. **独立 Eloquent**: 避免 Laravel Facades，使用 Capsule 原生方法
4. **边界条件**: 路径处理要考虑根目录、空字符串等特殊输入
5. **类型安全**: PHP 弱类型语言，对可能混合类型的字段要添加类型检查
