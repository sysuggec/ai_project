#!/bin/bash
# API 测试脚本

BASE_URL="http://localhost:8080"
PASS=0
FAIL=0

echo "===== 资源管理系统 API 测试 ====="
echo ""

# 测试函数
test_api() {
    local name="$1"
    local method="$2"
    local endpoint="$3"
    local data="$4"
    local expected="$5"
    
    echo -n "测试: $name ... "
    
    if [ -n "$data" ]; then
        response=$(curl -s -X "$method" "$BASE_URL$endpoint" \
            -H "Content-Type: application/json" \
            -d "$data")
    else
        response=$(curl -s -X "$method" "$BASE_URL$endpoint")
    fi
    
    if echo "$response" | grep -q "$expected"; then
        echo "✅ 通过"
        ((PASS++))
    else
        echo "❌ 失败"
        echo "   响应: $response"
        ((FAIL++))
    fi
}

# 1. 测试前端页面
echo "--- 前端页面测试 ---"
test_api "首页加载" "GET" "/" "" "<!DOCTYPE html>"

# 2. 测试目录 API
echo ""
echo "--- 目录 API 测试 ---"
test_api "获取目录列表" "GET" "/api/directories" "" '"success"'
test_api "创建目录" "POST" "/api/directories" '{"path":"/测试目录"}' '"success"'
test_api "获取目录（包含新建）" "GET" "/api/directories" "" '测试目录'

# 3. 测试文件 API
echo ""
echo "--- 文件 API 测试 ---"
test_api "获取文件列表" "GET" "/api/files" "" '"success"'
test_api "搜索文件" "GET" "/api/files?search=test" "" '"success"'

# 4. 测试上传历史 API
echo ""
echo "--- 上传历史 API 测试 ---"
test_api "获取上传历史" "GET" "/api/upload/history" "" '"success"'

# 5. 测试秒传检查 API
echo ""
echo "--- 秒传检查 API 测试 ---"
test_api "检查哈希（不存在）" "POST" "/api/upload/check" '{"hash":"abc123def456"}' '"exists":false'

# 输出结果
echo ""
echo "===== 测试结果 ====="
echo "通过: $PASS"
echo "失败: $FAIL"
echo ""

if [ $FAIL -eq 0 ]; then
    echo "🎉 所有测试通过！"
    exit 0
else
    echo "⚠️ 部分测试失败"
    exit 1
fi
