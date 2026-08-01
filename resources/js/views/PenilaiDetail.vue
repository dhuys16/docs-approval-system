<template>
  <div class="max-w-5xl mx-auto p-6 bg-white shadow-md rounded-lg mt-8">
    <div class="flex justify-between items-center border-b pb-4 mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Detail & Penilaian Permohonan</h2>
      <button @click="goBack" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
        &larr; Kembali
      </button>
    </div>

    <!-- State Loading -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-16">
      <svg class="animate-spin h-10 w-10 text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <p class="text-gray-500 font-medium">Memuat data permohonan...</p>
    </div>

    <!-- State Error -->
    <div v-else-if="error" class="text-center py-16 bg-red-50 rounded-lg border border-red-100">
      <p class="text-red-600 text-lg font-medium">{{ error }}</p>
      <button @click="fetchDetail" class="mt-4 px-6 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
        Coba Lagi
      </button>
    </div>

    <!-- State Sukses -->
    <div v-else-if="permohonan">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Kolom Kiri: Informasi Data -->
        <div>
          <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Informasi Permohonan</h3>
          <div class="space-y-4">
            <div>
              <span class="block text-sm text-gray-500">Nomor Permohonan</span>
              <span class="font-bold text-indigo-600 text-lg">{{ permohonan.nomor_permohonan }}</span>
            </div>
            <div>
              <span class="block text-sm text-gray-500">Judul Project</span>
              <span class="font-medium text-gray-900 text-lg">{{ permohonan.judul_project || 'Tidak ada judul' }}</span>
            </div>
            <div>
              <span class="block text-sm text-gray-500">Deskripsi</span>
              <span class="font-medium text-gray-900">{{ permohonan.deskripsi || '-' }}</span>
            </div>
            <div>
              <span class="block text-sm text-gray-500">Pemohon</span>
              <span class="font-medium text-gray-900">{{ permohonan.pemohon?.name || '-' }}</span>
              <span class="text-xs text-gray-400 ml-1">{{ permohonan.pemohon?.email }}</span>
            </div>
            <div>
              <span class="block text-sm text-gray-500">Status Saat Ini</span>
              <span class="inline-block mt-1 px-3 py-1 text-sm font-semibold rounded-full"
                    :class="statusBadgeClass(permohonan.status)">
                {{ permohonan.status }}
              </span>
            </div>
            <div>
              <span class="block text-sm text-gray-500">Tanggal Pengajuan</span>
              <span class="font-medium text-gray-900">
                {{ permohonan.created_at ? new Date(permohonan.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Kolom Kanan: Dokumen Lampiran -->
        <div>
          <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Dokumen Lampiran</h3>
          
          <div v-if="permohonan.dokumen?.length" class="space-y-3">
            <div v-for="doc in permohonan.dokumen" :key="doc.id" class="border rounded-lg p-3 bg-gray-50">
              <div v-if="isImage(doc.file_path)" class="mb-2">
                <img :src="`/storage/${doc.file_path}`" :alt="doc.nama_file" class="w-full h-64 object-contain rounded" />
              </div>
              <div class="flex items-center justify-between">
                <div>
                  <p class="font-medium text-gray-800 text-sm">{{ doc.nama_file }}</p>
                  <p class="text-xs text-gray-400">{{ doc.file_size }} KB</p>
                </div>
                <a :href="`/storage/${doc.file_path}`" target="_blank" class="px-3 py-1.5 bg-indigo-600 text-white text-xs font-bold rounded-lg hover:bg-indigo-700 transition">
                  Lihat / Unduh
                </a>
              </div>
            </div>
          </div>
          
          <div v-else class="border border-dashed border-gray-300 rounded-lg p-10 h-40 flex items-center justify-center bg-gray-50 text-gray-500">
            Tidak ada dokumen yang dilampirkan.
          </div>
        </div>
      </div>

      <!-- Riwayat Penilaian -->
      <div v-if="permohonan.riwayat?.length" class="mt-8">
        <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Riwayat Penilaian</h3>
        <div class="space-y-3">
          <div v-for="riwayat in permohonan.riwayat" :key="riwayat.id" class="flex items-start gap-3 p-3 bg-slate-50 rounded-lg border border-slate-100">
            <div class="shrink-0 mt-1">
              <span class="inline-block w-2.5 h-2.5 rounded-full" :class="{
                'bg-blue-500': riwayat.status_baru === 'submitted',
                'bg-emerald-500': riwayat.status_baru === 'approved',
                'bg-amber-500': riwayat.status_baru === 'revisi',
                'bg-rose-500': riwayat.status_baru === 'rejected',
                'bg-slate-400': riwayat.status_baru === 'draft',
              }"></span>
            </div>
            <div class="flex-1">
              <div class="flex items-center gap-2 text-xs text-gray-500">
                <span class="font-semibold text-gray-700">{{ riwayat.penilai?.name || 'Sistem' }}</span>
                <span>&middot;</span>
                <span>{{ new Date(riwayat.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }) }}</span>
              </div>
              <p class="text-xs mt-0.5">
                <span class="text-gray-400">{{ riwayat.status_sebelumnya || '-' }}</span>
                <span class="mx-1">&rarr;</span>
                <span class="font-bold" :class="{
                  'text-blue-600': riwayat.status_baru === 'submitted',
                  'text-emerald-600': riwayat.status_baru === 'approved',
                  'text-amber-600': riwayat.status_baru === 'revisi',
                  'text-rose-600': riwayat.status_baru === 'rejected',
                  'text-slate-600': riwayat.status_baru === 'draft',
                }">{{ riwayat.status_baru }}</span>
              </p>
              <p v-if="riwayat.catatan" class="text-sm text-gray-600 mt-1">{{ riwayat.catatan }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Panel Penilaian (hanya tampil jika status masih bisa di-review) -->
      <div v-if="canReview" class="mt-8 border-t pt-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">
          <svg class="w-5 h-5 inline-block mr-1 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Berikan Penilaian
        </h3>

        <!-- Notifikasi Sukses -->
        <div v-if="reviewSuccess" class="mb-4 p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-700 text-sm font-medium flex items-center gap-2">
          <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
          </svg>
          {{ reviewSuccess }}
        </div>

        <!-- Notifikasi Error -->
        <div v-if="reviewError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-700 text-sm font-medium">
          {{ reviewError }}
        </div>

        <form @submit.prevent="submitReview" class="space-y-4 bg-slate-50 p-5 rounded-xl border border-slate-200">
          <!-- Pilihan Keputusan -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-2">Keputusan <span class="text-rose-500">*</span></label>
            <div class="flex gap-3">
              <label
                v-for="option in reviewOptions"
                :key="option.value"
                class="flex-1 cursor-pointer"
              >
                <input type="radio" v-model="reviewForm.status" :value="option.value" class="sr-only peer" />
                <div class="text-center px-4 py-3 rounded-lg border-2 transition-all text-sm font-bold peer-checked:ring-2 peer-checked:ring-offset-1"
                     :class="[
                       reviewForm.status === option.value ? option.activeClass : 'border-slate-200 bg-white text-slate-500 hover:bg-slate-50'
                     ]">
                  <span class="block text-lg mb-0.5">{{ option.icon }}</span>
                  {{ option.label }}
                </div>
              </label>
            </div>
          </div>

          <!-- Catatan -->
          <div>
            <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Penilaian <span class="text-rose-500">*</span></label>
            <textarea
              v-model="reviewForm.catatan"
              rows="3"
              required
              minlength="5"
              placeholder="Tulis catatan penilaian Anda (minimal 5 karakter)..."
              class="w-full border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
            ></textarea>
          </div>

          <!-- Tombol Submit -->
          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="!reviewForm.status || !reviewForm.catatan || submitting"
              class="px-6 py-2.5 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition disabled:opacity-40 disabled:cursor-not-allowed text-sm"
            >
              <span v-if="submitting">Menyimpan...</span>
              <span v-else>Kirim Penilaian</span>
            </button>
          </div>
        </form>
      </div>

      <!-- Info jika permohonan sudah selesai dinilai -->
      <div v-else-if="permohonan.status === 'approved' || permohonan.status === 'rejected'" class="mt-8 border-t pt-6">
        <div class="p-4 rounded-lg text-sm font-medium flex items-center gap-2"
             :class="permohonan.status === 'approved' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200'">
          <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Permohonan ini sudah <strong class="ml-1">{{ permohonan.status === 'approved' ? 'disetujui' : 'ditolak' }}</strong>.
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../axios.js';

