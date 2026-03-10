<template>
  <div class="max-w-5xl mx-auto px-4 py-10">
    <div class="flex items-center justify-between mb-8">
      <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $t('room.myRooms') }}</h1>
      <div class="flex gap-3">
        <RouterLink to="/rooms/create"
          class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-lg font-semibold transition">
          + {{ $t('room.create') }}
        </RouterLink>
      </div>
    </div>

    <!-- Join by code -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 mb-8 flex flex-col sm:flex-row gap-3">
      <input v-model="joinCode" type="text" :placeholder="$t('room.enterCode')" maxlength="8"
        class="flex-1 px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white uppercase tracking-widest focus:outline-none focus:ring-2 focus:ring-indigo-500" />
      <button @click="joinRoom" :disabled="!joinCode || joining"
        class="bg-green-600 hover:bg-green-700 disabled:opacity-60 text-white px-6 py-2 rounded-lg font-semibold transition">
        {{ $t('room.joinButton') }}
      </button>
    </div>

    <!-- Rooms grid -->
    <div v-if="loading" class="flex justify-center py-20">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
    </div>
    <div v-else-if="rooms.length === 0" class="text-center py-20 text-gray-400 dark:text-gray-500">
      <div class="text-6xl mb-4">🃏</div>
      <p class="text-lg">{{ $t('room.noRooms') }}</p>
    </div>
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <RouterLink v-for="room in rooms" :key="room.id" :to="`/rooms/${room.id}`"
        class="bg-white dark:bg-gray-800 rounded-xl shadow hover:shadow-md transition p-5 border border-transparent hover:border-indigo-200 dark:hover:border-indigo-700">
        <div class="flex items-center gap-3 mb-3">
          <img v-if="room.logo" :src="`/storage/${room.logo}`" class="w-10 h-10 rounded-lg object-cover" />
          <div v-else class="w-10 h-10 rounded-lg bg-indigo-100 dark:bg-indigo-800 flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-bold text-lg">
            {{ room.name.charAt(0) }}
          </div>
          <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900 dark:text-white truncate">{{ room.name }}</p>
            <p class="text-xs text-gray-500 dark:text-gray-400">{{ room.host?.name }}</p>
          </div>
        </div>
        <div class="flex items-center justify-between text-sm">
          <span :class="stateBadge(room.state)" class="px-2 py-0.5 rounded-full text-xs font-semibold">
            {{ $t(`room.${room.state}`) }}
          </span>
          <span class="text-gray-500 dark:text-gray-400 font-mono text-xs tracking-widest">{{ room.code }}</span>
        </div>
      </RouterLink>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import axios from 'axios';

const rooms = ref([]);
const loading = ref(true);
const joinCode = ref('');
const joining = ref(false);
const router = useRouter();

const stateBadge = (state) => ({
  waiting: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
  voting: 'bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-300',
  discussion: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-800 dark:text-yellow-300',
  break: 'bg-blue-100 text-blue-700 dark:bg-blue-800 dark:text-blue-300',
}[state] || 'bg-gray-100 text-gray-600');

onMounted(async () => {
  try {
    const { data } = await axios.get('/api/rooms');
    rooms.value = data.data || data;
  } finally {
    loading.value = false;
  }
});

async function joinRoom() {
  if (!joinCode.value) return;
  joining.value = true;
  try {
    const { data } = await axios.post(`/api/rooms/join/${joinCode.value.toUpperCase()}`);
    router.push(`/rooms/${data.id}`);
  } catch (e) {
    alert(e.response?.data?.message || 'Room not found.');
  } finally {
    joining.value = false;
  }
}
</script>
