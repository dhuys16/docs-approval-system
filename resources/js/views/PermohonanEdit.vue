<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Header Navigasi -->
    <div>
      <router-link to="/permohonan" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mb-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        Kembali ke Daftar Permohonan
      </router-link>
      <h1 class="text-2xl font-bold text-slate-800">Edit Permohonan</h1>
      <p class="text-xs text-slate-500">Ubah detail dan dokumen permohonan Anda, lalu simpan atau submit ulang.</p>
    </div>

    <!-- Loading -->
    <div v-if="loadingData" class="flex flex-col items-center justify-center py-16">
      <svg class="animate-spin h-10 w-10 text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <p class="text-gray-500 font-medium">Memuat data permohonan...</p>
    </div>

    <!-- Error loading -->
    <div v-else-if="loadError" class="text-center py-16 bg-red-50 rounded-lg border border-red-100">
      <p class="text-red-600 text-lg font-medium">{{ loadError }}</p>
      <router-link to="/permohonan" class="mt-4 inline-block px-6 py-2 bg-slate-600 text-white rounded hover:bg-slate-700 transition">
        Kembali ke Daftar
      </router-link>
    </div>

    <!-- Form -->
    <template v-else>
      <!-- Alert Error -->
      <div v-if="errorMessage" class="p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center justify-between">
        <span>{{ errorMessage }}</span>
        <button @click="errorMessage = ''" class="font-bold">&times;</button>
      </div>

      <!-- Info Status -->
      <div class="p-3 bg-slate-50 border border-slate-200 rounded-xl flex items-center gap-3 text-xs">
        <span class="text-slate-500">Status saat ini:</span>
        <span :class="statusBadge(permohonan.status)" class="px-2.5 py-1 rounded-full font-bold uppercase tracking-wide text-[10px]">
          {{ permohonan.status }}
        </span>
        <span class="text-slate-400">&middot;</span>
        <span class="text-slate-500 font-medium">{{ permohonan.nomor_permohonan }}</span>
      </div>

      <form @submit.prevent="handleSubmit" class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-6">
        <!-- Judul Project -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Judul Project <span class="text-rose-500">*</span>
          </label>
          <input
            v-model="form.judul_project"
            type="text"
            required
            placeholder="Contoh: Pengajuan Prototyping Sistem Monitoring Sensor IoT"
            class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
          />
        </div>

        <!-- Deskripsi -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Deskripsi Proyek
          </label>
          <textarea
            v-model="form.deskripsi"
            rows="4"
            placeholder="Jelaskan secara singkat latar belakang dan tujuan proyek Anda..."
            class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
          ></textarea>
        </div>

        <!-- Dokumen Lama -->
        <div v-if="permohonan.dokumen?.length">
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Dokumen Saat Ini
          </label>
          <div v-for="doc in permohonan.dokumen" :key="doc.id" class="flex items-center gap-3 p-3 bg-slate-50 border border-slate-200 rounded-lg">
            <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg font-bold text-xs uppercase">
              {{ doc.nama_file?.split('.').pop() || 'FILE' }}
            </div>
            <div class="flex-1">
              <p class="text-xs font-bold text-slate-800">{{ doc.nama_file }}</p>
              <p class="text-[10px] text-slate-400">{{ doc.file_size }} KB</p>
            </div>
            <a :href="`/storage/${doc.file_path}`" target="_blank" class="text-xs text-indigo-600 font-semibold hover:underline">Lihat</a>
          </div>
        </div>

        <!-- Upload Dokumen Baru -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
            Ganti / Tambah Dokumen Baru (PDF, DOCX, Max 5MB)
          </label>
          <div
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            :class="isDragging ? 'border-indigo-500 bg-indigo-50/50' : 'border-slate-300 hover:border-slate-400'"
            class="border-2 border-dashed rounded-xl p-6 text-center transition cursor-pointer bg-slate-50 relative"
            @click="$refs.fileInput.click()"
          >
            <input
              ref="fileInput"
              type="file"
              @change="handleFileSelect"
              class="hidden"
              accept=".pdf,.doc,.docx,.jpg,.png,.zip"
            />
            <div v-if="!selectedFile" class="space-y-2">
              <svg class="w-10 h-10 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
              <p class="text-xs font-semibold text-slate-700">Klik untuk pilih file baru atau tarik & lepas di sini</p>
              <p class="text-[10px] text-slate-400">Format: PDF, DOCX, JPG, PNG, ZIP (Maks 5 MB)</p>
            </div>
            <div v-else class="flex items-center justify-between bg-white p-3 rounded-lg border border-slate-200 shadow-sm text-left">
              <div class="flex items-center gap-3">
                <div class="p-2 bg-indigo-50 text-indigo-600 rounded-lg font-bold text-xs uppercase">
                  {{ selectedFile.name.split('.').pop() }}
                </div>
                <div>
                  <p class="text-xs font-bold text-slate-800 truncate max-w-md">{{ selectedFile.name }}</p>
                  <p class="text-[10px] text-slate-400">{{ (selectedFile.size / 1024).toFixed(1) }} KB</p>
                </div>
              </div>
              <button type="button" @click.stop="selectedFile = null" class="p-1 text-slate-400 hover:text-rose-600 transition" title="Hapus file">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Action Footer -->
        <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
          <router-link to="/permohonan" class="px-4 py-2.5 text-xs font-semibold text-slate-600 hover:text-slate-800 transition">
            Batal
          </router-link>
          <div class="flex items-center gap-3">
            <button
              type="button"
              @click="submitForm(false)"
              :disabled="submitting"
              class="px-4 py-2.5 border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-lg transition disabled:opacity-50"
            >
              Simpan Draft
            </button>
            <button
              type="button"
              @click="submitForm(true)"
              :disabled="submitting"
              class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition disabled:opacity-50"
            >
              {{ submitting ? 'Memproses...' : 'Submit Ulang' }}
            </button>
          </div>
        </div>
      </form>
    </template>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../axios';

