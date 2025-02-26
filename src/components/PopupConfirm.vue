<template>
  <div v-if="isVisible" class="popup-overlay d-flex justify-content-center align-items-center">
    <div class="popup bg-white p-4 rounded shadow-lg">
      <h5 class="mb-3">Vuoi confermare l'azione?</h5>
      <div class="buttons d-flex justify-content-between mt-4">
        <button @click="$emit('confirm')" class="btn btn-success">Conferma</button>
        <button @click="$emit('close')" class="btn btn-secondary">Annulla</button>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
  name: 'PopupConfirm',
  props: {
    isVisible: {
      type: Boolean,
      required: true,
    }
  },
});
</script>

<style scoped>
.popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1050;
}
.popup {
  max-width: 400px;
  width: 90%;
}
</style>