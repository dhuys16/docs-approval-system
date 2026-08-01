import { createRouter, createWebHistory } from 'vue-router';
import Login from './views/Login.vue';
import Register from './views/Register.vue';
import Dashboard from './views/Dashboard.vue';
import PermohonanList from './views/PermohonanList.vue';
import PermohonanCreate from './views/PermohonanCreate.vue';
import PermohonanDetail from './views/PermohonanDetail.vue';
import PermohonanEdit from './views/PermohonanEdit.vue';
import PenilaiList from './views/PenilaiList.vue';
import PenilaiDetail from './views/PenilaiDetail.vue';

const routes = [
  { path: '/login', name: 'Login', component: Login },
  { path: '/register', name: 'Register', component: Register },
  { path: '/', name: 'Dashboard', component: Dashboard },
  { path: '/permohonan', name: 'PermohonanList', component: PermohonanList },
  { path: '/permohonan/create', name: 'PermohonanCreate', component: PermohonanCreate },
  { path: '/permohonan/:nomor_permohonan', name: 'PermohonanDetail', component: PermohonanDetail },
  { path: '/permohonan/:nomor_permohonan/edit', name: 'PermohonanEdit', component: PermohonanEdit },
  { path: '/penilai', name: 'PenilaiList', component: PenilaiList },
  { path: '/penilai/permohonan/:nomor_permohonan', name: 'PenilaiDetail', component: PenilaiDetail },
];  

const router = createRouter({
  history: createWebHistory(),
  routes,
});

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token');
  const isAuthRoute = to.name === 'Login' || to.name === 'Register';

  if (!isAuthRoute && !token) {
    next({ name: 'Login' });
  } else if (isAuthRoute && token) {
    next({ name: 'Dashboard' });
  } else {
    next();
  }
});

export default router;