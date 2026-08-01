<template>
  <div class="space-y-6 max-w-4xl mx-auto pb-12">
    <!-- Header Navigation -->
    <div class="flex items-center justify-between">
      <button @click="$router.back()" class="text-xs font-bold text-slate-500 hover:text-indigo-600 flex items-center gap-1">
        &larr; Kembali
      </button>
      <span v-if="permohonan.status" :class="statusClass(permohonan.status)" class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide">
        {{ permohonan.status }}
      </span>
    </div>

    <!-- Informasi Permohonan -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
      <div class="border-b border-slate-100 pb-3">
        <span class="text-[10px] font-mono font-bold text-indigo-600 uppercase tracking-wider">No. Permohonan</span>
        <h1 class="text-xl font-bold text-slate-800">{{ permohonan.nomor_permohonan || '-' }}</h1>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs">
        <div>
          <span class="text-slate-400 font-semibold block uppercase">Judul Project</span>
          <p class="text-slate-800 font-bold text-sm">{{ permohonan.judul_project || '-' }}</p>
        </div>
        <div>
          <span class="text-slate-400 font-semibold block uppercase">Pemohon</span>
          <p class="text-slate-800 font-bold">{{ permohonan.pemohon?.name || '-' }}</p>
          <p class="text-slate-400 text-[11px]">{{ permohonan.pemohon?.email || '' }}</p>
        </div>
      </div>

      <div>
        <span class="text-slate-400 font-semibold block text-xs uppercase mb-1">Deskripsi</span>
        <p class="text-slate-700 text-xs leading-relaxed bg-slate-50 p-3 rounded-lg border border-slate-100">
          {{ permohonan.deskripsi || 'Tidak ada deskripsi.' }}
        </p>
      </div>

      <!-- Lampiran Dokumen -->
      <div>
        <span class="text-slate-400 font-semibold block text-xs uppercase mb-1">Lampiran Dokumen</span>
        <div v-if="permohonan.dokumen?.length" class="flex items-center gap-2">
          <a
            :href="`/storage/${permohonan.dokumen[0].file_path}`"
            target="_blank"
            class="px-3 py-2 bg-indigo-50 border border-indigo-200 text-indigo-600 rounded-lg text-xs font-bold hover:bg-indigo-100 transition inline-flex items-center gap-1.5"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            {{ permohonan.dokumen[0].nama_file }}
          </a>
        </div>
        <span v-else class="text-xs text-slate-400 italic">Tidak ada dokumen dilampirkan.</span>
      </div>
    </div>

    <!-- PANEL KHUSUS PENILAI -->
    <div v-if="isPenilai" class="bg-white rounded-xl border border-indigo-200 shadow-md p-6 space-y-4">
      <div class="border-b border-slate-100 pb-2">
        <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
          <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          Form Keputusan Penilai
        </h3>
        <p class="text-[11px] text-slate-400">Tentukan keputusan status permohonan dan berikan catatan penilaian.</p>
      </div>

      <form @submit.prevent="submitReview" class="space-y-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Pilih Status Keputusan</label>
          <select v-model="reviewForm.status" class="w-full border border-slate-300 rounded-lg p-2.5 text-xs font-medium focus:ring-2 focus:ring-indigo-500 focus:outline-none">
            <option value="approved">Approved (Setujui)</option>
            <option value="revisi">Revisi (Minta Perbaikan)</option>
            <option value="rejected">Rejected (Tolak)</option>
          </select>
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Penilaian (Wajib, Min 5 Karakter)</label>
          <textarea
            v-model="reviewForm.catatan"
            rows="4"
            required
            placeholder="Berikan alasan keputusan atau petunjuk perbaikan jika revisi..."
            class="w-full border border-slate-300 rounded-lg p-3 text-xs focus:ring-2 focus:ring-indigo-500 focus:outline-none"
          ></textarea>
        </div>

        <div class="flex justify-end pt-2">
          <button
            type="submit"
            :disabled="submitting || reviewForm.catatan.trim().length < 5"
            class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs rounded-lg shadow transition disabled:opacity-50"
          >
            {{ submitting ? 'Menyimpan...' : 'Simpan Keputusan' }}
          </button>
        </div>
      </form>
    </div>

    <!-- Riwayat Catatan / Penilaian -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-3">
      <h3 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Riwayat Catatan Penilaian</h3>
      <div v-if="permohonan.riwayat?.length" class="space-y-3 divide-y divide-slate-100">
        <div v-for="r in permohonan.riwayat" :key="r.id" class="pt-3 first:pt-0 space-y-1">
          <div class="flex justify-between items-center text-[11px]">
            <span class="font-bold text-slate-700">{{ r.user?.name || 'Penilai' }}</span>
            <span class="text-slate-400">{{ new Date(r.created_at).toLocaleString('id-ID') }}</span>
          </div>
          <span :class="statusClass(r.status)" class="inline-block px-2 py-0.5 rounded text-[9px] font-bold uppercase">
            {{ r.status }}
          </span>
          <p class="text-xs text-slate-600 pt-1 italic">"{{ r.catatan }}"</p>
        </div>
      </div>
      <p v-else class="text-xs text-slate-400 italic">Belum ada riwayat catatan.</p>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRoute } from 'vue-router';
import api from '../axios';

const route = useRoute();
const permohonan = ref({});
const submitting = ref(false);

// Deteksi Role Penilai secara fleksibel (pemeriksaan langsung dari localStorage)
const isPenilai = computed(() => {
  const user = JSON.parse(localStorage.getItem('user') || '{}');
  const rawRole = localStorage.getItem('role') || user.role || user.role_name || '';
  return String(rawRole).toLowerCase() === 'penilai';
});

const reviewForm = ref({
  status: 'approved',
  catatan: '',
});

const fetchDetail = async () => {
  try {
    const res = await api.get(`/permohonan/${route.params.nomor_permohonan}`);
    
    // Cek jika response ternyata HTML (bukan Object JSON)
    if (typeof res.data === 'string' && res.data.includes('<!DOCTYPE html>')) {
      console.error('ERROR: API mengembalikan HTML, route /api/permohonan/... belum terdaftar di routes/api.php!');
      return;
    }

    // Assign data JSON
    permohonan.value = res.data.data || res.data;
    console.log('DATA PERMOHONAN BERHASIL DILOAD:', permohonan.value);

  } catch (err) {
    console.error('Gagal mengambil detail:', err);
  }
};

const submitReview = async () => {
  submitting.value = true;
  try {
    await api.post(`/penilai/permohonan/${permohonan.value.id}/review`, reviewForm.value);
    alert('Keputusan berhasil disimpan!');
    fetchDetail();
  } catch (err) {
    alert(err.response?.data?.message || 'Gagal menyimpan keputusan.');
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

onMounted(fetchDetail);
</script>