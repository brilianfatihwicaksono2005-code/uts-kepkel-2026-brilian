<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

interface HousingUnit {
  id: number
  unit_number: string
}

interface User {
  id: number
  name: string
}

interface MaintenanceTicket {
  id: number
  housing_unit_id: number
  description: string
  urgency: string
  status: string
  housing_unit?: HousingUnit
  user?: User
}

const router = useRouter()

const tickets = ref<MaintenanceTicket[]>([])
const isLoading = ref(false)
const isSubmitting = ref(false)

const form = ref({
  housing_unit_id: '' as number | string,
  description: '',
  urgency: 'low',
})

const toast = ref<{ type: 'success' | 'error'; message: string } | null>(null)
let toastTimer: ReturnType<typeof setTimeout> | undefined

function showToast(type: 'success' | 'error', message: string) {
  toast.value = { type, message }

  if (toastTimer) {
    clearTimeout(toastTimer)
  }

  toastTimer = setTimeout(() => {
    toast.value = null
  }, 3000)
}

function urgencyClass(urgency: string) {
  return `badge badge-${urgency}`
}

function statusClass(status: string) {
  return `badge status-${status}`
}

async function fetchTickets() {
  isLoading.value = true

  try {
    const { data } = await api.get('/maintenance-tickets')
    tickets.value = data.data
  } catch {
    showToast('error', 'Gagal memuat daftar tiket.')
  } finally {
    isLoading.value = false
  }
}

async function handleSubmit() {
  isSubmitting.value = true

  try {
    await api.post('/maintenance-tickets', {
      housing_unit_id: Number(form.value.housing_unit_id),
      description: form.value.description,
      urgency: form.value.urgency,
    })

    showToast('success', 'Tiket pemeliharaan berhasil dibuat.')

    form.value = {
      housing_unit_id: '',
      description: '',
      urgency: 'low',
    }

    await fetchTickets()
  } catch (error: any) {
    if (error.response?.status === 422) {
      showToast('error', 'Data tidak valid. Periksa kembali input Anda.')
    } else {
      showToast('error', 'Gagal membuat tiket. Silakan coba lagi.')
    }
  } finally {
    isSubmitting.value = false
  }
}

function handleLogout() {
  localStorage.removeItem('token')
  router.push({ name: 'login' })
}

onMounted(fetchTickets)
</script>

<template>
  <div class="maintenance-page">
    <nav class="navbar">
      <RouterLink class="nav-link" :to="{ name: 'dashboard' }">Unit Hunian</RouterLink>
      <RouterLink class="nav-link active" :to="{ name: 'maintenance' }"
        >Tiket Pemeliharaan</RouterLink
      >
    </nav>

    <header class="page-header">
      <h1>Tiket Pemeliharaan</h1>
      <button class="logout-button" @click="handleLogout">Logout</button>
    </header>

    <Transition name="toast">
      <div v-if="toast" :class="['toast', `toast-${toast.type}`]">
        {{ toast.message }}
      </div>
    </Transition>

    <form class="ticket-form" @submit.prevent="handleSubmit">
      <h2>Buat Tiket Baru</h2>

      <label for="housing_unit_id">ID Unit Hunian</label>
      <input
        id="housing_unit_id"
        v-model="form.housing_unit_id"
        type="number"
        min="1"
        placeholder="Contoh: 1"
        required
      />

      <label for="description">Deskripsi</label>
      <textarea
        id="description"
        v-model="form.description"
        rows="3"
        placeholder="Jelaskan kerusakan atau keluhan..."
        required
      ></textarea>

      <label for="urgency">Urgensi</label>
      <select id="urgency" v-model="form.urgency" required>
        <option value="low">Low</option>
        <option value="medium">Medium</option>
        <option value="high">High</option>
      </select>

      <button type="submit" :disabled="isSubmitting">
        {{ isSubmitting ? 'Menyimpan...' : 'Kirim Tiket' }}
      </button>
    </form>

    <h2 class="list-title">Daftar Tiket</h2>

    <p v-if="isLoading">Memuat data...</p>
    <p v-else-if="tickets.length === 0">Belum ada tiket pemeliharaan.</p>

    <table v-else class="ticket-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Unit</th>
          <th>Pelapor</th>
          <th>Deskripsi</th>
          <th>Urgensi</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(ticket, index) in tickets" :key="ticket.id">
          <td>{{ index + 1 }}</td>
          <td>{{ ticket.housing_unit?.unit_number ?? ticket.housing_unit_id }}</td>
          <td>{{ ticket.user?.name ?? '-' }}</td>
          <td>{{ ticket.description }}</td>
          <td>
            <span :class="urgencyClass(ticket.urgency)">{{ ticket.urgency }}</span>
          </td>
          <td>
            <span :class="statusClass(ticket.status)">{{ ticket.status }}</span>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.maintenance-page {
  max-width: 1000px;
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

.page-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 1.5rem;
}

h1 {
  margin: 0;
  font-size: 1.5rem;
}

.list-title {
  margin: 2rem 0 1rem;
  font-size: 1.15rem;
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

.ticket-form {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  max-width: 480px;
  padding: 1.5rem;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
}

.ticket-form h2 {
  margin: 0 0 0.5rem;
  font-size: 1.1rem;
}

label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #334155;
}

input,
textarea,
select {
  padding: 0.6rem 0.75rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.95rem;
  font-family: inherit;
}

input:focus,
textarea:focus,
select:focus {
  outline: 2px solid #3b82f6;
  border-color: transparent;
}

.ticket-form button {
  margin-top: 0.75rem;
  padding: 0.65rem;
  border: none;
  border-radius: 8px;
  background: #2563eb;
  color: #ffffff;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
}

.ticket-form button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.ticket-table {
  width: 100%;
  border-collapse: collapse;
  background: #ffffff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
}

.ticket-table th,
.ticket-table td {
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
  vertical-align: top;
}

.ticket-table th {
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
  white-space: nowrap;
}

.badge-low {
  background: #dcfce7;
  color: #166534;
}

.badge-medium {
  background: #fef3c7;
  color: #92400e;
}

.badge-high {
  background: #fee2e2;
  color: #991b1b;
}

.status-open {
  background: #fee2e2;
  color: #991b1b;
}

.status-in_progress {
  background: #fef3c7;
  color: #92400e;
}

.status-resolved {
  background: #dcfce7;
  color: #166534;
}

.toast {
  position: fixed;
  top: 1.5rem;
  right: 1.5rem;
  padding: 0.75rem 1.25rem;
  border-radius: 8px;
  color: #ffffff;
  font-weight: 600;
  box-shadow: 0 8px 20px rgba(15, 23, 42, 0.2);
  z-index: 1000;
}

.toast-success {
  background: #16a34a;
}

.toast-error {
  background: #dc2626;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>