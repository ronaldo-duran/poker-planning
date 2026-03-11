<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 px-4">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl w-full max-w-md p-8">
      <div class="text-center mb-8">
        <div class="text-5xl mb-3">🃏</div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('auth.register') }}</h2>
      </div>

      <form @submit.prevent="handleRegister" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('auth.name') }}</label>
          <input v-model="form.name" type="text" required
            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('auth.email') }}</label>
          <input v-model="form.email" type="email" required
            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('auth.password') }}</label>
          <input v-model="form.password" type="password" required minlength="8"
            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('auth.confirmPassword') }}</label>
          <input v-model="form.password_confirmation" type="password" required
            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>

        <div v-if="errors" class="bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm px-4 py-3 rounded-lg space-y-1">
          <div v-for="(msgs, field) in errors" :key="field">
            <span v-for="msg in msgs" :key="msg">{{ msg }}</span>
          </div>
        </div>

        <button type="submit" :disabled="loading"
          class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white py-2.5 rounded-lg font-semibold transition">
          <span v-if="loading">{{ $t('common.loading') }}</span>
          <span v-else>{{ $t('auth.registerButton') }}</span>
        </button>
      </form>

      <p class="text-center text-sm text-gray-600 dark:text-gray-400 mt-6">
        {{ $t('auth.hasAccount') }}
        <RouterLink to="/login" class="text-indigo-600 dark:text-indigo-400 hover:underline font-medium">
          {{ $t('auth.login') }}
        </RouterLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '../../stores/auth';

const auth = useAuthStore();
const router = useRouter();
const form = ref({ name: '', email: '', password: '', password_confirmation: '' });
const loading = ref(false);
const errors = ref(null);

async function handleRegister() {
  loading.value = true;
  errors.value = null;
  try {
    const { data } = await axios.post('/api/register', form.value);
    auth.setAuth(data.user, data.token);
    router.push({ name: 'rooms' });
  } catch (e) {
    errors.value = e.response?.data?.errors || { general: [e.response?.data?.message || 'Registration failed.'] };
  } finally {
    loading.value = false;
  }
}
</script>
