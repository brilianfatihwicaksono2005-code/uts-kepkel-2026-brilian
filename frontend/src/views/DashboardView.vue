<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

interface HousingUnit {
  id: number
  unit_number: string
  capacity: number
  status: string
}

const router = useRouter()

const housingUnits = ref<HousingUnit[]>([])
const isLoading = ref(false)
const errorMessage = ref('')

function statusClass(status: string) {
  return `badge badge-${status}`
}

async function fetchHousingUnits() {
  isLoading.value = true
  errorMessage.value = ''

  try {
    const { data } = await api.get('/housing-units')
    housingUnits.value = data.data
  } catch {
    errorMessage.value = 'Gagal memuat daftar unit hunian.'
  } finally {
    isLoading.value = false
  }
}

function handleLogout() {
  localStorage.removeItem('token')
  router.push({ name: 'login' })
}

onMounted(fetchHousingUnits)
</script>

<template>
  <div class="dashboard-page">
    <nav class="navbar">
      <RouterLink class="nav-link active" :to="{ name: 'housing-units' }">Unit Hunian</RouterLink>
      <RouterLink class="nav-link" :to="{ name: 'maintenance' }">Tiket Pemeliharaan</RouterLink>
      <RouterLink class="nav-link" :to="{ name: 'placements' }">Penugasan</RouterLink>
    </nav>

    <header class="dashboard-header">
      <h1>Daftar Unit Hunian</h1>
      <button class="logout-button" @click="handleLogout">Logout</button>
    </header>

    <p v-if="isLoading">Memuat data...</p>
    <p v-else-if="errorMessage" class="error">{{ errorMessage }}</p>
    <p v-else-if="housingUnits.length === 0">Belum ada unit hunian.</p>

    <table v-else class="unit-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Nomor Unit</th>
          <th>Kapasitas</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(unit, index) in housingUnits" :key="unit.id">
          <td>{{ index + 1 }}</td>
          <td>{{ unit.unit_number }}</td>
          <td>{{ unit.capacity }}</td>
          <td>
            <span :class="statusClass(unit.status)">{{ unit.status }}</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.dashboard-page {
  max-width: 900px;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.navbar {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1.5rem;
}

.nav-link {
  padding: 0.5rem 1rem;
  border-radius: 8px;
  text-decoration: none;
  font-weight: 600;
  color: #334155;
  background: #e2e8f0;
}

.nav-link.active {
  background: #2563eb;
  color: #ffffff;
}

.dashboard-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

h1 {
  margin: 0;
  font-size: 1.5rem;
}

.logout-button {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 8px;
  background: #dc2626;
  color: #ffffff;
  font-weight: 600;
  cursor: pointer;
}

.unit-table {
  width: 100%;
  border-collapse: collapse;
  background: #ffffff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
}

.unit-table th,
.unit-table td {
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}

.unit-table th {
  background: #f8fafc;
  font-size: 0.85rem;
  text-transform: uppercase;
  color: #475569;
}

.badge {
  display: inline-block;
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 600;
  text-transform: capitalize;
}

.badge-available {
  background: #dcfce7;
  color: #166534;
}

.badge-occupied {
  background: #dbeafe;
  color: #1e40af;
}

.badge-maintenance {
  background: #fef3c7;
  color: #92400e;
}

.error {
  color: #dc2626;
}
</style>