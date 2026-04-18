<template>
  <div id="app" class="app-container">
    <router-view />
  </div>
</template>

<script>
export default {
  name: 'App'
};
</script>

<style scoped>
.app-container {
  width: 100%;
  height: 100vh;
  background: #f5f5f5;
}
</style>
