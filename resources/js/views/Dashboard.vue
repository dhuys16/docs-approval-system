<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-800">Dashboard Overview</h1>
        <p class="text-xs text-slate-500">Statistik permohonan real-time berdasarkan hak akses Anda (Role: <span class="font-bold uppercase text-indigo-600">{{ role }}</span>)</p>
      </div>
    </div>

    <!-- Cards Stats -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <p class="text-[10px] font-bold text-slate-400 uppercase">Total</p>
        <p class="text-2xl font-extrabold text-slate-800 mt-1">{{ stats.total || 0 }}</p>
      </div>

      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <p class="text-[10px] font-bold text-slate-400 uppercase">Draft</p>
        <p class="text-2xl font-extrabold text-slate-500 mt-1">{{ stats.draft || 0 }}</p>
      </div>

      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <p class="text-[10px] font-bold text-blue-500 uppercase">Submitted</p>
        <p class="text-2xl font-extrabold text-blue-600 mt-1">{{ stats.submitted || 0 }}</p>
      </div>

      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <p class="text-[10px] font-bold text-amber-500 uppercase">Revisi</p>
        <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ stats.revisi || 0 }}</p>
      </div>

      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <p class="text-[10px] font-bold text-emerald-500 uppercase">Approved</p>
        <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ stats.approved || 0 }}</p>
      </div>

      <div class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm">
        <p class="text-[10px] font-bold text-rose-500 uppercase">Rejected</p>
        <p class="text-2xl font-extrabold text-rose-600 mt-1">{{ stats.rejected || 0 }}</p>
      </div>
    </div>

    <!-- Chart Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
      <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800 mb-4 uppercase tracking-wider">Grafik Status Permohonan</h2>
        <div class="relative w-full h-72">
          <canvas ref="chartCanvas"></canvas>
        </div>
      </div>

      <div class="bg-white p-6 rounded-xl border border-slate-200 shadow-sm">
        <h2 class="text-sm font-bold text-slate-800 mb-4 uppercase tracking-wider">Tren Permohonan (7 Hari Terakhir)</h2>
        <div class="relative w-full h-72">
          <canvas ref="timeChartCanvas"></canvas>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue';
import api from '../axios';
import Chart from 'chart.js/auto';

const stats = ref({});
const role = ref('');
const chartCanvas = ref(null);
const timeChartCanvas = ref(null);
let chartInstance = null;
let timeChartInstance = null;

onMounted(async () => {
  try {
    const res = await api.get('/dashboard/stats');
    stats.value = res.data.statistics || {};
    role.value = res.data.role || 'pemohon';

    await nextTick();
    renderChart();
  } catch (err) {
    console.error('Gagal mengambil statistik dashboard', err);
  }
});

const renderChart = () => {
  if (chartInstance) {
    chartInstance.destroy();
  }
  if (timeChartInstance) {
    timeChartInstance.destroy();
  }

  if (chartCanvas.value) {
    chartInstance = new Chart(chartCanvas.value, {
      type: 'bar',
      data: {
        labels: ['Draft', 'Submitted', 'Revisi', 'Approved', 'Rejected'],
        datasets: [{
          label: 'Jumlah Permohonan',
          data: [
            stats.value.draft || 0,
            stats.value.submitted || 0,
            stats.value.revisi || 0,
            stats.value.approved || 0,
            stats.value.rejected || 0,
          ],
          backgroundColor: [
            '#cbd5e1', // draft
            '#3b82f6', // submitted
            '#f59e0b', // revisi
            '#10b981', // approved
            '#f43f5e', // rejected
          ],
          borderRadius: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        },
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
  }

  if (timeChartCanvas.value && stats.value.time_series) {
    const timeLabels = Object.keys(stats.value.time_series);
    const timeData = Object.values(stats.value.time_series);

    timeChartInstance = new Chart(timeChartCanvas.value, {
      type: 'line',
      data: {
        labels: timeLabels,
        datasets: [{
          label: 'Jumlah Permohonan',
          data: timeData,
          borderColor: '#4f46e5',
          backgroundColor: 'rgba(79, 70, 229, 0.1)',
          borderWidth: 2,
          fill: true,
          tension: 0.3,
          pointBackgroundColor: '#4f46e5',
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
          y: {
            beginAtZero: true,
            ticks: {
              stepSize: 1
            }
          }
        },
        plugins: {
          legend: {
            display: false
          }
        }
      }
    });
  }
};
</script>