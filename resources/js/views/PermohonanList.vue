<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Daftar Permohonan Saya</h1>
        <p class="text-xs text-slate-500">Kelola draft dan pengajuan permohonan proyek Anda.</p>
      </div>
      <button 
        @click="openModal()" 
        class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow transition flex items-center gap-1.5"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Permohonan Baru
      </button>
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

              <!-- Tombol Edit Modal (Hanya jika status Draft / Revisi) -->
              <button
                v-if="['draft', 'revisi'].includes(item.status)"
                @click="openModal(item)"
                class="px-3 py-1.5 border border-slate-300 text-slate-700 font-semibold rounded-lg hover:bg-slate-100 transition inline-block text-[11px]"
              >
                Edit
              </button>
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

    <!-- Modal Form (Create / Edit Quick Modal) -->
    <div v-if="showModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-xl max-w-lg w-full p-6 space-y-4 shadow-2xl">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
          <h3 class="text-base font-bold text-slate-800">
            {{ isEdit ? 'Edit Permohonan' : 'Buat Permohonan Baru' }}
          </h3>
          <button @click="showModal = false" class="text-slate-400 hover:text-slate-600 font-bold">&times;</button>
        </div>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Judul Project <span class="text-rose-500">*</span></label>
            <input v-model="form.judul_project" required type="text" placeholder="Masukkan judul project" class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none" />
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Deskripsi</label>
            <textarea v-model="form.deskripsi" rows="3" placeholder="Masukkan deskripsi permohonan..." class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"></textarea>
          </div>

          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Upload Dokumen (Max 5MB)</label>
            <input type="file" @change="e => form.dokumen = e.target.files[0]" class="w-full text-xs text-slate-500 border border-slate-300 rounded-lg p-2" />
          </div>

          <div class="flex items-center gap-2 pt-1">
            <input type="checkbox" id="is_submit" v-model="form.is_submit" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500" />
            <label for="is_submit" class="text-xs font-semibold text-slate-700 cursor-pointer">Langsung Submit untuk Direview?</label>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t border-slate-100">
            <button type="button" @click="showModal = false" class="px-4 py-2 text-xs border border-slate-300 font-semibold text-slate-600 rounded-lg hover:bg-slate-50">Batal</button>
            <button type="submit" :disabled="submitting" class="px-5 py-2 text-xs bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition disabled:opacity-50">
              {{ submitting ? 'Menyimpan...' : 'Simpan' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../axios';

const pagination = ref({});
const filterStatus = ref('');
const showModal = ref(false);
const isEdit = ref(false);
const selectedId = ref(null);
const submitting = ref(false);

const form = ref({
  judul_project: '',
  deskripsi: '',
  is_submit: true,
  dokumen: null,
});

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

const openModal = (item = null) => {
  if (item) {
    isEdit.value = true;
    selectedId.value = item.id;
    form.value = {
      judul_project: item.judul_project,
      deskripsi: item.deskripsi || '',
      is_submit: true,
      dokumen: null,
    };
  } else {
    isEdit.value = false;
    selectedId.value = null;
    form.value = { judul_project: '', deskripsi: '', is_submit: true, dokumen: null };
  }
  showModal.value = true;
};

const submitForm = async () => {
  submitting.value = true;
  const formData = new FormData();
  formData.append('judul_project', form.value.judul_project);
  formData.append('deskripsi', form.value.deskripsi);
  formData.append('is_submit', form.value.is_submit ? '1' : '0');
  if (form.value.dokumen) formData.append('dokumen', form.value.dokumen);

  try {
    if (isEdit.value) {
      await api.post(`/pemohon/permohonan/${selectedId.value}`, formData);
    } else {
      await api.post('/pemohon/permohonan', formData);
    }
    showModal.value = false;
    fetchPermohonan();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menyimpan data.');
  } finally {
    submitting.value = false;
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