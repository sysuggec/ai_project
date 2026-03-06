#!/bin/bash
# 文件上传测试脚本

BASE_URL="http://localhost:8080"
TEST_DIR="/workspace/test/files"

echo "===== 文件上传测试 ====="
echo ""

# 创建测试文件
mkdir -p "$TEST_DIR"
echo "这是一个测试文件的内容" > "$TEST_DIR/test.txt"
dd if=/dev/urandom of="$TEST_DIR/binary.bin" bs=1024 count=100 2>/dev/null

echo "测试文件已创建:"
ls -la "$TEST_DIR"
echo ""

# 计算 SHA-256 哈希
get_hash() {
    sha256sum "$1" | awk '{print $1}'
}

# 1. 测试文件上传
echo "--- 测试文件上传 ---"

file="$TEST_DIR/test.txt"
hash=$(get_hash "$file")
filename=$(basename "$file")

echo "文件: $filename"
echo "哈希: $hash"
echo ""

# 秒传检查
echo "1. 秒传检查..."
response=$(curl -s -X POST "$BASE_URL/api/upload/check" \
    -H "Content-Type: application/json" \
    -d "{\"hash\":\"$hash\"}")
echo "   响应: $response"

# 上传文件
echo "2. 上传文件..."
response=$(curl -s -X POST "$BASE_URL/api/upload/file" \
    -F "file=@$file" \
    -F "hash=$hash" \
    -F "directory=/")
echo "   响应: $response"

if echo "$response" | grep -q '"success"'; then
    echo "   ✅ 上传成功"
    file_id=$(echo "$response" | grep -o '"id":[0-9]*' | grep -o '[0-9]*' | head -1)
else
    echo "   ❌ 上传失败"
fi

# 3. 验证文件列表
echo ""
echo "3. 验证文件列表..."
response=$(curl -s "$BASE_URL/api/files")
echo "   响应: $response"

if echo "$response" | grep -q "$filename"; then
    echo "   ✅ 文件已在列表中"
else
    echo "   ❌ 文件未在列表中"
fi

# 4. 测试重复上传（秒传）
echo ""
echo "--- 测试秒传 ---"
echo "再次上传相同文件..."
response=$(curl -s -X POST "$BASE_URL/api/upload/file" \
    -F "file=@$file" \
    -F "hash=$hash" \
    -F "directory=/")
echo "   响应: $response"

# 5. 测试下载
if [ -n "$file_id" ]; then
    echo ""
    echo "--- 测试文件下载 ---"
    echo "下载文件 ID: $file_id"
    curl -s -o "$TEST_DIR/downloaded.txt" "$BASE_URL/api/files/$file_id/download"
    
    if diff -q "$file" "$TEST_DIR/downloaded.txt" > /dev/null; then
        echo "   ✅ 下载文件内容一致"
    else
        echo "   ❌ 下载文件内容不一致"
    fi
fi

# 6. 测试删除
if [ -n "$file_id" ]; then
    echo ""
    echo "--- 测试文件删除 ---"
    response=$(curl -s -X DELETE "$BASE_URL/api/files/$file_id")
    echo "   响应: $response"
    
    if echo "$response" | grep -q '"success"'; then
        echo "   ✅ 删除成功"
    else
        echo "   ❌ 删除失败"
    fi
fi

echo ""
echo "===== 测试完成 ====="
