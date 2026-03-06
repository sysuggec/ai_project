#!/bin/bash
# 快速功能测试

echo "=========================================="
echo "   资源管理系统 - 快速测试"
echo "=========================================="
echo ""

BASE_URL="http://localhost:8080"

# 检查服务状态
echo "📡 检查服务状态..."
if curl -s --max-time 5 "$BASE_URL/" > /dev/null 2>&1; then
    echo "   ✅ 服务运行中"
else
    echo "   ❌ 服务未启动"
    echo ""
    echo "请先启动服务："
    echo "  cd /workspace/src/backend && php -S 0.0.0.0:8080 -t public"
    exit 1
fi

echo ""
echo "📋 测试项目："
echo ""

# 1. 首页
echo "1️⃣  首页"
html=$(curl -s "$BASE_URL/")
if echo "$html" | grep -q "资源管理系统"; then
    echo "   ✅ 页面标题正确"
else
    echo "   ❌ 页面标题错误"
fi

# 2. 静态资源
echo ""
echo "2️⃣  静态资源 (JS/CSS)"
js_status=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/assets/index-DNa1xsS8.js")
css_status=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/assets/index-C1zNlWGA.css")
if [ "$js_status" = "200" ] && [ "$css_status" = "200" ]; then
    echo "   ✅ JS 文件加载正常 (HTTP $js_status)"
    echo "   ✅ CSS 文件加载正常 (HTTP $css_status)"
else
    echo "   ❌ JS 文件加载失败 (HTTP $js_status)"
    echo "   ❌ CSS 文件加载失败 (HTTP $css_status)"
fi

# 3. 目录 API
echo ""
echo "3️⃣  目录 API"
dir_response=$(curl -s "$BASE_URL/api/directories")
if echo "$dir_response" | grep -q '"success"'; then
    echo "   ✅ 获取目录列表成功"
else
    echo "   ❌ 获取目录列表失败"
fi

# 4. 文件 API
echo ""
echo "4️⃣  文件 API"
file_response=$(curl -s "$BASE_URL/api/files")
if echo "$file_response" | grep -q '"success"'; then
    echo "   ✅ 获取文件列表成功"
else
    echo "   ❌ 获取文件列表失败"
fi

# 5. 秒传检查 API
echo ""
echo "5️⃣  秒传检查 API"
check_response=$(curl -s -X POST "$BASE_URL/api/upload/check" \
    -H "Content-Type: application/json" \
    -d '{"hash":"test123"}')
if echo "$check_response" | grep -q '"success"'; then
    echo "   ✅ 秒传检查接口正常"
else
    echo "   ❌ 秒传检查接口异常"
fi

# 6. 上传历史 API
echo ""
echo "6️⃣  上传历史 API"
history_response=$(curl -s "$BASE_URL/api/upload/history")
if echo "$history_response" | grep -q '"success"'; then
    echo "   ✅ 获取上传历史成功"
else
    echo "   ❌ 获取上传历史失败"
fi

echo ""
echo "=========================================="
echo "   测试完成"
echo "=========================================="
