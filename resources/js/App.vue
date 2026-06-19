<template>
  <app-layout>
    <router-view v-slot="{ Component }">
      <transition name="page-slide" mode="out-in">
        <component :is="Component" :key="$route.path" />
      </transition>
    </router-view>
  </app-layout>
</template>

<script>
export default {
  name: 'App',
};
</script>
