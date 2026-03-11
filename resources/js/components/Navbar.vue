<template>
  <nav class="bg-indigo-700 dark:bg-gray-800 shadow-lg">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between h-16">
        <!-- Logo / Brand -->
        <RouterLink to="/" class="flex items-center gap-2 text-white font-bold text-xl">
          🃏 {{ $t('app.name') }}
        </RouterLink>

        <!-- Desktop navigation -->
        <div class="hidden md:flex items-center gap-4">
          <template v-if="auth.isLoggedIn">
            <RouterLink to="/rooms" class="text-indigo-100 hover:text-white transition">
              {{ $t('room.myRooms') }}
            </RouterLink>
            <RouterLink to="/profile" class="flex items-center gap-2 text-indigo-100 hover:text-white transition">
              <img v-if="auth.user?.avatar" :src="`/storage/${auth.user.avatar}`" class="w-7 h-7 rounded-full object-cover" />
              <span v-else class="w-7 h-7 rounded-full bg-indigo-400 flex items-center justify-center text-white text-sm font-bold">
                {{ auth.user?.name?.charAt(0)?.toUpperCase() }}
              </span>
              {{ auth.user?.name }}
            </RouterLink>
            <button @click="logout" class="text-indigo-100 hover:text-white transition">
              {{ $t('nav.logout') }}
            </button>
          </template>
          <template v-else>
            <RouterLink to="/login" class="text-indigo-100 hover:text-white transition">{{ $t('nav.login') }}</RouterLink>
            <RouterLink to="/register" class="bg-white text-indigo-700 hover:bg-indigo-50 px-4 py-2 rounded-lg font-semibold transition">
              {{ $t('nav.register') }}
            </RouterLink>
          </template>

          <!-- Dark Mode Toggle -->
          <button @click="toggleDark()" class="text-indigo-100 hover:text-white transition p-1 rounded-full" :title="isDark ? 'Light mode' : 'Dark mode'">
            <span v-if="isDark">☀️</span>
            <span v-else>🌙</span>
          </button>

          <!-- Language Toggle -->
          <button @click="toggleLocale" class="text-indigo-100 hover:text-white text-sm font-medium border border-indigo-400 rounded px-2 py-1 transition">
            {{ locale === 'en' ? 'ES' : 'EN' }}
          </button>
        </div>

        <!-- Mobile hamburger -->
        <button @click="mobileOpen = !mobileOpen" class="md:hidden text-white">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
          </svg>
        </button>
      </div>

      <!-- Mobile menu -->
      <div v-show="mobileOpen" class="md:hidden pb-4 flex flex-col gap-3">
        <template v-if="auth.isLoggedIn">
          <RouterLink to="/rooms" @click="mobileOpen=false" class="text-indigo-100">{{ $t('room.myRooms') }}</RouterLink>
          <RouterLink to="/profile" @click="mobileOpen=false" class="text-indigo-100">{{ $t('nav.profile') }}</RouterLink>
          <button @click="logout" class="text-indigo-100 text-left">{{ $t('nav.logout') }}</button>
        </template>
        <template v-else>
          <RouterLink to="/login" @click="mobileOpen=false" class="text-indigo-100">{{ $t('nav.login') }}</RouterLink>
          <RouterLink to="/register" @click="mobileOpen=false" class="text-indigo-100">{{ $t('nav.register') }}</RouterLink>
        </template>
        <div class="flex gap-3 items-center">
          <button @click="toggleDark()" class="text-indigo-100">{{ isDark ? '☀️' : '🌙' }}</button>
          <button @click="toggleLocale" class="text-indigo-100 text-sm border border-indigo-400 rounded px-2 py-1">{{ locale === 'en' ? 'ES' : 'EN' }}</button>
        </div>
      </div>
    </div>
  </nav>
</template>

<script setup>
import { ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { useDark, useToggle } from '@vueuse/core';
import { useI18n } from 'vue-i18n';
import { useAuthStore } from '../stores/auth';
import axios from 'axios';

const isDark = useDark();
const toggleDark = useToggle(isDark);
const { locale } = useI18n();
const auth = useAuthStore();
const router = useRouter();
const mobileOpen = ref(false);

function toggleLocale() {
  locale.value = locale.value === 'en' ? 'es' : 'en';
  localStorage.setItem('locale', locale.value);
}

async function logout() {
  try { await axios.post('/api/logout'); } catch {}
  auth.clearAuth();
  router.push({ name: 'login' });
}
</script>
