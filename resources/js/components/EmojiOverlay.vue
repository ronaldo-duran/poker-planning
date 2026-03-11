<template>
  <Teleport to="body">
    <TransitionGroup name="emoji-fly" tag="div" class="fixed inset-0 pointer-events-none z-50 overflow-hidden">
      <div
        v-for="item in items"
        :key="item.id"
        :style="{ left: item.x + 'px', top: item.y + 'px' }"
        class="absolute text-5xl select-none animate-bounce"
      >
        {{ item.emoji }}
      </div>
    </TransitionGroup>
  </Teleport>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const items = ref([]);

function spawnEmoji(emoji) {
  const id = Date.now() + Math.random();
  const x = Math.random() * (window.innerWidth - 80);
  const y = Math.random() * (window.innerHeight - 80);
  items.value.push({ id, emoji, x, y });
  setTimeout(() => {
    items.value = items.value.filter(i => i.id !== id);
  }, 2500);
}

function onPokerEmoji(e) {
  spawnEmoji(e.detail);
}

onMounted(() => {
  window.addEventListener('poker:emoji', onPokerEmoji);
});

onUnmounted(() => {
  window.removeEventListener('poker:emoji', onPokerEmoji);
});
</script>

<style scoped>
.emoji-fly-enter-active { transition: all 0.3s ease-out; }
.emoji-fly-leave-active { transition: all 0.5s ease-in; }
.emoji-fly-enter-from { opacity: 0; transform: scale(0.5); }
.emoji-fly-leave-to { opacity: 0; transform: scale(0.5) translateY(-60px); }
</style>
