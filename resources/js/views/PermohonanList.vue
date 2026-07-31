<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Daftar Permohonan Saya</h1>
        <p class="text-sm text-gray-500">Kelola dokumen permohonan kelayakan Anda</p>
      </div>
      <button
        @click="openModal()"
        class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium px-4 py-2 rounded-md text-sm shadow-sm transition"
      >
        + Buat Permohonan Baru
      </button>
    </div>

    <!-- Tabel Permohonan -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
      <table class="w-full text-left text-sm text-gray-600">
        <thead class="bg-gray-50 text-gray-700 font-semibold uppercase text-xs border-b">
          <tr>
            <th class="px-4 py-3">No. Permohonan</th>
            <th class="px-4 py-3">Judul Project</th>
            <th class="px-4 py-3">Dokumen</th>
            <th class="px-4 py-3">Status</th>
            <th class="px-4 py-3">Tanggal</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="loading">
            <td colspan="5" class="text-center py-8 text-gray-400">Memuat data permohonan...</td>
          </tr>
          <tr v-else-if="items.length === 0">
            <td colspan="5" class="text-center py-8 text-gray-400">Belum ada permohonan yang dibuat.</td>
          </tr>
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-semibold text-gray-800">{{ item.nomor_permohonan }}</td>
            <td class="px-4 py-3">{{ item.judul_project }}</td>
            <td class="px-4 py-3">
              <span v-if="item.dokumen.length > 0" class="text-xs bg-indigo-50 text-indigo-600 px-2 py-1 rounded">
                {{ item.dokumen[0].nama_file }}
              </span>
              <span v-else class="text-xs text-gray-400">Tanpa File</span>
            </td>
            <td class="px-4 py-3">
              <span :class="statusBadge(item.status)" class="text-xs font-bold px-2 py-1 rounded uppercase">
                {{ item.status }}
              </span>
            </td>
            <td class="px-4 py-3 text-xs text-gray-500">{{ formatDate(item.created_at) }}</td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Form (Tambah / Edit) -->
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg p-6 max-w-lg w-full space-y-4">
        <h3 class="text-lg font-bold text-gray-800">Buat Permohonan Baru</h3>

        <form @submit.prevent="submitForm" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Judul Project</label>
            <input
              v-model="form.judul_project"
              type="text"
              required
              class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
            <textarea
              v-model="form.deskripsi"
              rows="3"
              class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
            ></textarea>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Upload Dokumen (PDF/Gambar Max 5MB)</label>
            <input
              type="file"
              @change="handleFileUpload"
              class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
            />
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t">
            <button
              type="button"
              @click="showModal = false"
              class="px-4 py-2 border rounded-md text-sm text-gray-600 hover:bg-gray-50"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700 disabled:opacity-50"
            >
              {{ submitting ? 'Menyimpan...' : 'Kirim Permohonan' }}
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

const items = ref([]);
const loading = ref(true);
const showModal = ref(false);
const submitting = ref(false);

const form = ref({
  judul_project: '',
  deskripsi: '',
  dokumen: null,
});

const fetchPermohonan = async () => {
  loading.value = true;
  try {
    const res = await api.get('/pemohon/permohonan');
    items.value = res.data.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const openModal = () => {
  form.value = { judul_project: '', deskripsi: '', dokumen: null };
  showModal.value = true;
};

const handleFileUpload = (e) => {
  form.value.dokumen = e.target.files[0];
};

const submitForm = async () => {
  submitting.value = true;
  const formData = new FormData();
  formData.append('judul_project', form.value.judul_project);
  formData.append('deskripsi', form.value.deskripsi);
  formData.append('is_submit', 1);
  if (form.value.dokumen) {
    formData.append('dokumen', form.value.dokumen);
  }

  try {
    await api.post('/pemohon/permohonan', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    showModal.value = false;
    fetchPermohonan();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menyimpan permohonan');
  } finally {
    submitting.value = false;
  }
};

const statusBadge = (status) => {
  const maps = {
    draft: 'bg-gray-100 text-gray-600',
    submitted: 'bg-blue-100 text-blue-700',
    revisi: 'bg-yellow-100 text-yellow-700',
    approved: 'bg-green-100 text-green-700',
    rejected: 'bg-red-100 text-red-700',
  };
  return maps[status] || 'bg-gray-100 text-gray-600';
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('id-ID');
};

onMounted(() => {
  fetchPermohonan();
});
</script>