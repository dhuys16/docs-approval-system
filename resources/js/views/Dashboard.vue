<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Dashboard Overview</h1>
        <p class="text-xs text-slate-500">Statistik permohonan real-time berdasarkan hak akses Anda (Role: <span class="font-bold uppercase text-indigo-600">{{ role }}</span>)</p>
      </div>
    </div>

    <!-- Cards Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <p class="text-[10px] font-bold text-slate-400 uppercase">Total</p>
        <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ stats.total || 0 }}</p>
      </div>

      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <p class="text-[10px] font-bold text-slate-400 uppercase">Draft</p>
        <p class="text-2xl font-extrabold text-slate-500 mt-1">{{ stats.draft || 0 }}</p>
      </div>

      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <p class="text-[10px] font-bold text-blue-500 uppercase">Submitted</p>
        <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ stats.submitted || 0 }}</p>
      </div>

      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <p class="text-[10px] font-bold text-amber-500 uppercase">Revisi</p>
        <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ stats.revisi || 0 }}</p>
      </div>

      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <p class="text-[10px] font-bold text-emerald-500 uppercase">Approved</p>
        <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ stats.approved || 0 }}</p>
      </div>

      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <p class="text-[10px] font-bold text-rose-500 uppercase">Rejected</p>
        <p class="text-2xl font-extrabold text-rose-600 mt-1">{{ stats.rejected || 0 }}</p>
      </div>
    </div>

    <!-- Banner Quick Navigation -->
    <div class="bg-slate-900 text-white p-6 rounded-xl flex items-center justify-between shadow-lg">
      <div>
        <h2 class="text-lg font-bold">Akses Menu Pengelolaan</h2>
        <p class="text-xs text-slate-400 mt-1">Kelola permohonan atau lakukan review dokumen sesuai kewenangan Anda.</p>
      </div>
      <div class="flex gap-3">
        <router-link to="/permohonan" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-xs font-semibold rounded-lg transition">
          Menu Pemohon
        </router-link>
        <router-link to="/penilai" class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-xs font-semibold rounded-lg border border-slate-700 transition">
          Menu Penilai
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../axios';

const stats = ref({});
const role = ref('');

onMounted(async () => {
  try {
    const res = await api.get('/dashboard/stats');
    stats.value = res.data.statistics || {};
    role.value = res.data.role || 'pemohon';
  } catch (err) {
    console.error('Gagal mengambil statistik dashboard', err);
  }
});
</script>