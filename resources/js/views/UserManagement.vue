<template>
  <div class="space-y-6 max-w-6xl mx-auto p-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Master Data User</h1>
        <p class="text-xs text-slate-500">Kelola hak akses pengguna di dalam sistem.</p>
      </div>

      <!-- Area Search & Filter -->
      <div class="flex items-center gap-3">
        <div class="relative">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Cari nama atau email..."
            class="border border-slate-300 rounded-lg pl-9 pr-3 py-1.5 text-xs w-56 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
          />
          <svg class="w-4 h-4 text-slate-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
        </div>

        <select 
          v-model="filterRole" 
          class="border border-slate-300 rounded-lg px-3 py-1.5 text-xs bg-white focus:ring-2 focus:ring-indigo-500 focus:outline-none"
        >
          <option value="">Semua Role</option>
          <option value="pemohon">Pemohon</option>
          <option value="penilai">Penilai</option>
          <option value="admin">Admin</option>
        </select>
      </div>
    </div>

    <!-- Tabel Data -->
    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-xs text-slate-600">
          <thead class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase">
            <tr>
              <th class="p-4">Nama</th>
              <th class="p-4">Email</th>
              <th class="p-4">Role Saat Ini</th>
              <th class="p-4 text-center">Aksi Ubah Role</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">

            <tr v-if="filteredUsers.length === 0">
              <td colspan="4" class="p-8 text-center text-slate-400 italic">
                Tidak ada data user yang sesuai dengan pencarian.
              </td>
            </tr>

            <tr v-for="user in paginatedUsers" :key="user.id" class="hover:bg-slate-50 transition">
              <td class="p-4 font-bold text-slate-800">{{ user.name }}</td>
              <td class="p-4 text-slate-500">{{ user.email }}</td>
              <td class="p-4">
                <span :class="[
                  'px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide',
                  user.currentRole === 'admin' ? 'bg-rose-100 text-rose-700' : '',
                  user.currentRole === 'penilai' ? 'bg-blue-100 text-blue-700' : '',
                  user.currentRole === 'pemohon' ? 'bg-emerald-100 text-emerald-700' : ''
                ]">
                  {{ user.currentRole }}
                </span>
              </td>
              <td class="p-4 flex justify-center items-center gap-2">
                <select 
                  v-model="user.selectedRole" 
                  class="border border-slate-300 text-slate-700 text-xs rounded-lg px-2 py-1 focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                >
                  <option value="pemohon">Pemohon</option>
                  <option value="penilai">Penilai</option>
                  <option value="admin">Admin</option>
                </select>
                <button 
                  @click="updateRole(user)" 
                  class="px-3.5 py-1.5 bg-indigo-600 hover:bg-indigo-700 text-white font-bold rounded-lg shadow-sm transition inline-block text-[11px]"
                >
                  Simpan
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Navigasi Pagination -->
      <div v-if="totalPages > 1" class="p-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
        <span>Halaman {{ currentPage }} dari {{ totalPages }}</span>
        <div class="flex gap-1">
          <button
            @click="prevPage"
            :disabled="currentPage === 1"
            class="px-3 py-1 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-40 disabled:hover:bg-transparent transition"
          >
            Sebelumnya
          </button>
          <button
            @click="nextPage"
            :disabled="currentPage === totalPages"
            class="px-3 py-1 border border-slate-200 rounded-lg hover:bg-slate-50 disabled:opacity-40 disabled:hover:bg-transparent transition"
          >
            Selanjutnya
          </button>
        </div>
      </div>
      
    </div>
  </div>
</template>

<script>
import axios from 'axios';

export default {
  data() {
    return {
      users: [],
      searchQuery: '', 
      filterRole: '',  
      currentPage: 1,      
      itemsPerPage: 10     
    };
  },
  computed: {
    filteredUsers() {
      return this.users.filter(user => {
        const query = this.searchQuery.toLowerCase();
        const matchSearch = user.name.toLowerCase().includes(query) || 
                            user.email.toLowerCase().includes(query);
        const matchRole = this.filterRole === '' || user.currentRole === this.filterRole;

        return matchSearch && matchRole;
      });
    },
    totalPages() {
      return Math.ceil(this.filteredUsers.length / this.itemsPerPage);
    },
    startIndex() {
      return (this.currentPage - 1) * this.itemsPerPage;
    },
    endIndex() {
      return this.startIndex + this.itemsPerPage;
    },
    paginatedUsers() {
      return this.filteredUsers.slice(this.startIndex, this.endIndex);
    }
  },
  watch: {
    searchQuery() {
      this.currentPage = 1;
    },
    filterRole() {
      this.currentPage = 1;
    }
  },
  mounted() {
    this.fetchUsers();
  },
  methods: {
    getAuthHeaders() {
      const token = localStorage.getItem('token'); 
      return {
        headers: {
          Authorization: `Bearer ${token}`
        }
      };
    },

    async fetchUsers() {
      try {
        const response = await axios.get('/api/users', this.getAuthHeaders());
        
        this.users = response.data.data.map(u => {
          const currentRole = u.roles.length > 0 ? u.roles[0].name : 'pemohon';
          return {
            ...u,
            currentRole: currentRole,
            selectedRole: currentRole
          };
        });
      } catch (error) {
        console.error('Error fetching users:', error);
        alert('Gagal mengambil data dari server. Cek console.');
      }
    },
    
    async updateRole(user) {
      try {
        await axios.put(
          `/api/users/${user.id}/role`, 
          { role: user.selectedRole }, 
          this.getAuthHeaders()
        );
        
        alert(`Role ${user.name} berhasil diubah menjadi ${user.selectedRole}!`);
        this.fetchUsers();
      } catch (error) {
        console.error('Error updating role:', error);
        alert('Gagal mengubah role. Cek console log.');
      }
    },

    nextPage() {
      if (this.currentPage < this.totalPages) {
        this.currentPage++;
      }
    },
    prevPage() {
      if (this.currentPage > 1) {
        this.currentPage--;
      }
    }
  }
};
</script>