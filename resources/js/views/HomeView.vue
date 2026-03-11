<template>
  <div class="min-h-screen bg-gradient-to-br from-indigo-600 via-purple-600 to-pink-500 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 flex items-center justify-center px-4">
    <div class="text-center text-white max-w-2xl">
      <div class="text-8xl mb-6">🃏</div>
      <h1 class="text-5xl font-extrabold mb-4 tracking-tight">{{ $t('app.name') }}</h1>
      <p class="text-xl text-indigo-100 mb-10">{{ $t('app.tagline') }}</p>

      <div v-if="auth.isLoggedIn" class="flex flex-col sm:flex-row gap-4 justify-center">
        <RouterLink to="/rooms" class="bg-white text-indigo-700 hover:bg-indigo-50 px-8 py-3 rounded-xl font-bold text-lg shadow-lg transition">
          {{ $t('room.myRooms') }}
        </RouterLink>
        <RouterLink to="/rooms/create" class="bg-indigo-500 hover:bg-indigo-400 text-white px-8 py-3 rounded-xl font-bold text-lg shadow-lg border border-indigo-400 transition">
          {{ $t('room.create') }}
        </RouterLink>
      </div>

      <div v-else class="flex flex-col sm:flex-row gap-4 justify-center">
        <RouterLink to="/login" class="bg-white text-indigo-700 hover:bg-indigo-50 px-8 py-3 rounded-xl font-bold text-lg shadow-lg transition">
          {{ $t('auth.login') }}
        </RouterLink>
        <RouterLink to="/register" class="bg-indigo-500 hover:bg-indigo-400 text-white px-8 py-3 rounded-xl font-bold text-lg shadow-lg border border-indigo-400 transition">
          {{ $t('auth.register') }}
        </RouterLink>
      </div>

      <!-- Feature cards -->
      <div class="mt-16 grid grid-cols-1 sm:grid-cols-3 gap-6 text-left">
        <div class="bg-white/10 backdrop-blur rounded-2xl p-6">
          <div class="text-3xl mb-3">⚡</div>
          <h3 class="font-bold text-lg mb-2">Real-time Voting</h3>
          <p class="text-indigo-100 text-sm">All participants see votes instantly via WebSockets.</p>
        </div>
        <div class="bg-white/10 backdrop-blur rounded-2xl p-6">
          <div class="text-3xl mb-3">🃏</div>
          <h3 class="font-bold text-lg mb-2">Custom Card Decks</h3>
          <p class="text-indigo-100 text-sm">Fibonacci, T-Shirt sizes, or custom values for any team.</p>
        </div>
        <div class="bg-white/10 backdrop-blur rounded-2xl p-6">
          <div class="text-3xl mb-3">😄</div>
          <h3 class="font-bold text-lg mb-2">Emoji Reactions</h3>
          <p class="text-indigo-100 text-sm">Make planning sessions fun with animated emoji reactions.</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const auth = useAuthStore();
</script>
