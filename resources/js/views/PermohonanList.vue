<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Daftar Permohonan Saya</h1>
        <p class="text-xs text-slate-500">Kelola draft dan pengajuan permohonan proyek Anda.</p>
      </div>
      <router-link 
        to="/permohonan/create" 
        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow transition flex items-center gap-1.5"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Permohonan Baru
      </router-link>
    </div>

    <!-- Filter Status -->
    <div class="flex gap-2 border-b border-slate-200 pb-2 overflow-x-auto text-xs font-medium">
      <button
        v-for="st in ['', 'draft', 'submitted', 'revisi', 'approved', 'rejected']"
        :key="st"
        @click="filterStatus = st; fetchPermohonan()"
        :class="filterStatus === st ? 'bg-indigo-600 text-white font-semibold' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50'"
        class="px-3.5 py-1.5 rounded-lg capitalize transition whitespace-nowrap shadow-sm"
      >
        {{ st ? st : 'Semua Status' }}
      </button>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <table class="w-full text-left text-xs text-slate-600">
        <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase">
          <tr>
            <th class="p-4">No. Permohonan</th>
            <th class="p-4">Judul Project</th>
            <th class="p-4">Dokumen</th>
            <th class="p-4">Status</th>
            <th class="p-4">Catatan Terakhir</th>
            <th class="p-4 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
          <tr v-for="item in pagination.data" :key="item.id" class="hover:bg-slate-50 transition">
            <!-- No. Permohonan klik mengarah ke Halaman Detail -->
            <td class="p-4 font-bold text-indigo-600">
              <router-link :to="`/permohonan/${item.nomor_permohonan}`" class="hover:underline">
                {{ item.nomor_permohonan }}
              </router-link>
            </td>
            <td class="p-4 font-semibold text-slate-800">{{ item.judul_project }}</td>
            <td class="p-4">
              <span v-if="item.dokumen?.length" class="text-slate-600 font-medium">
                {{ item.dokumen[0].nama_file }} 
                <span class="text-slate-400 text-[10px]">({{ item.dokumen[0].file_size }} KB)</span>
              </span>
              <span v-else class="text-slate-400 italic">Tidak ada</span>
            </td>
            <td class="p-4">
              <span :class="statusClass(item.status)" class="px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide">
                {{ item.status }}
              </span>
            </td>
            <td class="p-4 text-slate-500 max-w-xs truncate">
              {{ item.riwayat?.[0]?.catatan || '-' }}
            </td>
            <td class="p-4 text-center space-x-1.5">
              <!-- Tombol Navigasi Halaman Detail Baru -->
              <router-link
                :to="`/permohonan/${item.nomor_permohonan}`"
                class="px-3 py-1.5 bg-indigo-50 border border-indigo-200 text-indigo-600 font-bold rounded-lg hover:bg-indigo-100 transition inline-block text-[11px]"
              >
                Detail
              </router-link>

              <!-- Tombol Edit (Hanya jika status Draft / Revisi) -->
              <router-link
                v-if="['draft', 'revisi'].includes(item.status)"
                :to="`/permohonan/${item.nomor_permohonan}/edit`"
                class="px-3 py-1.5 border border-slate-300 text-slate-700 font-semibold rounded-lg hover:bg-slate-100 transition inline-block text-[11px]"
              >
                Edit
              </router-link>
            </td>
          </tr>
          <tr v-if="!pagination.data?.length">
            <td colspan="6" class="p-8 text-center text-slate-400 italic">Tidak ada data permohonan.</td>
          </tr>
        </tbody>
      </table>

      <!-- Navigasi Pagination -->
      <div v-if="pagination.last_page > 1" class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
        <span>Halaman {{ pagination.current_page }} dari {{ pagination.last_page }}</span>
        <div class="flex gap-1">
          <button
            @click="fetchPermohonan(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-3 py-1 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-40 disabled:hover:bg-transparent"
          >
            Sebelumnya
          </button>
          <button
            @click="fetchPermohonan(pagination.current_page + 1)"
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
const filterStatus = ref('');

const fetchPermohonan = async (page = 1) => {
  try {
    const res = await api.get('/pemohon/permohonan', { 
      params: { 
        status: filterStatus.value,
        page: page
      } 
    });
    pagination.value = res.data;
  } catch (err) {
    console.error(err);
  }
};

const statusClass = (status) => ({
  draft: 'bg-slate-100 text-slate-600',
  submitted: 'bg-blue-100 text-blue-700',
  revisi: 'bg-amber-100 text-amber-700',
  approved: 'bg-emerald-100 text-emerald-700',
  rejected: 'bg-rose-100 text-rose-700',
}[status] || 'bg-slate-100 text-slate-600');

onMounted(fetchPermohonan);
</script>