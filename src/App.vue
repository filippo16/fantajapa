<template>
  <div class="vh-100">
  <!-- Popup -->
  <Popup
    v-if="isPopupVisible"
    :isVisible="isPopupVisible"
    :showList="showListPopup"
    @submit="handleSubmit"
    @submitName="handleSubmitName"
  />
  <PopupConfirm 
    v-if="isPopupConfirmVisible"
    :isVisible="isPopupConfirmVisible"
    @confirm="handleConfirm"
    @close="closePopup"
  />
  <PopupInput
  v-if="isPopupInputVisible"
  :isOpen="isPopupInputVisible"
  @close="closePopup"
  @dataSubmit="handleInputRule"
  />

  <!-- Header -->
  <header class="bg-primary py-3 d-flex justify-content-between align-items-center">
    <h1 class="fs-4 ms-3">Ciao {{ user?.name }}</h1>
    <div class="d-flex align-items-center gap-4 me-3">
      <span class="badge bg-secondary fs-5">Punti: {{ user?.score }}</span>
      <button v-if="user" @click="logout" class="btn btn-danger d-flex align-items-center gap-2">Logout</button>
    </div>
  </header>

  <div class="container-fluid bg-secondary pt-3">
    <!--Giocatori in squadra-->
    <section class="p-4">
      <h2 class="fs-5">Giocatori in Squadra</h2>
      <div class="d-flex flex-row" style="overflow-x: auto;">
        <div 
          v-for="(player, index) in teamPlayers" 
          :key="index"
          class="card m-2"
          style="min-width: 150px;"
        >
          <div class="card-body">
            {{ player }}
          </div>
        </div>
      </div>
    </section>

    <!-- Main Content -->
    <div class="row mt-4">
      <!-- Lista Utenti -->
      <section class="col-12 col-md-3 mb-4 mx-md-4">
        <h2 class="fs-5">Lista Utenti</h2>
        <div class="list-group" style="max-height: 300px; overflow-y: auto;">
          <div 
            v-for="(u, index) in users" 
            :key="index" 
            class="list-group-item d-flex justify-content-between align-items-center">
            <span>{{ u.name }}</span>
            <span class="badge bg-secondary">{{ u.score }}</span>
          </div>
        </div>
      </section>

      <!-- Regole del Fantasanremo -->
      <section class="col-12 col-md-4 mb-4 me-md-4">
        <h2 class="fs-5">Regole del Fantasanremo</h2>
        <div class="mb-3">
          <input
            v-model="searchQuery"
            class="form-control"
            placeholder="Cerca regole..."
          />
        </div>
        <div class="list-group" style="max-height: 300px; overflow-y: auto;">
          <div 
            v-for="(rule, index) in filteredRules" 
            :key="index" 
            class="list-group-item">
            <strong>{{ rule.title }}</strong>
            <div>
              <div 
                v-for="(item, itemIndex) in rule.items" 
                :key="itemIndex" 
                class="list-group-item d-flex justify-content-between align-items-center">
                <span>{{ item.text }}</span>
                <div class="d-flex align-items-center gap-2">
                  <span 
                    :class="{'text-success': item.points > 0, 'text-danger': item.points < 0}">
                    {{ item.points }}
                  </span>
                  <button 
                    v-if="user?.role == 'mod'"
                    class="btn btn-sm btn-outline-primary"
                    @click="handleRuleClick(item)"
                    title="Aggiungi punti">
                    <i class="bi bi-plus"></i>
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Console e Registro Azioni -->
      <section class="col-12 col-md-4 mb-4">
        <h2 class="fs-5">Console e Registro Azioni</h2>
        <div class="border p-3" style="height: 200px; overflow-y: auto;">
          <div v-if="actionLogs.length > 0">
            <div 
              v-for="(action, index) in actionLogs" 
              :key="index">
              <div class="d-flex justify-content-between align-items-center">
                <!-- Action text content -->
                <div class="d-flex flex-grow-1">
                  <p class="m-0" v-if="action.typeAction == 'aggiungi'">
                    L'utente {{ action.username }} ha 
                    <span :class="{'text-success': action.points > 0, 'text-danger': action.points < 0}">
                      {{ action.points > 0 ? 'aggiunto' : 'rimosso' }} {{ Math.abs(action.points) }} punti
                    </span>
                    per: {{ action.text }}
                  </p>
                </div>
                
                <!-- Buttons group -->
                <div class="d-flex">
                  <button
                    v-if="user?.role == 'mod'"
                    class="btn btn-outline-info btn-sm" 
                    @click="deleteAction(action)"
                    title="Delete">
                    <i class="bi bi-trash"></i>
                  </button>
                </div>
              </div>
              <hr class="my-2">
            </div>
          </div>
          <p v-else class="text-muted">Nessuna azione registrata</p>
        </div>
      </section>
    </div>
  </div>
  <footer class="mt-auto py-3 text-center bg-primary">
    <div class="container-fluid">
      <span class="text-white">© 2024 Fantagioco. A scopo ludico. Sito non custodito.</span>
    </div>
  </footer>
