<template>
  <div class="fc-layout">
    <!-- Left: flip card + controls -->
    <div class="fc-main">
      <div class="fc-progress-row">
        <span class="fc-progress-label">Card {{ currentIndex + 1 }} of {{ total }}</span>
        <div class="fc-progress-track">
          <div class="fc-progress-fill" :style="{ width: percent + '%' }"></div>
        </div>
        <span class="fc-progress-pct">{{ percent }}%</span>
      </div>

      <div class="flip-card fc-card" :class="{ flipped: isFlipped }" @click="flip">
        <div class="flip-card-inner">
          <div class="flip-card-front fc-face">
            <span class="fc-cat">{{ currentCard?.category }}</span>
            <span class="fc-flip-hint">Tap to flip <ic-repeat :size="11" /></span>
            <div class="fc-icon"><ic-brain :size="26" color="#fff" /></div>
            <div class="fc-face-label">QUESTION</div>
            <div class="flip-card-text">{{ currentCard?.question }}</div>
            <span class="fc-foot">Card {{ currentIndex + 1 }} of {{ total }}</span>
          </div>
          <div class="flip-card-back fc-face">
            <span class="fc-cat">Answer</span>
            <div class="fc-face-label">ANSWER</div>
            <div class="flip-card-text">{{ currentCard?.answer }}</div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="fc-actions">
        <button type="button" class="btn-ghost" @click.stop="saveForLater"><ic-bookmark :size="15" /> Save for Later</button>
        <button type="button" class="btn-grad" @click.stop="markMastered"><ic-check :size="15" /> Mark as Mastered</button>
      </div>

      <!-- Navigation -->
      <div class="fc-nav">
        <button type="button" class="btn-ghost" @click="prev" :disabled="currentIndex === 0">← Previous</button>
        <button type="button" class="btn-grad" @click="next" :disabled="currentIndex >= total - 1">Next →</button>
      </div>

      <!-- Dots -->
      <div class="fc-dots">
        <span v-for="(c, idx) in cards" :key="idx" class="fc-dot" :class="dotClass(idx, c)"></span>
      </div>

      <!-- Legend -->
      <div class="fc-legend">
        <span><i class="fc-key is-current"></i> Current</span>
        <span><i class="fc-key is-mastered"></i> Mastered</span>
        <span><i class="fc-key is-saved"></i> Saved</span>
        <span><i class="fc-key is-unseen"></i> Unseen</span>
      </div>
    </div>

    <!-- Right: all cards list -->
    <aside class="fc-sidebar">
      <div class="fc-sidebar-head">
        <span class="fc-sidebar-title"><ic-list-checks :size="15" /> All Cards</span>
        <span class="fc-sidebar-count">{{ total }} total</span>
      </div>
      <div class="fc-list">
        <button type="button" v-for="(c, idx) in cards" :key="c.id ?? idx"
                class="fc-list-item" :class="{ active: idx === currentIndex }"
                @click="goTo(idx)">
          <span class="fc-num" :class="numClass(idx, c)">{{ idx + 1 }}</span>
          <span class="fc-list-title">{{ c.question }}</span>
          <span class="fc-list-icon" :class="numClass(idx, c)"><component v-if="statusIcon(idx, c)" :is="statusIcon(idx, c)" :size="13" /></span>
        </button>
      </div>
    </aside>
  </div>
</template>

<script>
import { ref, computed } from 'vue';

export default {
  name: 'FlashcardDeck',
  props: {
    cards: { type: Array, default: () => [] },
    deckId: { type: [String, Number], default: null },
  },
  setup(props) {
    const currentIndex = ref(0);
    const isFlipped = ref(false);

    const total = computed(() => props.cards.length);
    const currentCard = computed(() => props.cards[currentIndex.value]);
    const percent = computed(() => total.value ? Math.round((currentIndex.value + 1) / total.value * 100) : 0);

    function flip() { isFlipped.value = !isFlipped.value; }
    function goTo(idx) { currentIndex.value = idx; isFlipped.value = false; }
    function next() { if (currentIndex.value < total.value - 1) goTo(currentIndex.value + 1); }
    function prev() { if (currentIndex.value > 0) goTo(currentIndex.value - 1); }

    function persist(card) {
      if (props.deckId == null || card?.id == null) return;
      axios.patch(`/api/flashcards/${props.deckId}/cards/${card.id}`, { status: card.status }).catch(() => {});
    }

    function saveForLater() {
      const card = props.cards[currentIndex.value];
      if (card) { card.status = 'saved'; persist(card); next(); }
    }

    function markMastered() {
      const card = props.cards[currentIndex.value];
      if (card) { card.status = 'mastered'; persist(card); next(); }
    }

    // Status helpers — "current" takes precedence over the stored status.
    function statusKey(idx, card) {
      if (idx === currentIndex.value) return 'current';
      if (card.status === 'mastered') return 'mastered';
      if (card.status === 'saved') return 'saved';
      return 'unseen';
    }
    function numClass(idx, card) { return 'is-' + statusKey(idx, card); }
    function dotClass(idx, card) { return 'fc-dot ' + 'is-' + statusKey(idx, card); }
    function statusIcon(idx, card) {
      const k = statusKey(idx, card);
      return { current: null, mastered: 'ic-check', saved: 'ic-bookmark', unseen: null }[k];
    }

    return {
      currentIndex, isFlipped, total, currentCard, percent,
      flip, goTo, next, prev, saveForLater, markMastered,
      numClass, dotClass, statusIcon,
    };
  },
};
</script>
