<template>
  <div v-if="loading" class="flex justify-center py-20">
    <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
  </div>

  <div v-else-if="room" class="max-w-7xl mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-6">
      <div class="flex items-center gap-3 flex-1">
        <img v-if="room.logo" :src="`/storage/${room.logo}`" class="w-12 h-12 rounded-xl object-cover" />
        <div v-else class="w-12 h-12 rounded-xl bg-indigo-100 dark:bg-indigo-800 flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-bold text-xl">
          {{ room.name.charAt(0) }}
        </div>
        <div>
          <h1 class="text-xl font-bold text-gray-900 dark:text-white">{{ room.name }}</h1>
          <div class="flex items-center gap-2">
            <span :class="stateBadge(room.state)" class="px-2 py-0.5 rounded-full text-xs font-semibold">
              {{ $t(`room.${room.state}`) }}
            </span>
            <span class="text-gray-500 dark:text-gray-400 text-xs font-mono tracking-widest">{{ room.code }}</span>
          </div>
        </div>
      </div>

      <!-- Host controls -->
      <div v-if="isHost" class="flex flex-wrap gap-2">
        <select v-model="selectedState" @change="changeState"
          class="text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-1.5 dark:bg-gray-700 dark:text-white focus:outline-none focus:ring-2 focus:ring-indigo-500">
          <option value="waiting">{{ $t('room.waiting') }}</option>
          <option value="voting">{{ $t('room.voting') }}</option>
          <option value="discussion">{{ $t('room.discussion') }}</option>
          <option value="break">{{ $t('room.break') }}</option>
        </select>
        <button @click="newSession"
          class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-1.5 rounded-lg font-semibold transition">
          + {{ $t('vote.newSession') }}
        </button>
        <button v-if="session && session.status === 'open'" @click="reveal"
          :disabled="revealPending"
          :class="revealPending ? 'opacity-60 cursor-not-allowed' : ''"
          class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-1.5 rounded-lg font-semibold transition">
          {{ $t('vote.revealCards') }}
        </button>
        <button @click="toggleEmojis"
          :class="room.emojis_blocked ? 'bg-red-100 text-red-700 dark:bg-red-900 dark:text-red-300' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300'"
          class="text-sm px-3 py-1.5 rounded-lg font-semibold transition">
          {{ room.emojis_blocked ? '🚫' : '😊' }} {{ $t('room.toggleEmojis') }}
        </button>
      </div>

      <!-- Copy link -->
      <button @click="copyLink" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline flex items-center gap-1">
        🔗 {{ linkCopied ? $t('room.linkCopied') : $t('room.copyLink') }}
      </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
      <!-- Main voting area -->
      <div class="lg:col-span-3 space-y-6">
        <!-- Session info -->
        <div v-if="session" class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
          <div class="flex items-center justify-between mb-3">
            <h2 class="font-bold text-gray-900 dark:text-white">
              {{ session.story_title || $t('vote.newSession') }}
            </h2>
            <span :class="sessionStatusBadge(session.status)" class="px-2 py-0.5 rounded-full text-xs font-semibold">
              {{ session.status }}
            </span>
          </div>
          <p v-if="session.story_description" class="text-sm text-gray-600 dark:text-gray-400 mb-4">{{ session.story_description }}</p>

          <!-- Countdown overlay -->
          <div v-if="roomStore.countdown" class="flex items-center justify-center py-8">
            <div class="text-8xl font-black text-indigo-600 dark:text-indigo-400 animate-bounce">
              {{ roomStore.countdown }}
            </div>
          </div>

          <!-- Revealed votes -->
          <div v-else-if="roomStore.revealed && session.votes" class="space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-lg font-semibold text-gray-700 dark:text-gray-300">{{ $t('vote.average') }}:</span>
              <span class="text-3xl font-black text-indigo-600 dark:text-indigo-400">{{ session.average ?? '?' }}</span>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
              <div v-for="vote in session.votes" :key="vote.id"
                class="bg-indigo-50 dark:bg-indigo-900/30 rounded-xl p-3 text-center border border-indigo-100 dark:border-indigo-800">
                <div class="w-8 h-8 rounded-full bg-indigo-100 dark:bg-indigo-800 flex items-center justify-center text-indigo-700 dark:text-indigo-300 font-bold text-sm mx-auto mb-2">
                  {{ vote.user?.name?.charAt(0)?.toUpperCase() }}
                </div>
                <p class="text-xs text-gray-600 dark:text-gray-400 truncate mb-1">{{ vote.user?.name }}</p>
                <p class="text-2xl font-black text-indigo-600 dark:text-indigo-300">{{ vote.value }}</p>
              </div>
            </div>
          </div>

          <!-- Voting in progress: show who voted (not values) -->
          <div v-else-if="session.status === 'open'" class="py-4">
            <p class="text-sm text-gray-500 dark:text-gray-400 text-center">{{ $t('vote.waitingReveal') }}</p>
          </div>
        </div>

        <!-- Voting cards -->
        <div v-if="session && session.status === 'open' && !roomStore.revealed" class="bg-white dark:bg-gray-800 rounded-xl shadow p-5">
          <p class="text-sm font-medium text-gray-600 dark:text-gray-400 mb-4">{{ $t('vote.yourVote') }}</p>
          <div class="flex flex-wrap gap-3 justify-center">
            <button v-for="card in room.card_config" :key="card"
              @click="castVote(String(card))"
              :class="[
                'w-16 h-24 rounded-xl font-black text-xl transition-all shadow',
                roomStore.myVote === String(card)
                  ? 'bg-indigo-600 text-white scale-110 shadow-indigo-300 dark:shadow-indigo-800 shadow-lg'
                  : 'bg-white dark:bg-gray-700 text-gray-800 dark:text-white border-2 border-gray-200 dark:border-gray-600 hover:border-indigo-400 hover:scale-105'
              ]">
              {{ card }}
            </button>
          </div>
        </div>

        <!-- Emoji bar -->
        <div v-if="!room.emojis_blocked" class="bg-white dark:bg-gray-800 rounded-xl shadow p-4">
          <div class="flex gap-2 flex-wrap justify-center">
            <button v-for="e in availableEmojis" :key="e" @click="sendEmoji(e)"
              class="text-2xl hover:scale-125 transition-transform p-1 rounded">{{ e }}</button>
          </div>
        </div>
      </div>

      <!-- Participants sidebar -->
      <div class="lg:col-span-1">
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow p-5 sticky top-6">
          <h3 class="font-semibold text-gray-700 dark:text-gray-300 mb-4">
            {{ $t('room.participants') }} ({{ onlineUsers.length }})
          </h3>
          <div class="space-y-3">
            <div v-for="user in room.users" :key="user.id" class="flex items-center gap-3">
              <div class="relative">
                <img v-if="user.avatar" :src="`/storage/${user.avatar}`" class="w-9 h-9 rounded-full object-cover" />
                <div v-else class="w-9 h-9 rounded-full bg-indigo-100 dark:bg-indigo-800 flex items-center justify-center text-indigo-600 dark:text-indigo-300 font-bold">
                  {{ user.name?.charAt(0)?.toUpperCase() }}
                </div>
                <div :class="user._online !== false ? 'bg-green-400' : 'bg-gray-300'" class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full border-2 border-white dark:border-gray-800"></div>
                <div
                  v-if="user._reaction"
                  class="absolute -top-5 left-1/2 -translate-x-1/2 text-2xl pointer-events-none animate-[emoji-pop_1.2s_ease-out]"
                >
                  {{ user._reaction }}
                </div>
              </div>
              <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                  {{ user.name }}
                  <span v-if="user.id === room.host_id" class="ml-1 text-xs text-indigo-500">👑</span>
                </p>
              </div>
              <!-- Voted indicator -->
              <div v-if="session && session.status === 'open' && !roomStore.revealed">
                <span v-if="user._voted" class="text-green-500 text-lg">✓</span>
                <span v-else class="text-gray-300 dark:text-gray-600 text-lg">○</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<style scoped>