const route = useRoute();
const router = useRouter();

const loadingData = ref(true);
const loadError = ref('');
const isDragging = ref(false);
const selectedFile = ref(null);
const submitting = ref(false);
const errorMessage = ref('');
const permohonan = ref({});

const form = ref({
  judul_project: '',
  deskripsi: '',
});

const fetchPermohonan = async () => {
  loadingData.value = true;
  try {
    const res = await api.get(`/pemohon/permohonan/${route.params.nomor_permohonan}`);
    const data = res.data.data ? res.data.data : res.data;
    permohonan.value = data;
    form.value.judul_project = data.judul_project || '';
    form.value.deskripsi = data.deskripsi || '';
    if (!['draft', 'revisi'].includes(data.status)) {
      loadError.value = `Permohonan dengan status "${data.status}" tidak dapat diedit.`;
    }
  } catch (err) {
    loadError.value = err.response?.data?.message || 'Gagal memuat data permohonan.';
  } finally {
    loadingData.value = false;
  }
};

const handleFileSelect = (event) => {
  const file = event.target.files[0];
  if (file) validateAndSetFile(file);
};

const handleDrop = (event) => {
  isDragging.value = false;
  const file = event.dataTransfer.files[0];
  if (file) validateAndSetFile(file);
};

const validateAndSetFile = (file) => {
  if (file.size > 5 * 1024 * 1024) {
    errorMessage.value = 'Ukuran file melebihi batas maksimal 5MB.';
    return;
  }
  selectedFile.value = file;
  errorMessage.value = '';
};

const submitForm = async (isSubmitDirectly) => {
  if (!form.value.judul_project.trim()) {
    errorMessage.value = 'Judul project wajib diisi.';
    return;
  }

  submitting.value = true;
  errorMessage.value = '';

  const formData = new FormData();
  formData.append('judul_project', form.value.judul_project);
  formData.append('deskripsi', form.value.deskripsi);
  formData.append('is_submit', isSubmitDirectly ? '1' : '0');
  if (selectedFile.value) {
    formData.append('dokumen', selectedFile.value);
  }

  try {
    await api.post(`/pemohon/permohonan/${route.params.nomor_permohonan}`, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    router.push(`/permohonan/${route.params.nomor_permohonan}`);
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Gagal menyimpan permohonan. Periksa kembali inputan Anda.';
  } finally {
    submitting.value = false;
  }
};

const statusBadge = (status) => ({
  draft: 'bg-slate-100 text-slate-600',
  submitted: 'bg-blue-100 text-blue-700',
  revisi: 'bg-amber-100 text-amber-700',
  approved: 'bg-emerald-100 text-emerald-700',
  rejected: 'bg-rose-100 text-rose-700',
}[status] || 'bg-slate-100 text-slate-600');

onMounted(fetchPermohonan);
</script>
