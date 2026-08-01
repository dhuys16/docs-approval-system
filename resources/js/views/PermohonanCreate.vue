<template>
  <div class="max-w-4xl mx-auto space-y-6">
    <!-- Header Navigasi -->
    <div class="flex items-center justify-between">
      <div>
        <router-link to="/permohonan" class="text-xs font-semibold text-indigo-600 hover:text-indigo-800 flex items-center gap-1 mb-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          Kembali ke Daftar Permohonan
        </router-link>
        <h1 class="text-2xl font-bold text-slate-800">Buat Permohonan Baru</h1>
        <p class="text-xs text-slate-500">Isi detail proyek dan unggah dokumen persyaratan Anda.</p>
      </div>
    </div>

    <!-- Alert Error -->
    <div v-if="errorMessage" class="p-4 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-xl flex items-center justify-between">
      <span>{{ errorMessage }}</span>
      <button @click="errorMessage = ''" class="font-bold">&times;</button>
    </div>

    <!-- Form Container -->
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

      <!-- File Upload Box -->
      <div>
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">
          Unggah Dokumen Lampiran (PDF, DOCX, Max 5MB)
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
            accept=".pdf,.doc,.docx,.zip"
          />

          <div v-if="!selectedFile" class="space-y-2">
            <svg class="w-10 h-10 mx-auto text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            <p class="text-xs font-semibold text-slate-700">Klik untuk pilih file atau tarik & lepas di sini</p>
            <p class="text-[10px] text-slate-400">Format yang didukung: PDF, DOCX, ZIP (Maksimal 5 MB)</p>
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
            <button
              type="button"
              @click.stop="selectedFile = null"
              class="p-1 text-slate-400 hover:text-rose-600 transition"
              title="Hapus file"
            >
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
          <!-- Button Simpan Draft -->
          <button
            type="button"
            @click="submitForm(false)"
            :disabled="submitting"
            class="px-4 py-2.5 border border-slate-300 hover:bg-slate-50 text-slate-700 text-xs font-bold rounded-lg transition disabled:opacity-50"
          >
            Simpan Draft
          </button>

          <!-- Button Submit Langsung -->
          <button
            type="button"
            @click="submitForm(true)"
            :disabled="submitting"
            class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow-sm transition disabled:opacity-50"
          >
            {{ submitting ? 'Memproses...' : 'Submit Permohonan' }}
          </button>
        </div>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../axios';

const router = useRouter();
const isDragging = ref(false);
const selectedFile = ref(null);
const submitting = ref(false);
const errorMessage = ref('');

const form = ref({
  judul_project: '',
  deskripsi: '',
});

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
    const res = await api.post('/pemohon/permohonan', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });
    
    // Redirect menggunakan nomor_permohonan
    router.push(`/permohonan/${res.data.data.nomor_permohonan}`);
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Gagal menyimpan permohonan. Periksa kembali inputan Anda.';
  } finally {
    submitting.value = false;
  }
};
</script>