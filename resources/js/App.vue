<template>
  <div :class="{ dark: isDark }" class="min-h-screen bg-white dark:bg-gray-900 transition-colors">
    <Navbar />
    <RouterView />
    <EmojiOverlay />
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { RouterView } from 'vue-router';
import Navbar from './components/Navbar.vue';
import EmojiOverlay from './components/EmojiOverlay.vue';
import { useDark } from '@vueuse/core';
import { useAuthStore } from './stores/auth';

const isDark = useDark();
const auth = useAuthStore();

onMounted(() => {
  auth.fetchUser();
});
</script>