@keyframes emoji-pop {
  0% { transform: translate(-50%, 8px) scale(0.8); opacity: 0; }
  20% { opacity: 1; }
  100% { transform: translate(-50%, -24px) scale(1.15); opacity: 0; }
}
</style>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';
import { useRoomStore } from '../../stores/room';
import { useAuthStore } from '../../stores/auth';

const route = useRoute();
const roomStore = useRoomStore();
const authStore = useAuthStore();
const loading = ref(true);
const linkCopied = ref(false);
const revealPending = ref(false);
const selectedState = ref('waiting');
const availableEmojis = ['👍', '👎', '🎉', '😂', '🤔', '🔥', '❤️', '😮', '👏', '🚀'];

const room = computed(() => roomStore.currentRoom);
const session = computed(() => roomStore.currentSession);
const isHost = computed(() => room.value?.host_id === authStore.user?.id);
const onlineUsers = computed(() => room.value?.users?.filter(u => u._online !== false) || []);

const stateBadge = (s) => ({
  waiting: 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
  voting: 'bg-green-100 text-green-700 dark:bg-green-800 dark:text-green-300',
  discussion: 'bg-yellow-100 text-yellow-700',
  break: 'bg-blue-100 text-blue-700',
}[s] || 'bg-gray-100 text-gray-600');

