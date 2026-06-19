<template>
  <div class="onboarding-wrap">
    <div class="onboarding-left">
      <div class="onboarding-badge">
        <span v-if="currentStep === 1">Step 1 of 4</span>
        <span v-else-if="currentStep === 2">Step 2 of 4</span>
        <span v-else-if="currentStep === 3">Step 3 of 4</span>
        <span v-else>Step 4 of 4</span>
      </div>

      <h1 class="onboarding-headline">
        <template v-if="currentStep === 1">What brings you here?</template>
        <template v-else-if="currentStep === 2">How do you like to learn?</template>
        <template v-else-if="currentStep === 3">Let's make learning accessible</template>
        <template v-else>You're all set!</template>
      </h1>

      <p class="onboarding-subhead" :style="{ color: 'var(--text-muted)' }">
        <template v-if="currentStep === 1">
          Pick up to 3 goals so we can tailor your experience.
        </template>
        <template v-else-if="currentStep === 2">
          Choose a pace that fits your daily routine.
        </template>
        <template v-else-if="currentStep === 3">
          Turn on any features that help you learn better.
        </template>
        <template v-else>
          Your preferences have been saved. Ready to start learning?
        </template>
      </p>

      <div class="onboarding-steps">
        <div
          v-for="step in 4"
          :key="step"
          class="step-indicator"
          :class="{ active: step === currentStep, completed: step < currentStep }"
        >
          <div class="step-dot"></div>
          <span class="step-label">
            <template v-if="step === 1">Goals</template>
            <template v-else-if="step === 2">Pace</template>
            <template v-else-if="step === 3">Accessibility</template>
            <template v-else>Done</template>
          </span>
        </div>
      </div>
    </div>

    <div class="onboarding-right">
      <div class="onboarding-card">
        <div class="onboarding-progress">
          <div
            v-for="step in 4"
            :key="step"
            class="progress-dot"
            :class="{ active: step === currentStep, completed: step < currentStep }"
          ></div>
        </div>

        <div class="onboarding-content">
          <!-- Step 1: Goals -->
          <div v-if="currentStep === 1" class="goal-grid">
            <button
              v-for="goal in goalsList"
              :key="goal"
              class="goal-tile"
              :class="{ selected: selectedGoals.includes(goal) }"
              :style="{
                borderColor: selectedGoals.includes(goal) ? 'var(--purple-bright)' : 'var(--card-border)',
              }"
              :disabled="!selectedGoals.includes(goal) && selectedGoals.length >= 3"
              @click="toggleGoal(goal)"
            >
              {{ goal }}
            </button>
          </div>

          <!-- Step 2: Pace -->
          <div v-if="currentStep === 2" class="pace-options">
            <button
              v-for="pace in paceList"
              :key="pace.value"
              class="pace-card"
              :class="{ selected: selectedPace === pace.value }"
              :style="{
                borderColor: selectedPace === pace.value ? 'var(--purple-bright)' : 'var(--card-border)',
              }"
              @click="selectedPace = pace.value"
            >
              <span class="pace-label">{{ pace.label }}</span>
              <span class="pace-desc" :style="{ color: 'var(--text-muted)' }">{{ pace.desc }}</span>
            </button>
          </div>

          <!-- Step 3: Accessibility -->
          <div v-if="currentStep === 3" class="a11y-toggles">
            <div
              v-for="toggle in a11yOptions"
              :key="toggle.key"
              class="a11y-row"
            >
              <div class="a11y-info">
                <span class="a11y-label">{{ toggle.label }}</span>
                <span class="a11y-desc" :style="{ color: 'var(--text-muted)' }">{{ toggle.desc }}</span>
              </div>
              <label class="toggle-switch">
                <input
                  type="checkbox"
                  v-model="a11yDefaults[toggle.key]"
                />
                <span class="toggle-slider"></span>
              </label>
            </div>
          </div>

          <!-- Step 4: Celebration -->
          <div v-if="currentStep === 4" class="celebration">
            <div class="celebration-icon"><ic-party :size="48" color="#9d7bff" /></div>
            <p class="celebration-text">
              Your learning profile is ready. Let's get started on your journey!
            </p>
          </div>
        </div>

        <div class="onboarding-actions">
          <a
            v-if="currentStep < 4"
            class="skip-link"
            :style="{ color: 'var(--text-muted)' }"
            href="#"
            @click.prevent="skipOnboarding"
          >
            Skip onboarding
          </a>

          <button
            v-if="currentStep < 4"
            class="continue-btn"
            :style="{ backgroundColor: 'var(--purple-bright)' }"
            :disabled="!canContinue"
            @click="nextStep"
          >
            Continue
          </button>

          <button
            v-if="currentStep === 4"
            type="button"
            class="continue-btn dashboard-link"
            :style="{ backgroundColor: 'var(--purple-bright)' }"
            @click="finishOnboarding"
          >
            Go to Dashboard
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed } from 'vue'

export default {
  name: 'Onboarding',
  setup() {
    const currentStep = ref(1)
    const selectedGoals = ref([])
    const selectedPace = ref('regular')

    const a11yDefaults = reactive({
      dyslexia_font: false,
      high_contrast: false,
      tts_enabled: false,
      simplify_language: false,
    })

    const goalsList = [
      'Career Advancement',
      'Certification Prep',
      'Personal Growth',
      'Academic Support',
      'Skill Building',
      'Curiosity & Fun',
    ]

    const paceList = [
      { label: 'Casual', value: 'casual', desc: '15 min/day' },
      { label: 'Regular', value: 'regular', desc: '30 min/day' },
      { label: 'Intensive', value: 'intensive', desc: '60 min/day' },
    ]

    const a11yOptions = [
      { key: 'dyslexia_font', label: 'Dyslexia Font', desc: 'Switch to a dyslexia-friendly typeface' },
      { key: 'high_contrast', label: 'High Contrast', desc: 'Increase contrast for better visibility' },
      { key: 'tts_enabled', label: 'Text-to-Speech', desc: 'Listen to content read aloud' },
      { key: 'simplify_language', label: 'Simplify Language', desc: 'Use plain, easy-to-read language' },
    ]

    const canContinue = computed(() => {
      if (currentStep.value === 1) {
        return selectedGoals.value.length > 0
      }
      return true
    })

    function toggleGoal(goal) {
      const idx = selectedGoals.value.indexOf(goal)
      if (idx > -1) {
        selectedGoals.value.splice(idx, 1)
      } else if (selectedGoals.value.length < 3) {
        selectedGoals.value.push(goal)
      }
    }

    function nextStep() {
      if (currentStep.value < 4) {
        currentStep.value++
      }
    }

    function skipOnboarding() {
      currentStep.value = 4
    }

    async function finishOnboarding() {
      const payload = {
        learning_goals: selectedGoals.value,
        learning_pace: selectedPace.value,
        ...a11yDefaults,
      }

      try {
        await window.axios.put('/api/user/preferences', payload)
      } catch {
        // proceed to home even if request fails
      }

      window.location.href = '/home'
    }

    return {
      currentStep,
      selectedGoals,
      selectedPace,
      a11yDefaults,
      goalsList,
      paceList,
      a11yOptions,
      canContinue,
      toggleGoal,
      nextStep,
      skipOnboarding,
      finishOnboarding,
    }
  },
}
</script>