import { inject } from 'vue'

export function useToast() {
  const showToast = inject('showToast', (message, type = 'info') => {
    console.log(`[Toast] ${type}: ${message}`)
  })

  return {
    showToast
  }
}
