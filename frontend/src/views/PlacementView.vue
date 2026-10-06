<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

interface User {
  id: number
  name: string
}

interface HousingUnit {
  id: number
  unit_number: string
  capacity: number
  status: string
}

interface Placement {
  id: number
  user_id: number
  housing_unit_id: number
  check_in_date: string
  check_out_date: string | null
  user?: User
  housing_unit?: HousingUnit
}

const router = useRouter()

const placements = ref<Placement[]>([])
const isLoading = ref(false)
const isSubmitting = ref(false)
const editingId = ref<number | null>(null)

const form = ref({
  user_id: '' as number | string,
  housing_unit_id: '' as number | string,
  check_in_date: '',
  check_out_date: '',
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

function statusClass(status: string) {
  return `badge badge-${status}`
}

function formatDate(value: string | null) {
  if (!value) {
    return '-'
  }

  return value.slice(0, 10)
}

async function fetchPlacements() {
  isLoading.value = true

  try {
    const { data } = await api.get('/placements')
    placements.value = data.data
  } catch {
    showToast('error', 'Gagal memuat daftar penugasan.')
  } finally {
    isLoading.value = false
  }
}

function resetForm() {
  form.value = {
    user_id: '',
    housing_unit_id: '',
    check_in_date: '',
    check_out_date: '',
  }

  editingId.value = null
}

function handleEdit(placement: Placement) {
  editingId.value = placement.id
  form.value = {
    user_id: placement.user_id,
    housing_unit_id: placement.housing_unit_id,
    check_in_date: placement.check_in_date ? placement.check_in_date.slice(0, 10) : '',
    check_out_date: placement.check_out_date ? placement.check_out_date.slice(0, 10) : '',
  }

  window.scrollTo({ top: 0, behavior: 'smooth' })
}

async function handleDelete(placement: Placement) {
  if (!window.confirm(`Hapus penugasan #${placement.id}?`)) {
    return
  }

  try {
    await api.delete(`/placements/${placement.id}`)

    if (editingId.value === placement.id) {
      resetForm()
    }

    showToast('success', 'Penugasan berhasil dihapus.')
    await fetchPlacements()
  } catch {
    showToast('error', 'Gagal menghapus penugasan. Silakan coba lagi.')
  }
}

async function handleSubmit() {
  isSubmitting.value = true

  const payload = {
    user_id: Number(form.value.user_id),
    housing_unit_id: Number(form.value.housing_unit_id),
    check_in_date: form.value.check_in_date,
    check_out_date: form.value.check_out_date || null,
  }

  try {
    if (editingId.value !== null) {
      await api.put(`/placements/${editingId.value}`, payload)
      showToast('success', 'Penugasan berhasil diperbarui.')
    } else {
      await api.post('/placements', payload)
      showToast('success', 'Penugasan berhasil dibuat.')
    }

    resetForm()
    await fetchPlacements()
  } catch (error: any) {
    if (error.response?.status === 422) {
      showToast('error', 'Data tidak valid. Periksa kembali input Anda.')
    } else if (editingId.value !== null) {
      showToast('error', 'Gagal memperbarui penugasan. Silakan coba lagi.')
    } else {
      showToast('error', 'Gagal membuat penugasan. Silakan coba lagi.')
    }
  } finally {
    isSubmitting.value = false
  }
}

function handleLogout() {
  localStorage.removeItem('token')
  router.push({ name: 'login' })
}

onMounted(fetchPlacements)
</script>

<template>
  <div class="placement-page">
    <nav class="navbar">
      <RouterLink class="nav-link" :to="{ name: 'housing-units' }">Unit Hunian</RouterLink>
      <RouterLink class="nav-link" :to="{ name: 'maintenance' }">Tiket Pemeliharaan</RouterLink>
      <RouterLink class="nav-link active" :to="{ name: 'placements' }">Penugasan</RouterLink>
    </nav>

    <header class="page-header">
      <h1>Penugasan Pegawai</h1>
      <button class="logout-button" @click="handleLogout">Logout</button>
    </header>

    <Transition name="toast">
      <div v-if="toast" :class="['toast', `toast-${toast.type}`]">
        {{ toast.message }}
      </div>
    </Transition>

    <form class="placement-form" @submit.prevent="handleSubmit">
      <h2>{{ editingId !== null ? `Edit Penugasan #${editingId}` : 'Buat Penugasan Baru' }}</h2>

      <label for="user_id">ID Pegawai</label>
      <input
        id="user_id"
        v-model="form.user_id"
        type="number"
        min="1"
        placeholder="Contoh: 1"
        required
      />

      <label for="housing_unit_id">ID Unit Hunian</label>
      <input
        id="housing_unit_id"
        v-model="form.housing_unit_id"
        type="number"
        min="1"
        placeholder="Contoh: 1"
        required
      />

      <label for="check_in_date">Tanggal Check-in</label>
      <input id="check_in_date" v-model="form.check_in_date" type="date" required />

      <label for="check_out_date">Tanggal Check-out</label>
      <input id="check_out_date" v-model="form.check_out_date" type="date" />

      <button type="submit" :disabled="isSubmitting">
        {{ isSubmitting ? 'Menyimpan...' : editingId !== null ? 'Perbarui Penugasan' : 'Kirim Penugasan' }}
      </button>

      <button v-if="editingId !== null" type="button" class="cancel-button" @click="resetForm">
        Batal
      </button>
    </form>

    <h2 class="list-title">Daftar Penugasan</h2>

    <p v-if="isLoading">Memuat data...</p>
    <p v-else-if="placements.length === 0">Belum ada penugasan.</p>

    <table v-else class="placement-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Pegawai</th>
          <th>Unit Hunian</th>
          <th>Check-in</th>
          <th>Check-out</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(placement, index) in placements" :key="placement.id">
          <td>{{ index + 1 }}</td>
          <td>{{ placement.user?.name ?? placement.user_id }}</td>
          <td>
            <span>{{ placement.housing_unit?.unit_number ?? placement.housing_unit_id }}</span>
            <span
              v-if="placement.housing_unit"
              :class="statusClass(placement.housing_unit.status)"
            >
              {{ placement.housing_unit.status }}
            </span>
          </td>
          <td>{{ formatDate(placement.check_in_date) }}</td>
          <td>{{ formatDate(placement.check_out_date) }}</td>
          <td>
            <div class="action-buttons">
              <button type="button" class="edit-button" @click="handleEdit(placement)">
                Edit
              </button>
              <button type="button" class="delete-button" @click="handleDelete(placement)">
                Hapus
              </button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.placement-page {
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

.placement-form {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  max-width: 480px;
  padding: 1.5rem;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
}

.placement-form h2 {
  margin: 0 0 0.5rem;
  font-size: 1.1rem;
}

label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #334155;
}

input {
  padding: 0.6rem 0.75rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.95rem;
  font-family: inherit;
}

input:focus {
  outline: 2px solid #3b82f6;
  border-color: transparent;
}

.placement-form button {
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

.placement-form button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.cancel-button {
  background: #64748b !important;
}

.action-buttons {
  display: flex;
  gap: 0.5rem;
}

.edit-button,
.delete-button {
  padding: 0.4rem 0.75rem;
  border: none;
  border-radius: 6px;
  font-size: 0.82rem;
  font-weight: 600;
  cursor: pointer;
}

.edit-button {
  background: #2563eb;
  color: #ffffff;
}

.delete-button {
  background: #dc2626;
  color: #ffffff;
}

.placement-table {
  width: 100%;
  border-collapse: collapse;
  background: #ffffff;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
}

.placement-table th,
.placement-table td {
  padding: 0.75rem 1rem;
  text-align: left;
  border-bottom: 1px solid #e2e8f0;
}

.placement-table th {
  background: #f8fafc;
  font-size: 0.85rem;
  text-transform: uppercase;
  color: #475569;
}

.badge {
  display: inline-block;
  margin-left: 0.5rem;
  padding: 0.25rem 0.6rem;
  border-radius: 999px;
  font-size: 0.78rem;
  font-weight: 600;
  text-transform: capitalize;
  white-space: nowrap;
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