<template>
  <div class="max-w-xl mx-auto px-4 py-10">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-8">{{ $t('room.create') }}</h1>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow p-8 space-y-6">
      <form @submit.prevent="createRoom" class="space-y-5">
        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ $t('room.name') }}</label>
          <input v-model="form.name" type="text" required
            class="w-full px-4 py-2.5 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500" />
        </div>

        <div>
          <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Card Deck</label>
          <div class="flex flex-wrap gap-2 mb-2">
            <span v-for="(card, i) in form.card_config" :key="i"
              class="bg-indigo-50 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 px-3 py-1 rounded-lg text-sm font-mono flex items-center gap-1">
              {{ card }}
              <button type="button" @click="removeCard(i)" class="text-indigo-400 hover:text-red-500 font-bold ml-1">×</button>
            </span>
          </div>
          <div class="flex gap-2">
            <input v-model="newCard" type="text" placeholder="Add card value" maxlength="5"
              class="flex-1 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
            <button type="button" @click="addCard"
              class="bg-indigo-100 hover:bg-indigo-200 dark:bg-indigo-800 dark:hover:bg-indigo-700 text-indigo-700 dark:text-indigo-300 px-3 py-1.5 rounded-lg text-sm font-semibold transition">
              Add
            </button>
          </div>
          <p class="text-xs text-gray-400 mt-1">Default: Fibonacci (0,1,2,3,5,8,13,21,?)</p>
        </div>

        <div v-if="error" class="bg-red-50 dark:bg-red-900/30 text-red-600 dark:text-red-400 text-sm px-4 py-3 rounded-lg">
          {{ error }}
        </div>

        <div class="flex gap-3">
          <button type="submit" :disabled="loading"
            class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white py-2.5 rounded-lg font-semibold transition">
            <span v-if="loading">{{ $t('common.loading') }}</span>
            <span v-else>{{ $t('room.createButton') }}</span>
          </button>
          <RouterLink to="/rooms"
            class="px-6 py-2.5 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 rounded-lg font-semibold hover:bg-gray-50 dark:hover:bg-gray-700 transition text-center">
            {{ $t('common.cancel') }}
          </RouterLink>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import axios from 'axios';
import { useRoomStore } from '../../stores/room';

const router = useRouter();
const roomStore = useRoomStore();
const form = ref({
  name: '',
  card_config: [0, 1, 2, 3, 5, 8, 13, 21, '?'],
});
const newCard = ref('');
const loading = ref(false);
const error = ref('');

function addCard() {
  if (newCard.value.trim()) {
    const v = newCard.value.trim();
    form.value.card_config.push(isNaN(Number(v)) ? v : Number(v));
    newCard.value = '';
  }
}

function removeCard(index) {
  form.value.card_config.splice(index, 1);
}

async function createRoom() {
  loading.value = true;
  error.value = '';
  try {
    const { data } = await axios.post('/api/rooms', form.value);
    roomStore.setRoom(data);
    router.push(`/rooms/${data.id}`);
  } catch (e) {
    error.value = e.response?.data?.message || 'Failed to create room.';
  } finally {
    loading.value = false;
  }
}
</script>
