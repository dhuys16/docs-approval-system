import { createRouter, createWebHistory } from 'vue-router';
import Login from './views/Login.vue';
import Dashboard from './views/Dashboard.vue';
import PermohonanList from './views/PermohonanList.vue';
import PenilaiList from './views/PenilaiList.vue';

const routes = [
    { path: '/login', name: 'Login', component: Login },
    { path: '/', name: 'Dashboard', component: Dashboard, meta: { requiresAuth: true } },
    { path: '/permohonan', name: 'Permohonan', component: PermohonanList, meta: { requiresAuth: true } },
    { path: '/penilai', name: 'Penilai', component: PenilaiList, meta: { requiresAuth: true } },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('token');
    if (to.meta.requiresAuth && !token) {
        next('/login');
    } else if (to.path === '/login' && token) {
        next('/');
    } else {
        next();
    }
});

export default router;