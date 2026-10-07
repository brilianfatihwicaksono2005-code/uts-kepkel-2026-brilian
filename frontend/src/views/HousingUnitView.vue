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
const isSubmitting = ref(false)
const editingId = ref<number | null>(null)

const form = ref({
  unit_number: '',
  capacity: '' as number | string,
  status: 'available',
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

async function fetchHousingUnits() {
  isLoading.value = true

  try {
    const { data } = await api.get('/housing-units')
    housingUnits.value = data.data
  } catch {
    showToast('error', 'Gagal memuat daftar unit hunian.')
  } finally {
    isLoading.value = false
  }
}

function resetForm() {
  form.value = {
    unit_number: '',
    capacity: '',
    status: 'available',
  }

  editingId.value = null
}

function handleEdit(unit: HousingUnit) {
  editingId.value = unit.id
  form.value = {
    unit_number: unit.unit_number,
    capacity: unit.capacity,
    status: unit.status,
  }

  window.scrollTo({ top: 0, behavior: 'smooth' })
}

async function handleDelete(unit: HousingUnit) {
  if (!window.confirm(`Hapus unit ${unit.unit_number}?`)) {
    return
  }

  try {
    await api.delete(`/housing-units/${unit.id}`)

    if (editingId.value === unit.id) {
      resetForm()
    }

    showToast('success', 'Unit hunian berhasil dihapus.')
    await fetchHousingUnits()
  } catch {
    showToast('error', 'Gagal menghapus unit hunian. Silakan coba lagi.')
  }
}

async function handleSubmit() {
  isSubmitting.value = true

  const payload = {
    unit_number: form.value.unit_number,
    capacity: Number(form.value.capacity),
    status: form.value.status,
  }

  try {
    if (editingId.value !== null) {
      await api.put(`/housing-units/${editingId.value}`, payload)
      showToast('success', 'Unit hunian berhasil diperbarui.')
    } else {
      await api.post('/housing-units', payload)
      showToast('success', 'Unit hunian berhasil dibuat.')
    }

    resetForm()
    await fetchHousingUnits()
  } catch (error: any) {
    if (error.response?.status === 422) {
      showToast('error', 'Data tidak valid. Periksa kembali input Anda.')
    } else if (editingId.value !== null) {
      showToast('error', 'Gagal memperbarui unit hunian. Silakan coba lagi.')
    } else {
      showToast('error', 'Gagal membuat unit hunian. Silakan coba lagi.')
    }
  } finally {
    isSubmitting.value = false
  }
}

function handleLogout() {
  localStorage.removeItem('token')
  router.push({ name: 'login' })
}

onMounted(fetchHousingUnits)
</script>

<template>
  <div class="housing-page">
    <nav class="navbar">
      <RouterLink class="nav-link active" :to="{ name: 'housing-units' }">Unit Hunian</RouterLink>
      <RouterLink class="nav-link" :to="{ name: 'maintenance' }">Tiket Pemeliharaan</RouterLink>
      <RouterLink class="nav-link" :to="{ name: 'placements' }">Penugasan</RouterLink>
    </nav>

    <header class="page-header">
      <h1>Manajemen Unit Hunian</h1>
      <button class="logout-button" @click="handleLogout">Logout</button>
    </header>

    <Transition name="toast">
      <div v-if="toast" :class="['toast', `toast-${toast.type}`]">
        {{ toast.message }}
      </div>
    </Transition>

    <form class="unit-form" @submit.prevent="handleSubmit">
      <h2>{{ editingId !== null ? `Edit Unit #${editingId}` : 'Tambah Unit Baru' }}</h2>

      <label for="unit_number">Nomor Unit</label>
      <input
        id="unit_number"
        v-model="form.unit_number"
        type="text"
        placeholder="Contoh: A-101"
        required
      />

      <label for="capacity">Kapasitas</label>
      <input
        id="capacity"
        v-model="form.capacity"
        type="number"
        min="1"
        placeholder="Contoh: 4"
        required
      />

      <label for="status">Status</label>
      <select id="status" v-model="form.status" required>
        <option value="available">Available</option>
        <option value="occupied">Occupied</option>
        <option value="maintenance">Maintenance</option>
      </select>

      <button type="submit" :disabled="isSubmitting">
        {{ isSubmitting ? 'Menyimpan...' : editingId !== null ? 'Perbarui Unit' : 'Simpan Unit' }}
      </button>

      <button v-if="editingId !== null" type="button" class="cancel-button" @click="resetForm">
        Batal
      </button>
    </form>

    <h2 class="list-title">Daftar Unit Hunian</h2>

    <p v-if="isLoading">Memuat data...</p>
    <p v-else-if="housingUnits.length === 0">Belum ada unit hunian.</p>

    <table v-else class="unit-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Nomor Unit</th>
          <th>Kapasitas</th>
          <th>Status</th>
          <th>Aksi</th>
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
          <td>
            <div class="action-buttons">
              <button type="button" class="edit-button" @click="handleEdit(unit)">Edit</button>
              <button type="button" class="delete-button" @click="handleDelete(unit)">Hapus</button>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<style scoped>
.housing-page {
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

.unit-form {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  max-width: 480px;
  padding: 1.5rem;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(15, 23, 42, 0.08);
}

.unit-form h2 {
  margin: 0 0 0.5rem;
  font-size: 1.1rem;
}

label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #334155;
}

input,
select {
  padding: 0.6rem 0.75rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.95rem;
  font-family: inherit;
}

input:focus,
select:focus {
  outline: 2px solid #3b82f6;
  border-color: transparent;
}

.unit-form button {
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

.unit-form button:disabled {
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