<template>
  <div class="min-h-[80vh] flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md border border-gray-100">
      <h2 class="text-2xl font-bold text-gray-800 text-center mb-1">Selamat Datang</h2>
      <p class="text-sm text-gray-500 text-center mb-6">Sistem Persetujuan Dokumen Kelayakan</p>

      <div v-if="errorMessage" class="bg-red-50 border border-red-200 text-red-600 px-4 py-3 rounded-md mb-4 text-sm">
        {{ errorMessage }}
      </div>

      <form @submit.prevent="handleLogin" class="space-y-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
          <input
            v-model="form.email"
            type="email"
            required
            placeholder="pemohon@test.com / penilai@test.com"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
          />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
          <input
            v-model="form.password"
            type="password"
            required
            placeholder="••••••••"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 text-sm"
          />
        </div>

        <button
          type="submit"
          :disabled="loading"
          class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 rounded-md transition duration-200 disabled:opacity-50 text-sm"
        >
          {{ loading ? 'Memproses...' : 'Login' }}
        </button>
      </form>

      <div class="mt-6 pt-4 border-t border-gray-200 text-xs text-gray-500">
        <p class="font-semibold mb-1">Akun Testing untuk Demo:</p>
        <ul class="space-y-1">
          <li>Pemohon: <code class="bg-gray-100 px-1 py-0.5 rounded">pemohon@test.com</code> | <code class="bg-gray-100 px-1 py-0.5 rounded">password123</code></li>
          <li>Penilai: <code class="bg-gray-100 px-1 py-0.5 rounded">penilai@test.com</code> | <code class="bg-gray-100 px-1 py-0.5 rounded">password123</code></li>
        </ul>
      </div>
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
  email: '',
  password: '',
});

const handleLogin = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const res = await api.post('/login', form.value);
    localStorage.setItem('token', res.data.token);
    localStorage.setItem('user', JSON.stringify(res.data.user));
    window.location.href = '/';
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Gagal login, periksa kembali email & password.';
  } finally {
    loading.value = false;
  }
};
</script>