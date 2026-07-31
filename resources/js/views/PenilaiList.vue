<template>
  <div class="space-y-6">
    <div>
      <h1 class="text-2xl font-bold text-gray-800">Verifikasi Permohonan Masuk</h1>
      <p class="text-sm text-gray-500">Daftar permohonan dokumen kelayakan dari seluruh pemohon</p>
    </div>

    <!-- Tabel Verifikasi Penilai -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden">
      <table class="w-full text-left text-sm text-gray-600">
        <thead class="bg-gray-50 text-gray-700 font-semibold uppercase text-xs border-b">
          <tr>
            <th class="px-4 py-3">No. Permohonan</th>
            <th class="px-4 py-3">Pemohon</th>
            <th class="px-4 py-3">Judul Project</th>
            <th class="px-4 py-3">Status Saat Ini</th>
            <th class="px-4 py-3 text-center">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="loading">
            <td colspan="5" class="text-center py-8 text-gray-400">Memuat data dari database (10.000 data)...</td>
          </tr>
          <tr v-for="item in items" :key="item.id" class="hover:bg-gray-50">
            <td class="px-4 py-3 font-semibold text-gray-800">{{ item.nomor_permohonan }}</td>
            <td class="px-4 py-3 font-medium">{{ item.pemohon?.name || '-' }}</td>
            <td class="px-4 py-3">{{ item.judul_project }}</td>
            <td class="px-4 py-3">
              <span :class="statusBadge(item.status)" class="text-xs font-bold px-2 py-1 rounded uppercase">
                {{ item.status }}
              </span>
            </td>
            <td class="px-4 py-3 text-center">
              <button
                @click="openReviewModal(item)"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-xs px-3 py-1.5 rounded transition"
              >
                Review
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Modal Review Decision -->
    <div v-if="selectedItem" class="fixed inset-0 bg-black/50 flex items-center justify-center p-4 z-50">
      <div class="bg-white rounded-lg p-6 max-w-lg w-full space-y-4">
        <h3 class="text-lg font-bold text-gray-800">Review: {{ selectedItem.nomor_permohonan }}</h3>
        <p class="text-xs text-gray-500">Pemohon: {{ selectedItem.pemohon?.name }}</p>

        <form @submit.prevent="submitReview" class="space-y-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Keputusan</label>
            <select
              v-model="reviewForm.status"
              required
              class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
            >
              <option value="approved">Setujui (Approved)</option>
              <option value="revisi">Minta Revisi</option>
              <option value="rejected">Tolak (Rejected)</option>
            </select>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Catatan Penilai (Wajib)</label>
            <textarea
              v-model="reviewForm.catatan"
              required
              rows="3"
              placeholder="Berikan alasan atau detail perbaikan..."
              class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500"
            ></textarea>
          </div>

          <div class="flex justify-end gap-2 pt-4 border-t">
            <button
              type="button"
              @click="selectedItem = null"
              class="px-4 py-2 border rounded-md text-sm text-gray-600 hover:bg-gray-50"
            >
              Batal
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700 disabled:opacity-50"
            >
              Simpan Keputusan
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
const selectedItem = ref(null);
const submitting = ref(false);

const reviewForm = ref({
  status: 'approved',
  catatan: '',
});

const fetchPenilaiData = async () => {
  loading.value = true;
  try {
    const res = await api.get('/penilai/permohonan');
    items.value = res.data.data;
  } catch (err) {
    console.error(err);
  } finally {
    loading.value = false;
  }
};

const openReviewModal = (item) => {
  selectedItem.value = item;
  reviewForm.value = { status: 'approved', catatan: '' };
};

const submitReview = async () => {
  submitting.value = true;
  try {
    await api.post(`/penilai/permohonan/${selectedItem.value.id}/review`, reviewForm.value);
    selectedItem.value = null;
    fetchPenilaiData();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menyimpan keputusan.');
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

onMounted(() => {
  fetchPenilaiData();
});
</script>