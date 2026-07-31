<template>
  <div class="min-h-screen bg-gray-50 flex flex-col">
    <header v-if="user" class="bg-indigo-600 text-white shadow-md">
      <div class="max-w-7xl mx-auto px-4 py-3 flex justify-between items-center">
        <div class="flex items-center gap-6">
          <div>
            <h1 class="text-xl font-bold">DocApproval System</h1>
            <span class="text-xs bg-indigo-800 px-2 py-0.5 rounded uppercase font-semibold">
              Role: {{ user.role }}
            </span>
          </div>

          <!-- Menu Navigasi Sesuai Role -->
          <nav class="hidden md:flex gap-3 text-sm">
            <router-link to="/" class="hover:text-indigo-200 font-medium">Dashboard</router-link>
            <router-link v-if="user.role === 'pemohon'" to="/permohonan" class="hover:text-indigo-200 font-medium">
              Permohonan Saya
            </router-link>
            <router-link v-if="user.role === 'penilai'" to="/penilai" class="hover:text-indigo-200 font-medium">
              Verifikasi Masuk
            </router-link>
          </nav>
        </div>

        <div class="flex items-center gap-4">
          <span class="text-sm">Halo, <strong>{{ user.name }}</strong></span>
          <button @click="handleLogout" class="bg-red-500 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded transition">
            Logout
          </button>
        </div>
      </div>
    </header>

    <main class="grow max-w-7xl w-full mx-auto p-4 md:p-6">
      <router-view></router-view>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from './axios';

const user = ref(null);
const router = useRouter();

const fetchUser = () => {
  const userData = localStorage.getItem('user');
  if (userData) {
    user.value = JSON.parse(userData);
  }
};

const handleLogout = async () => {
  try {
    await api.post('/logout');
  } catch (e) {
    console.error(e);
  } finally {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    user.value = null;
    router.push('/login');
  }
};

onMounted(() => {
  fetchUser();
});
</script>