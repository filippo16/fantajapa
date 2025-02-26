<template>
    <div v-if="isOpen" class="modal fade show d-block" style="background-color: rgba(0, 0, 0, 0.5);">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title">Inserisci dati</h5>
            <button type="button" class="btn-close" @click="closePopup"></button>
          </div>
          <div class="modal-body">
            <select v-model="name" class="form-control mb-2">
              <option v-for="nameOption in nameOptions" :key="nameOption" :value="nameOption">{{ nameOption }}</option>
            </select>
            <input type="number" v-model="quantity" class="form-control mb-2" placeholder="Quantità" />
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" @click="closePopup">Chiudi</button>
            <button type="button" class="btn btn-primary" @click="emitData">Invia</button>
          </div>
        </div>
      </div>
    </div>
  </template>
  
  <script>
  import { defineComponent } from 'vue';
  
  export default defineComponent({
    name: 'PopupInput',
    props: {
      isOpen: {
        type: Boolean,
        default: false
      }
    },
    data() {
      return {
        name: '',
        quantity: 0,
        nameOptions: ['Dark', 'Ingrippao', 'Gatto', 'Dile', 'Mira', 'Nome89','Lissa', 'Zuzi', 'Juri', 'Gaia', 'Ciao', 'Nome16','Pippo', 'Crimi', 'Horit', 'Nome11', 'Nome14', 'Nome17']
      };
    },
    methods: {
      emitData() {
        this.$emit('dataSubmit', {
          name: this.name,
          quantity: this.quantity
        });
        this.closePopup();
      },
      closePopup() {
        this.$emit('close');
      }
    }
  });
  </script>
  
  <style scoped>
  .modal.fade.show.d-block {
    display: flex !important;
    align-items: center;
    justify-content: center;
  }
  </style>