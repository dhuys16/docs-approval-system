<template>
  <div class="min-h-screen bg-slate-900 flex items-center justify-center p-4">
    <div class="bg-white w-full max-w-md rounded-2xl p-8 shadow-2xl space-y-6">
      <div class="text-center space-y-1">
        <h1 class="text-2xl font-bold text-slate-800">DocApprove System</h1>
        <p class="text-xs text-slate-500">Daftarkan akun baru Anda</p>
      </div>

      <div v-if="errorMessage" class="p-3 bg-rose-50 border border-rose-200 text-rose-700 text-xs rounded-lg">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleRegister" class="space-y-4">
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Nama Lengkap</label>
          <input
            v-model="form.name"
            type="text"
            required
            placeholder="Nama Anda"
            class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            placeholder="nama@email.com"
            class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
          />
        </div>

        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Password</label>
          <input
            v-model="form.password"
            type="password"
            required
            placeholder="••••••••"
            class="w-full border border-slate-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 rounded-lg text-sm shadow-md transition disabled:opacity-50"
        >
          {{ loading ? 'Memproses...' : 'Daftar Akun' }}
        </button>

        <p class="text-center text-xs text-slate-500 mt-4 pt-2">
          Sudah punya akun? 
          <router-link to="/login" class="text-indigo-600 font-bold hover:underline">Masuk di sini</router-link>
        </p>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../axios';

const router = useRouter();
const loading = ref(false);
const errorMessage = ref('');

const form = ref({
  name: '',
  email: '',
  password: '',
  role: 'pemohon'
});

const handleRegister = async () => {
  loading.value = true;
  errorMessage.value = '';

  try {
    const res = await api.post('/register', form.value);
    localStorage.setItem('token', res.data.access_token);
    router.push('/');
  } catch (err) {
    if (err.response?.data?.errors) {
      const errors = err.response.data.errors;
      errorMessage.value = Object.values(errors).map(e => e.join(', ')).join(' | ');
    } else {
      errorMessage.value = err.response?.data?.message || 'Registrasi gagal. Periksa kembali input Anda.';
    }
  } finally {
    loading.value = false;
  }
};
</script>