const sessionStatusBadge = (s) => ({
  open: 'bg-green-100 text-green-700',
  revealing: 'bg-yellow-100 text-yellow-700',
  revealed: 'bg-blue-100 text-blue-700',
  closed: 'bg-gray-100 text-gray-600',
}[s] || 'bg-gray-100 text-gray-600');

let channel = null;

onMounted(async () => {
  await roomStore.loadRoom(route.params.id);
  selectedState.value = room.value?.state || 'waiting';
  loading.value = false;

  // Subscribe to WebSocket presence channel
  channel = window.Echo.join(`room.${route.params.id}`)
    .here((users) => {
      users.forEach(u => roomStore.addUser({ ...u, _online: true }));
    })
    .joining((user) => {
      roomStore.addUser({ ...user, _online: true });
    })
    .leaving((user) => {
      if (roomStore.currentRoom?.users) {
        roomStore.currentRoom.users = roomStore.currentRoom.users.map(u =>
          u.id === user.id ? { ...u, _online: false } : u
        );
      }
    })
    .listen('.session.started', ({ session }) => {
      roomStore.setSession(session);
    })
    .listen('.vote.submitted', ({ user_id }) => {
      roomStore.markUserVoted(user_id);
    })
    .listen('.reveal.started', ({ votes, average }) => {
      revealPending.value = false;
      if (roomStore.currentSession) {
        roomStore.currentSession.votes = votes;
        roomStore.currentSession.average = average;
      }
      roomStore.startReveal({ votes, average });
    })
    .listen('.emoji.sent', ({ emoji, sender_id }) => {
      roomStore.setUserReaction(sender_id, emoji);
    })
    .listen('.room.state_changed', ({ state }) => {
      roomStore.updateRoomState(state);
      selectedState.value = state;
    });
});

onUnmounted(() => {
  if (channel) {
    window.Echo.leave(`room.${route.params.id}`);
  }
});

async function castVote(value) {
  if (!session.value) return;
  roomStore.setMyVote(value);
  try {
    await axios.post(`/api/sessions/${session.value.id}/vote`, { value });
    roomStore.markUserVoted(authStore.user.id);
  } catch {}
}

async function reveal() {
  if (!session.value || revealPending.value) return;
  revealPending.value = true;
  try {
    await axios.post(`/api/sessions/${session.value.id}/reveal`);
  } catch {
    revealPending.value = false;
  }
}

async function newSession() {
  try {
    const { data } = await axios.post(`/api/rooms/${room.value.id}/sessions`, {
      story_title: prompt('Story title (optional):') || null,
    });
    roomStore.setSession(data);
  } catch {}
}

async function changeState() {
  try {
    await axios.patch(`/api/rooms/${room.value.id}/state`, { state: selectedState.value });
  } catch {}
}

async function toggleEmojis() {
  try {
    const { data } = await axios.patch(`/api/rooms/${room.value.id}/toggle-emojis`);
    if (roomStore.currentRoom) roomStore.currentRoom.emojis_blocked = data.emojis_blocked;
  } catch {}
}

async function sendEmoji(emoji) {
  if (!room.value || room.value.emojis_blocked) return;
  try {
    await axios.post(`/api/rooms/${room.value.id}/emojis`, { emoji });
  } catch {}
}

async function copyLink() {
  await navigator.clipboard.writeText(window.location.href);
  linkCopied.value = true;
  setTimeout(() => { linkCopied.value = false; }, 2000);
}
</script>
