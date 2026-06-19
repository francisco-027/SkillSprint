<template>
  <div class="route-view">
    <div class="page-header">
      <h1>{{ loading ? 'Loading...' : (deck?.title || 'Flashcards') }}</h1>
      <p>{{ masteredCount }} mastered · {{ savedCount }} saved · {{ remainingCount }} remaining</p>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="text-center py-4">
      <div class="skel" style="width:50%;max-width:300px;margin:0 auto 16px;height:20px"></div>
      <div class="skel" style="width:100%;max-width:500px;height:200px;margin:0 auto"></div>
    </div>

    <div v-if="error" class="error-box mb-4"><div class="ic">!</div><div><h5>Error</h5><p>{{ error }}</p></div></div>

    <!-- Deck -->
    <flashcard-deck v-if="!loading && !error && cards.length" :cards="cards" :deck-id="deckId" />

    <!-- Empty -->
    <div v-if="!loading && !error && !cards.length" class="empty-mid py-5">
      <div class="circle">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/></svg>
      </div>
      <h4>No flashcards yet</h4>
      <p>Generate flashcards from your study material first.</p>
    </div>
  </div>
</template>

<script>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';

export default {
  name: 'FlashcardsPage',
  setup() {
    const deckId = useRoute().params.deckId;
    const deck = ref(null);
    const cards = ref([]);
    const loading = ref(true);
    const error = ref(null);

    const masteredCount = computed(() => cards.value.filter(c => c.status === 'mastered').length);
    const savedCount = computed(() => cards.value.filter(c => c.status === 'saved').length);
    const remainingCount = computed(() => cards.value.filter(c => c.status !== 'mastered').length);

    onMounted(async () => {
      try {
        const { data } = await axios.get(`/api/flashcards/${deckId}`);
        deck.value = data.deck;
        cards.value = data.cards;
      } catch (e) {
        error.value = 'Could not load content. Please try again.';
      } finally {
        loading.value = false;
      }
    });

    return { deckId, deck, cards, loading, error, masteredCount, savedCount, remainingCount };
  },
};
</script>
