<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '@/services/api'

const router = useRouter()

const email = ref('')
const password = ref('')
const errorMessage = ref('')
const isLoading = ref(false)

async function handleSubmit() {
  errorMessage.value = ''
  isLoading.value = true

  try {
    const { data } = await api.post('/login', {
      email: email.value,
      password: password.value,
    })

    localStorage.setItem('token', data.access_token)

    router.push({ name: 'dashboard' })
  } catch (error: any) {
    if (error.response?.status === 401) {
      errorMessage.value = 'Email atau password salah.'
    } else if (error.response?.status === 422) {
      errorMessage.value = 'Format email atau password tidak valid.'
    } else {
      errorMessage.value = 'Terjadi kesalahan. Silakan coba lagi.'
    }
  } finally {
    isLoading.value = false
  }
}
</script>

<template>
  <div class="login-page">
    <form class="login-card" @submit.prevent="handleSubmit">
      <h1>Login</h1>
      <p class="subtitle">Sistem Manajemen Fasilitas Hunian</p>

      <label for="email">Email</label>
      <input
        id="email"
        v-model="email"
        type="email"
        placeholder="email@contoh.com"
        autocomplete="email"
        required
      />

      <label for="password">Password</label>
      <input
        id="password"
        v-model="password"
        type="password"
        placeholder="••••••••"
        autocomplete="current-password"
        required
      />

      <p v-if="errorMessage" class="error">{{ errorMessage }}</p>

      <button type="submit" :disabled="isLoading">
        {{ isLoading ? 'Memproses...' : 'Masuk' }}
      </button>
    </form>
  </div>
</template>

<style scoped>
.login-page {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  background: #f1f5f9;
}

.login-card {
  width: 100%;
  max-width: 360px;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  padding: 2rem;
  background: #ffffff;
  border-radius: 12px;
  box-shadow: 0 10px 25px rgba(15, 23, 42, 0.1);
}

h1 {
  margin: 0;
  font-size: 1.5rem;
}

.subtitle {
  margin: 0 0 1rem;
  font-size: 0.875rem;
  color: #64748b;
}

label {
  font-size: 0.875rem;
  font-weight: 600;
  color: #334155;
}

input {
  padding: 0.6rem 0.75rem;
  border: 1px solid #cbd5e1;
  border-radius: 8px;
  font-size: 0.95rem;
}

input:focus {
  outline: 2px solid #3b82f6;
  border-color: transparent;
}

button {
  margin-top: 1rem;
  padding: 0.65rem;
  border: none;
  border-radius: 8px;
  background: #2563eb;
  color: #ffffff;
  font-size: 0.95rem;
  font-weight: 600;
  cursor: pointer;
}

button:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.error {
  margin: 0.25rem 0 0;
  color: #dc2626;
  font-size: 0.85rem;
}
</style>