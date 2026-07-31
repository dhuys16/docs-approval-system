<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Dashboard Statistik</h1>
        <p class="text-sm text-gray-500">Ringkasan status pengajuan permohonan</p>
      </div>
      <button
        @click="fetchStats"
        class="text-sm bg-white border border-gray-300 hover:bg-gray-50 px-3 py-1.5 rounded-md text-gray-700 shadow-sm transition"
      >
        Refresh Data
      </button>
    </div>

    <!-- Indicator Loading -->
    <div v-if="loading" class="text-center py-12 text-gray-500">
      Memuat data statistik dari cache...
    </div>

    <!-- Grid Card Stats -->
    <div v-else class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100">
        <p class="text-xs font-semibold text-gray-400 uppercase">Total</p>
        <p class="text-2xl font-extrabold text-gray-800 mt-1">{{ stats.total }}</p>
      </div>

      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 border-l-4 border-l-gray-400">
        <p class="text-xs font-semibold text-gray-400 uppercase">Draft</p>
        <p class="text-2xl font-extrabold text-gray-600 mt-1">{{ stats.draft }}</p>
      </div>

      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 border-l-4 border-l-blue-500">
        <p class="text-xs font-semibold text-blue-500 uppercase">Submitted</p>
        <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ stats.submitted }}</p>
      </div>

      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 border-l-4 border-l-yellow-500">
        <p class="text-xs font-semibold text-yellow-600 uppercase">Revisi</p>
        <p class="text-2xl font-extrabold text-yellow-600 mt-1">{{ stats.revisi }}</p>
      </div>

      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 border-l-4 border-l-green-500">
        <p class="text-xs font-semibold text-green-600 uppercase">Approved</p>
        <p class="text-2xl font-extrabold text-green-600 mt-1">{{ stats.approved }}</p>
      </div>

      <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-100 border-l-4 border-l-red-500">
        <p class="text-xs font-semibold text-red-500 uppercase">Rejected</p>
        <p class="text-2xl font-extrabold text-red-600 mt-1">{{ stats.rejected }}</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import api from '../axios';

const stats = ref({
  total: 0,
  draft: 0,
  submitted: 0,
  revisi: 0,
  approved: 0,
  rejected: 0,
});

const loading = ref(true);
let timer = null;

const fetchStats = async (isBackground = false) => {
  if (!isBackground) loading.value = true;
  try {
    const res = await api.get('/dashboard/stats');
    stats.value = res.data.statistics;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  fetchStats();
  
  // Auto-refresh data setiap 5 detik (Polling)
  timer = setInterval(() => {
    fetchStats(true);
  }, 5000);
});

// Bersihkan timer saat pengguna berpindah halaman
onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>