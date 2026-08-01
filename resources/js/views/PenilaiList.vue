<template>
  <div class="space-y-6">
    <!-- Header & Search -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Panel Penilai Dokumen</h1>
        <p class="text-xs text-slate-500">Daftar permohonan masuk yang memerlukan peninjauan dan penilaian.</p>
      </div>

      <!-- Search Box & Export -->
      <div class="flex items-center gap-3">
        <div class="relative">
          <input
            v-model="search"
            @input="fetchData(1)"
            type="text"
            placeholder="Cari No. Permohonan / Judul..."
            class="border border-slate-300 rounded-lg pl-9 pr-3 py-1.5 text-xs w-64 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
          />
          <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>
        
        <button
          @click="exportData"
          class="flex items-center gap-2 px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg shadow-sm transition text-xs"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
          Export
        </button>
      </div>
    </div>

    <!-- Table Antrean Permohonan -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <table class="w-full text-left text-xs text-slate-600">
        <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase">
          <tr>
            <th class="p-4">No. Permohonan</th>
            <th class="p-4">Pemohon</th>
            <th class="p-4">Judul Project</th>
            <th class="p-4">Lampiran Dokumen</th>
            <th class="p-4">Status</th>
            <th class="p-4 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="item in pagination.data" :key="item.id" class="hover:bg-slate-50 transition">
            <!-- Link Nomor Permohonan -->
            <td class="p-4 font-bold text-indigo-600">
              <router-link :to="`/penilai/permohonan/${item.nomor_permohonan}`" class="hover:underline">
                {{ item.nomor_permohonan }}
              </router-link>
            </td>
            <td class="p-4">
              <p class="font-bold text-slate-800">{{ item.pemohon?.name || '-' }}</p>
              <p class="text-[10px] text-slate-400">{{ item.pemohon?.email }}</p>
            </td>
            <td class="p-4 font-semibold text-slate-700">{{ item.judul_project }}</td>
            <td class="p-4">
              <a
                v-if="item.dokumen?.length"
                :href="`/storage/${item.dokumen[0].file_path}`"
                target="_blank"
                class="text-indigo-600 font-bold hover:underline inline-flex items-center gap-1"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                {{ item.dokumen[0].nama_file }}
              </a>
              <span v-else class="text-slate-400 italic">Tidak Ada File</span>
            </td>
            <td class="p-4">
              <span :class="statusClass(item.status)" class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide">
                {{ item.status }}
              </span>
            </td>
            <td class="p-4 text-center">
              <!-- Hanya Tombol Detail -->
              <router-link
                :to="`/penilai/permohonan/${item.nomor_permohonan}`"
                class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-sm transition inline-block text-[11px]"
              >
                Detail
              </router-link>
            </td>
          </tr>
          <tr v-if="!pagination.data?.length">
            <td colspan="6" class="p-8 text-center text-slate-400 italic">Belum ada permohonan masuk.</td>
          </tr>
        </tbody>
      </table>

      <!-- Navigasi Pagination -->
      <div v-if="pagination.last_page > 1" class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
        <span>Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</span>
        <div class="flex gap-1">
          <button
            @click="fetchData(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-3 py-1 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-40 disabled:hover:bg-transparent"
          >
            Sebelumnya
          </button>
          <button
            @click="fetchData(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-1 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-40 disabled:hover:bg-transparent"
          >
            Selanjutnya
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../axios';

const pagination = ref({});
const search = ref('');

const fetchData = async (page = 1) => {
  try {
    const res = await api.get('/penilai/permohonan', { 
      params: { 
        search: search.value,
        page: page
      } 
    });
    pagination.value = res.data;
  } catch (err) {
    console.error(err);
  }
};

const exportData = async () => {
  try {
    const res = await api.get('/penilai/permohonan/export', {
      params: { search: search.value },
      responseType: 'blob'
    });
    const url = window.URL.createObjectURL(new Blob([res.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `Data_Permohonan_Penilai_${new Date().toISOString().slice(0,10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  } catch (err) {
    console.error('Gagal export data', err);
  }
};

const statusClass = (status) => ({
  draft: 'bg-slate-100 text-slate-600',
  submitted: 'bg-blue-100 text-blue-700',
  revisi: 'bg-amber-100 text-amber-700',
  approved: 'bg-emerald-100 text-emerald-700',
  rejected: 'bg-rose-100 text-rose-700',
}[status] || 'bg-slate-100 text-slate-600');

onMounted(() => fetchData());
</script>