const route = useRoute();
const router = useRouter();

// State management
const permohonan = ref(null);
const loading = ref(true);
const error = ref(null);

// Review form
const reviewForm = ref({
  status: '',
  catatan: '',
});
const submitting = ref(false);
const reviewSuccess = ref('');
const reviewError = ref('');

const reviewOptions = [
  {
    value: 'approved',
    label: 'Setujui',
    icon: '✅',
    activeClass: 'border-emerald-500 bg-emerald-50 text-emerald-700 ring-emerald-500',
  },
  {
    value: 'revisi',
    label: 'Revisi',
    icon: '🔄',
    activeClass: 'border-amber-500 bg-amber-50 text-amber-700 ring-amber-500',
  },
  {
    value: 'rejected',
    label: 'Tolak',
    icon: '❌',
    activeClass: 'border-rose-500 bg-rose-50 text-rose-700 ring-rose-500',
  },
];

// Permohonan bisa di-review jika statusnya submitted atau revisi (re-submit)
const canReview = computed(() => {
  if (!permohonan.value) return false;
  return ['submitted'].includes(permohonan.value.status);
});

const fetchDetail = async () => {
  loading.value = true;
  error.value = null;

  try {
    const response = await api.get(`/penilai/permohonan/${route.params.nomor_permohonan}`);
    permohonan.value = response.data.data ? response.data.data : response.data;
    console.log("Data Permohonan (Penilai):", permohonan.value);
  } catch (err) {
    console.error("Gagal mengambil data:", err.response?.status, err.response?.data, err);
    error.value = err.response?.data?.message || `Gagal mengambil data (HTTP ${err.response?.status || 'unknown'})`;
  } finally {
    loading.value = false;
  }
};

