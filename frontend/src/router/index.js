import { createRouter, createWebHistory } from 'vue-router'

// We will create this Manuscripts view file in the next step!
import Manuscripts from '../views/Manuscripts.vue' 

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'Dashboard',
      component: () => import('../views/Dashboard.vue')
    },
    {
      path: '/publications',
      name: 'Manuscripts',
      component: Manuscripts
    },
    {
      path: '/assignments',
      name: 'Assignments',
      component: () => import('../views/Assignments.vue')
    },
    {
      path: '/my-tasks',
      name: 'MyTasks',
      component: () => import('../views/MyTasks.vue')
    },
    {
      path: '/article-status',
      name: 'ArticleStatus',
      component: () => import('../views/ArticleStatus.vue')
    },
    {
      path: '/eic-review',
      name: 'EicReview',
      component: () => import('../views/EicReview.vue')
    },
    {
      path: '/users',
      name: 'Users',
      component: () => import('../views/Users.vue')
    }
  ]
})

export default router
