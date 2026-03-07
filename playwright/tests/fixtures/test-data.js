/**
 * 测试数据生成器
 */

const fs = require('fs');
const path = require('path');

/**
 * 创建测试用的临时文件
 * @param {string} filename - 文件名
 * @param {string} content - 文件内容
 * @returns {string} 文件路径
 */
function createTestFile(filename, content = 'test content') {
  const testDir = path.join(__dirname, '../fixtures/temp');
  if (!fs.existsSync(testDir)) {
    fs.mkdirSync(testDir, { recursive: true });
  }
  const filePath = path.join(testDir, filename);
  fs.writeFileSync(filePath, content);
  return filePath;
}

/**
 * 清理测试文件
 */
function cleanupTestFiles() {
  const testDir = path.join(__dirname, '../fixtures/temp');
  if (fs.existsSync(testDir)) {
    fs.rmSync(testDir, { recursive: true, force: true });
  }
}

/**
 * 测试用的文件数据
 */
const testFiles = {
  textFile: {
    name: 'test-document.txt',
    content: 'This is a test document for Playwright testing.',
    mimeType: 'text/plain'
  },
  imageFile: {
    name: 'test-image.png',
    content: Buffer.from('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==', 'base64'),
    mimeType: 'image/png'
  },
  jsonFile: {
    name: 'test-data.json',
    content: JSON.stringify({ test: true, data: 'sample' }),
    mimeType: 'application/json'
  }
};

module.exports = {
  createTestFile,
  cleanupTestFiles,
  testFiles
};
