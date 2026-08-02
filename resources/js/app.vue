<template>
  <div v-if="$route.name === 'Login' || $route.name === 'Register'">
    <router-view></router-view>
  </div>

  <!-- Layout Utama Aplikasi -->
  <div v-else class="min-h-screen flex bg-slate-100 text-slate-800 font-sans">
    <!-- Sidebar -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col shrink-0 shadow-xl">
      <div class="p-5 flex items-center gap-3 border-b border-slate-800">
        <div class="bg-indigo-600 text-white p-2 rounded-lg font-bold text-lg leading-none shadow">
          DA
        </div>
        <div>
          <h1 class="font-bold text-white tracking-wide text-base">DocApprove</h1>
          <p class="text-[10px] text-slate-400 uppercase tracking-widest">Portal Approval</p>
        </div>
      </div>

      <nav class="flex-1 p-4 space-y-1 text-xs font-medium">
        <router-link
          to="/"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition"
          :class="$route.path === '/' ? 'bg-indigo-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
          Dashboard
        </router-link>

        <router-link
          v-if="user?.role === 'pemohon'"
          to="/permohonan"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition"
          :class="$route.path.startsWith('/permohonan') ? 'bg-indigo-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          Permohonan Saya
        </router-link>

        <router-link
          v-if="user?.role === 'penilai'"
          to="/penilai"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition"
          :class="$route.path.startsWith('/penilai') ? 'bg-indigo-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
          Panel Penilai
        </router-link>

        <router-link
          v-if="user?.role === 'admin'"
          to="/users"
          class="flex items-center gap-3 px-3.5 py-2.5 rounded-lg transition"
          :class="$route.path.startsWith('/users') ? 'bg-indigo-600 text-white font-semibold shadow' : 'hover:bg-slate-800 text-slate-400 hover:text-slate-200'"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
          Manajemen User
        </router-link>
      </nav>

      <!-- Profile & Logout -->
      <div class="p-4 border-t border-slate-800 flex items-center justify-between">
        <div class="truncate">
          <p class="text-xs font-semibold text-white truncate">{{ user?.name || 'User Active' }}</p>
          <p class="text-[10px] text-slate-400 truncate">{{ user?.email }}</p>
        </div>
        <button
          @click="handleLogout"
          title="Logout"
          class="p-2 text-slate-400 hover:text-rose-400 hover:bg-slate-800 rounded-lg transition"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
        </button>
      </div>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <main class="flex-1 overflow-y-auto p-8">
        <router-view></router-view>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from './axios';

const route = useRoute();
const router = useRouter();
const user = ref(null);

const fetchUser = async () => {
  if (localStorage.getItem('token') && route.name !== 'Login') {
    try {
      const res = await api.get('/me');
      user.value = res.data;
    } catch (err) {
      localStorage.removeItem('token');
      router.push('/login');
    }
  }
};

const handleLogout = async () => {
  try {
    await api.post('/logout');
  } catch (err) {
    console.error(err);
  } finally {
    localStorage.removeItem('token');
    user.value = null;
    router.push('/login');
  }
};

watch(() => route.path, () => {
  fetchUser();
});

onMounted(fetchUser);
</script>