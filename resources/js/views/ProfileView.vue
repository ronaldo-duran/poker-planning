<template>
  <div class="max-w-2xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">{{ $t('profile.title') }}</h1>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-8 space-y-6">
      <!-- Avatar -->
      <div class="flex items-center gap-6">
        <div class="relative">
          <img v-if="avatarPreview || auth.user?.avatar"
            :src="avatarPreview || `/storage/${auth.user?.avatar}`"
            class="w-20 h-20 rounded-full object-cover border-4 border-indigo-100" />
          <div v-else class="w-20 h-20 rounded-full bg-indigo-100 dark:bg-indigo-800 flex items-center justify-center text-3xl font-bold text-indigo-600 dark:text-indigo-300">
            {{ auth.user?.name?.charAt(0)?.toUpperCase() }}
          </div>
          <label class="absolute -bottom-1 -right-1 bg-indigo-600 rounded-full p-1.5 cursor-pointer hover:bg-indigo-700 transition">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            <input type="file" accept="image/*" class="hidden" @change="onAvatarChange" />
          </label>
        </div>
        <div>
          <p class="font-semibold text-gray-900 dark:text-white">{{ auth.user?.name }}</p>
          <p class="text-sm text-gray-500 dark:text-gray-400">{{ auth.user?.email }}</p>
        </div>
      </div>

      <!-- Form -->
      <form @submit.prevent="saveProfile" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('auth.name') }}</label>
          <input v-model="form.name" type="text"
            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('profile.bio') }}</label>
          <textarea v-model="form.bio" rows="3"
            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
        </div>

        <div v-if="successMsg" class="bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-400 px-4 py-3 rounded-lg text-sm">
          {{ successMsg }}
        </div>
        <div v-if="errorMsg" class="bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 px-4 py-3 rounded-lg text-sm">
          {{ errorMsg }}
        </div>

        <button type="submit" :disabled="saving"
          class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white px-8 py-2.5 rounded-lg font-semibold transition">
          <span v-if="saving">{{ $t('common.loading') }}</span>
          <span v-else>{{ $t('profile.saveChanges') }}</span>
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';
import { useAuthStore } from '../stores/auth';
import { useI18n } from 'vue-i18n';

const { t } = useI18n();
const auth = useAuthStore();
const form = ref({ name: auth.user?.name || '', bio: auth.user?.bio || '' });
const avatarFile = ref(null);
const avatarPreview = ref(null);
const saving = ref(false);
const successMsg = ref('');
const errorMsg = ref('');

function onAvatarChange(e) {
  const file = e.target.files[0];
  if (!file) return;
  avatarFile.value = file;
  avatarPreview.value = URL.createObjectURL(file);
}

async function saveProfile() {
  saving.value = true;
  successMsg.value = '';
  errorMsg.value = '';
  try {
    const formData = new FormData();
    formData.append('name', form.value.name);
    formData.append('bio', form.value.bio || '');
    if (avatarFile.value) formData.append('avatar', avatarFile.value);
    formData.append('_method', 'POST');
    const { data } = await axios.post('/api/profile', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    });
    auth.setAuth(data, auth.token);
    successMsg.value = t('profile.saved');
    avatarFile.value = null;
    avatarPreview.value = null;
  } catch (e) {
    errorMsg.value = e.response?.data?.message || t('common.error');
  } finally {
    saving.value = false;
  }
}
</script>
