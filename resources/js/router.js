import { createRouter, createWebHistory } from 'vue-router';
import Login from './views/Login.vue';
import Dashboard from './views/Dashboard.vue';
import PermohonanList from './views/PermohonanList.vue';
import PermohonanCreate from './views/PermohonanCreate.vue';
import PermohonanDetail from './views/PermohonanDetail.vue';
import PenilaiList from './views/PenilaiList.vue';

const routes = [
  { path: '/login', name: 'Login', component: Login },
  { path: '/', name: 'Dashboard', component: Dashboard },
  { path: '/permohonan', name: 'PermohonanList', component: PermohonanList },
  { path: '/permohonan/create', name: 'PermohonanCreate', component: PermohonanCreate },
  { path: '/permohonan/:nomor_permohonan', name: 'PermohonanDetail', component: PermohonanDetail },
  { path: '/penilai', name: 'PenilaiList', component: PenilaiList },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');
  if (to.name !== 'Login' && !token) {
    next({ name: 'Login' });
  } else if (to.name === 'Login' && token) {
    next({ name: 'Dashboard' });
  } else {
    next();
  }
});

export default router;