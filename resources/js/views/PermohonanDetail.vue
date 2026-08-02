<template>
  <div class="max-w-5xl mx-auto p-6 bg-white shadow-md rounded-lg mt-8">
    <div class="flex justify-between items-center border-b pb-4 mb-6">
      <h2 class="text-2xl font-bold text-gray-800">Detail Permohonan Dokumen</h2>
      <button @click="goBack" class="px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300 transition">
        &larr; Kembali
      </button>
    </div>

    <div v-if="loading" class="flex flex-col items-center justify-center py-16">
      <svg class="animate-spin h-10 w-10 text-blue-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <p class="text-gray-500 font-medium">Memuat data permohonan...</p>
    </div>

    <div v-else-if="error" class="text-center py-16 bg-red-50 rounded-lg border border-red-100">
      <p class="text-red-600 text-lg font-medium">{{ error }}</p>
      <button @click="fetchDetail" class="mt-4 px-6 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
        Coba Lagi
      </button>
    </div>

    <div v-else-if="permohonan">
      <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        
        <!-- Kolom Kiri: Informasi Data -->
        <div>
          <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Informasi Data</h3>
          <div class="space-y-4">
            <div>
              <span class="block text-sm text-gray-500">Judul Project / Permohonan</span>
              <span class="font-medium text-gray-900 text-lg">{{ permohonan.judul_project || permohonan.nama_project || 'Tidak ada judul' }}</span>
            </div>
            
            <div>
              <span class="block text-sm text-gray-500">Status Saat Ini</span>
              <span class="inline-block mt-1 px-3 py-1 text-sm font-semibold rounded-full"
                    :class="{
                      'bg-yellow-100 text-yellow-800': permohonan.status === 'Draft' || permohonan.status === 'Pending',
                      'bg-blue-100 text-blue-800': permohonan.status === 'Revisi',
                      'bg-green-100 text-green-800': permohonan.status === 'Approved' || permohonan.status === 'Disetujui',
                      'bg-red-100 text-red-800': permohonan.status === 'Rejected' || permohonan.status === 'Ditolak'
                    }">
                {{ permohonan.status }}
              </span>
            </div>

            <div>
              <span class="block text-sm text-gray-500">Tanggal Pengajuan</span>
              <span class="font-medium text-gray-900">
                {{ permohonan.created_at ? new Date(permohonan.created_at).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-' }}
              </span>
            </div>

            <div v-if="permohonan.catatan">
              <span class="block text-sm text-gray-500 mb-1">Catatan Penilai</span>
              <div class="bg-gray-50 p-4 rounded border text-gray-700 text-sm">
                {{ permohonan.catatan }}
              </div>
            </div>
          </div>
        </div>

        <!-- Kolom Kanan: Pratinjau Dokumen -->
        <div>
          <h3 class="text-lg font-semibold text-gray-700 border-b pb-2 mb-4">Dokumen Lampiran</h3>
          
          <div v-if="permohonan.dokumen && permohonan.dokumen.length > 0" class="space-y-4">
             <!-- Pastikan layout gambar menggunakan contain agar dokumen tidak terpotong (cropped) saat ditampilkan -->
             <div v-for="doc in permohonan.dokumen" :key="doc.id" class="border rounded-lg p-2 bg-gray-50 h-80 flex flex-col items-center justify-center relative mb-4">
                <img 
                  v-if="isImage(doc.file_path || doc.nama_file)" 
                  :src="`/storage/${doc.file_path}`" 
                  alt="Dokumen Permohonan" 
                  class="w-full h-full object-contain rounded" 
                />
                
                <div v-else class="text-center w-full">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 mx-auto text-blue-500 mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                  <p class="text-gray-600 mb-2 truncate px-2 font-medium">{{ doc.nama_file || 'Dokumen' }}</p>
                  <a :href="`/storage/${doc.file_path}`" target="_blank" class="px-4 py-2 mt-2 inline-block bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                    Unduh / Lihat Dokumen
                  </a>
                </div>
             </div>
          </div>
          
          <div v-else class="border border-dashed border-gray-300 rounded-lg p-10 h-64 flex items-center justify-center bg-gray-50 text-gray-500">
            Tidak ada dokumen yang dilampirkan.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../axios.js'; 

const route = useRoute();
const router = useRouter();

// State management
const permohonan = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchDetail = async () => {
  loading.value = true;
  error.value = null;
  
  try {
    const response = await api.get(`/pemohon/permohonan/${route.params.nomor_permohonan}`);
    permohonan.value = response.data.data ? response.data.data : response.data;
    
    console.log("Berhasil! Data Permohonan:", permohonan.value);
  } catch (err) {
    console.error("Gagal mengambil data:", err.response?.status, err.response?.data, err);
    error.value = err.response?.data?.message || `Gagal mengambil data (HTTP ${err.response?.status || 'unknown'})`;
  } finally {
    loading.value = false;
  }
};

const isImage = (url) => {
  if (!url) return false;
  return /\.(jpeg|jpg|png|webp)$/i.test(url);
};

const goBack = () => {
  router.back();
};
onMounted(() => {
  fetchDetail();
});
</script>