const submitReview = async () => {
  if (!reviewForm.value.status || !reviewForm.value.catatan) return;

  submitting.value = true;
  reviewSuccess.value = '';
  reviewError.value = '';

  try {
    const response = await api.post(
      `/penilai/permohonan/${route.params.nomor_permohonan}/review`,
      {
        status: reviewForm.value.status,
        catatan: reviewForm.value.catatan,
      }
    );

    reviewSuccess.value = response.data.message || 'Penilaian berhasil disimpan!';
    reviewForm.value = { status: '', catatan: '' };

    // Refresh data permohonan agar status & riwayat ter-update
    await fetchDetail();
  } catch (err) {
    console.error("Gagal submit review:", err);
    reviewError.value = err.response?.data?.message || 'Gagal menyimpan penilaian.';
  } finally {
    submitting.value = false;
  }
};

const statusBadgeClass = (status) => ({
  draft: 'bg-slate-100 text-slate-600',
  submitted: 'bg-blue-100 text-blue-700',
  revisi: 'bg-amber-100 text-amber-700',
  approved: 'bg-emerald-100 text-emerald-700',
  rejected: 'bg-rose-100 text-rose-700',
}[status] || 'bg-slate-100 text-slate-600');

const isImage = (path) => {
  if (!path) return false;
  return /\.(jpeg|jpg|png|webp)$/i.test(path);
};

const goBack = () => {
  router.back();
};

onMounted(() => {
  fetchDetail();
});
</script>
