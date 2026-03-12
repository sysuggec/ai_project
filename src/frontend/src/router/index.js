import { createRouter, createWebHistory } from 'vue-router'
import MainLayout from '@/layouts/MainLayout.vue'
import UploadPage from '@/pages/UploadPage.vue'
import ResourcePage from '@/pages/ResourcePage.vue'
import TrashPage from '@/pages/TrashPage.vue'
import SharePage from '@/pages/SharePage.vue'
import ShareAccessPage from '@/pages/ShareAccessPage.vue'

const routes = [
  {
    path: '/',
    component: MainLayout,
    children: [
      {
        path: '',
        redirect: '/upload'
      },
      {
        path: 'upload',
        name: 'Upload',
        component: UploadPage
      },
      {
        path: 'resources',
        name: 'Resources',
        component: ResourcePage
      },
      {
        path: 'trash',
        name: 'Trash',
        component: TrashPage
      },
      {
        path: 'shares',
        name: 'Shares',
        component: SharePage
      }
    ]
  },
  {
    path: '/share/:token',
    name: 'ShareAccess',
    component: ShareAccessPage
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
