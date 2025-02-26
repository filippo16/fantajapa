<template>
    <div v-if="isVisible" class="popup-overlay d-flex justify-content-center align-items-center">
        <div class="popup-1 bg-white p-4 rounded shadow-lg">
            <div v-if="!showList">
                <input v-model="inputValue" type="text" class="form-control mb-3" placeholder="Scrivi qui..." />
                <button @click="submitName" class="btn btn-primary w-100">Invia</button>
            </div>
            <div v-else class="row">
                <div v-for="(category, categoryIndex) in names" :key="categoryIndex" class="col-md-4 mb-4">
                    <h5>{{ category.type }}</h5>
                    <div v-for="(name, nameIndex) in category.names" :key="nameIndex" class="form-check">
                        <input type="checkbox" :id="name" :value="name" v-model="selectedNames" :disabled="selectedNames.length >= 6 && !selectedNames.includes(name)" class="form-check-input" />
                        <label :for="name" class="form-check-label">{{ name }}</label>
                    </div>
                </div>
                <button @click="submitNames" class="btn btn-primary w-100">Inserisci squadra</button>
            </div>
        </div>
    </div>
</template>

<script lang="ts">
import { defineComponent } from 'vue';

export default defineComponent({
    name: 'Popup',
    props: {
        isVisible: {
            type: Boolean,
            required: true,
        },
        showList: {
            type: Boolean,
            required: true
        }
    },
    data() {
        return {
            inputValue: "",
            selectedNames: [] as string[],
            names: [
                { type: 'Top donator', names: ['Dark', 'Ingrippao', 'Gatto', 'Dile', 'Mira', 'Nome89'] },
                { type: 'Mod', names: ['Lissa', 'Zuzi', 'Juri', 'Gaia', 'Ciao', 'Nome16'] },
                { type: 'Spect', names: ['Pippo', 'Crimi', 'Horit', 'Nome11', 'Nome14', 'Nome17'] }
            ],
        };
    },
    methods: {
        submitName() {
            this.$emit('submitName', this.inputValue);
        },
        submitNames() {
            console.log(this.selectedNames);
            this.$emit('submit', this.inputValue, this.selectedNames);
        }
    }
});
</script>

<style scoped>
.popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    z-index: 1000;
}

.popup-1 {
    z-index: 1001;
    max-width: 800px;
    width: 100%;
}

.row {
    display: flex;
    flex-wrap: wrap;
}

.col-md-4 {
    flex: 0 0 33.3333%;
    max-width: 33.3333%;
}
</style>