</div>
</template>


<script lang="ts">
import { defineComponent } from 'vue';
import Cookies from 'js-cookie';
import { SignJWT } from 'jose';
import type { User, Users, ActionLog, itemRules, Rules, popupType } from './types';
import Popup from './components/Popup.vue';
import { jwtDecode } from 'jwt-decode';
import PopupConfirm from './components/PopupConfirm.vue';
import PopupInput from './components/PopupInput.vue';
import rulesData from '../public/legend.json';

export default defineComponent({
  components: { Popup, PopupConfirm, PopupInput },
  data() {
    return {
      firstLoad: true,
      user: null as User | null,
      users: [] as Users[],
      teamPlayers: [] as string[] | undefined,
      COOKIE_NAME: 'act',
      isPopupVisible: false,
      isPopupConfirmVisible: false,
      showListPopup: false,
      isPopupInputVisible: false,
      selectedAction: {} as popupType,
      selectedItem: {} as itemRules,
      rules: rulesData as Rules[],
      actionLogs: [] as ActionLog[],
      actSend: {} as ActionLog,
      eventSource: null as EventSource | null,
      calcLastId: 0,
      searchQuery: ''
    };
  },
  computed: {
    filteredRules() {
      const query = this.searchQuery.toLowerCase();
      return this.rules.map(rule => ({
        ...rule,
        items: rule.items.filter(item => item.text.toLowerCase().includes(query))
      })).filter(rule => rule.items.length > 0);
    }
  },
  methods: {
    logout() {
      Cookies.remove(this.COOKIE_NAME)
      window.location.reload()
    },
    async setCookie() {
      //const jwt_sec = 'foo';
      const secret = new TextEncoder().encode("your_secret_key");
      if (!this.user) {return;}
      //const act = jwt.encode(this.user, jwt_sec);
      const act = await new SignJWT(this.user)
        .setProtectedHeader({ alg: 'HS256' })  // Algoritmo di firma
        .sign(secret);
      Cookies.set(this.COOKIE_NAME, act, { expires: 10, path: '/' });
    },
    async getCookie() {
      const act = Cookies.get(this.COOKIE_NAME);
      
      // Restituisce i dati dell'utente contenuti nell'access token, oppure null se il token è mancante o invalido
      if (!act) return null;
      try {
        const user = jwtDecode(act) as User;
        this.user = user;
        return user;
      } catch {
        return null;
      }
    },
    async getUser(name: string) {
      const response = await fetch("http://localhost/get_user", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({
          name: name
        })
      });
      
      const result = await response.json();
      
      if (result.success) {
        this.user = result.data;
        await this.setCookie();
      } else {
        console.error("Errore:", result.message);
        return null
      }
    },
    async getUsers() {
      const response = await fetch("http://localhost/get_users");
      const result = await response.json();

      if (result.success) {
        console.log("Utenti recuperati:", result.data);
        this.users = result.data;
      } else {
        console.error("Errore:", result.message);
      }
    },
    async addUser(name: string) {
      const response = await fetch('http://localhost/save_user', { // httpss://fantagioco.altervista.org/save_user.php
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            name: name, // Passa il nome dell'utente
        }),
      });
      const result = await response.json();

      // Gestisci la risposta
      if (result.success) {
        console.log(result.message); // Es. "Utente salvato!"
        await this.getUser(name);
      } else {
        console.error(result.message); // Es. Errore dal server
      }
    },
    async fetchUser() {
      if(this.user) return; // se è null continua
      if(await this.getCookie()) return;
      this.isPopupVisible = true;
      
    },
    closePopup() {
      this.isPopupVisible = false;
      this.isPopupConfirmVisible = false;
      this.isPopupInputVisible = false;
    },
    async sendSquad() {
      try {
        const response = await fetch('http://localhost/save_squad', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ squad: this.teamPlayers, name : this.user?.name })
        });
        
        const result = await response.json();

        if (!result.success) {
          throw new Error('Errore nel salvataggio della squadra');
        }

        // Pulisci l'input dopo l'invio
        
      } catch (error) {
        console.error('Errore:', error);
        alert('Errore nell\'invio del messaggio');
      }
    },
    async handleSubmitName(name: string) {
      await this.getUser(name)
      if(this.user) {
        //this.isPopupVisible = false;
        window.location.reload();
        return;
      }
      this.showListPopup = true;
    },
    async handleSubmit(name: string, selectedNames: string[]) {
      await this.addUser(name);
      
      this.teamPlayers = Object.values(selectedNames);
      await this.sendSquad();
      window.location.reload();
    },
    async sendLog(action_log: ActionLog) {
      try {
        const response = await fetch('http://localhost/save_log', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(action_log)
        });
        
        const result = await response.json();

        if (result.success) {
          console.log(result.message)
        } else {
          throw new Error('Errore nel salvataggio del log');
        }

        // Pulisci l'input dopo l'invio
        
      } catch (error) {
        console.error('Errore:', error);
        alert('Errore nell\'invio del messaggio');
      }
    },
    async delLog(id: number, name: string) {
      try {
        const response = await fetch('http://localhost/delete_log', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({ idToDel: id, name: name })
        });
        
        const result = await response.json();

        if (result.success) {
          
        } else {
          throw new Error('Errore nel eliminazione del log');
        }

        // Pulisci l'input dopo l'invio
        
      } catch (error) {
        console.error('Errore:', error);
        alert('Errore nell\'eliminazione del messaggio');
      }
    },
    setupSSE() {
      // Chiudi l'eventSource esistente se presente
      if (this.eventSource) {
        this.eventSource.close();
      }

      this.eventSource = new EventSource('http://localhost/sse');

      this.eventSource.onmessage = (event) => {
        console.log('Received message:', event.data);
        const mess = JSON.parse(event.data);
        if(mess.type == 'delete') {
          const log = this.actionLogs.find(log => log.id === mess.log.id);
          if (log) {
            this.actionLogs = this.actionLogs.filter(log => log.id !== mess.log.id);
            this.addPoints(log, true); // Sottrai i punti dell'azione eliminata
          }
        } else if(mess.type == 'update') {
          this.actionLogs.push(mess.log);
          this.addPoints(mess.log);
        }

        // Mantieni solo gli ultimi 100 messaggi per evitare problemi di memoria
        // if (this.actionLogs.length > 100) {
        //   this.actionLogs.shift();
        // }
      };

      this.eventSource.onerror = (error) => {
        console.error('SSE Error:', error);
        // Azzera l'array actionLogs in caso di errore
        this.actionLogs = [];
        // Prova a riconnetterti dopo 5 secondi in caso di errore
        setTimeout(() => this.setupSSE(), 5000);
      };
    },
    handleRuleClick(item: itemRules) {
      
      if(item.target == 'undefined') {
        this.isPopupInputVisible = true;
        this.selectedItem = item;
        return;
      }
      
      this.sendLog({username: this.user?.name, text: item.text, points: item.points, target: item.target, typeAction: 'aggiungi', active: true} as ActionLog);

    },
    handleInputRule(info: any) {
      this.selectedItem.points = this.selectedItem.points * info.quantity;
      this.sendLog({username: this.user?.name, text: this.selectedItem.text, points: this.selectedItem.points, target: info.name, typeAction: 'aggiungi', active: true} as ActionLog);
    },
    undoAction(action: ActionLog) {
      this.isPopupConfirmVisible = true;
      this.selectedAction.type = 'undo';
      this.selectedAction.action = action;
      // Implementa la logica per annullare l'azione
    },
    deleteAction(action: ActionLog) {
      this.isPopupConfirmVisible = true;
      this.selectedAction.type = 'delete';
      this.selectedAction.action = action;
    },
    async handleConfirm() {
      if(!this.user) return;
      
      await this.delLog(this.selectedAction.action.id, this.user.name);
      this.closePopup();
    },
    addPoints(log: ActionLog, undo: boolean = false) {
      const pointsModifier = undo ? -1 : 1;
      if(log.target == 'all') {
        this.users.forEach(user => {
          user.score += log.points * pointsModifier;
        });
      } else {
        // Add points to specific user
        this.users.forEach(user => {
          if (user.members.includes(log.target)) {
            user.score += log.points * pointsModifier;
          }
        });
      }
      if(this.user) this.user.score = this.users.find(us => us.name == this.user?.name)?.score ?? 0;
    },

  },
  watch: {
    user: {
      handler(){
        this.getUsers();
      }
    },
    users: {
      handler() {
        this.teamPlayers = this.users.find(us  => us.name == this.user?.name)?.members;
      }
    }
  },
  mounted() {
    this.fetchUser();
    this.setupSSE();
  },
  beforeDestroy() {
    if (this.eventSource) {
      this.eventSource.close();
    }
  }
});
</script>