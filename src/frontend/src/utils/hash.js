/**
 * 计算文件 SHA-256 哈希值
 * 
 * 注意：crypto.subtle API 需要安全上下文（HTTPS 或 localhost）
 * 在非安全上下文中，秒传功能将不可用
 */

// 标记是否支持安全哈希
export const supportsSecureHash = !!(window.crypto && window.crypto.subtle)

/**
 * 计算文件 SHA-256 哈希值
 * @returns {Promise<{hash: string, secure: boolean}>}
 */
export const calculateHash = async (file) => {
  // 检查是否支持 Web Crypto API
  if (window.crypto && window.crypto.subtle) {
    const hash = await calculateHashWithCrypto(file)
    return { hash, secure: true }
  }
  
  // 非安全上下文：返回 null，跳过秒传检查
  console.warn('Web Crypto API 不可用（需要 HTTPS 或 localhost），秒传功能将禁用')
  return { hash: null, secure: false }
}

/**
 * 使用 Web Crypto API 计算 SHA-256
 */
const calculateHashWithCrypto = async (file) => {
  return new Promise((resolve, reject) => {
    const reader = new FileReader()
    reader.onload = async (e) => {
      try {
        const buffer = e.target.result
        const hashBuffer = await crypto.subtle.digest('SHA-256', buffer)
        const hashArray = Array.from(new Uint8Array(hashBuffer))
        const hashHex = hashArray.map(b => b.toString(16).padStart(2, '0')).join('')
        resolve(hashHex)
      } catch (error) {
        reject(error)
      }
    }
    reader.onerror = () => reject(reader.error)
    reader.readAsArrayBuffer(file)
  })
}
