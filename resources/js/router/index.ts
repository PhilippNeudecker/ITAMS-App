import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: '/login',
      name: 'login',
      component: () => import('@/components/auth/LoginPage.vue'),
      meta: { public: true },
    },
    {
      path: '/',
      component: () => import('@/components/shared/AppLayout.vue'),
      children: [
        { path: '',                redirect: '/assets' },
        { path: 'assets',          name: 'assets.index',  component: () => import('@/components/assets/AssetList.vue') },
        { path: 'assets/create',   name: 'assets.create', component: () => import('@/components/assets/AssetCreatePage.vue') },
        { path: 'assets/:id',      name: 'assets.show',   component: () => import('@/components/assets/AssetDetail.vue') },
        { path: 'assets/:id/edit', name: 'assets.edit',   component: () => import('@/components/assets/AssetEditPage.vue') },
        { path: 'categories',      name: 'categories',    component: () => import('@/components/categories/CategoryList.vue') },
        { path: 'tags',            name: 'tags',          component: () => import('@/components/tags/TagList.vue') },
        { path: 'locations',       name: 'locations',     component: () => import('@/components/locations/LocationList.vue') },
        { path: 'manufacturers',   name: 'manufacturers', component: () => import('@/components/manufacturers/ManufacturerList.vue') },
      ],
    },
  ],
})

router.beforeEach((to) => {
  const token = localStorage.getItem('itams_token')
  if (!to.meta.public && !token) return { name: 'login' }
})

export default router
