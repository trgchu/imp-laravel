<template>
  <div class="chat-page">
    <div class="chat-container">
      <div class="sidebar">
        <div class="sidebar-header">
          <h2>HoshiChat 🌙</h2>
          <button @click="logout" class="btn-logout">Salir</button>
        </div>

        <button @click="showNewConversationModal = true" class="btn-new-conversation">
          Nueva Conversacion
        </button>

        <div class="conversations-list">
          <div
            v-for="conv in conversations"
            :key="conv.id"
            @click="selectConversation(conv.id)"
            :class="['conversation-item', { active: selectedConversationId === conv.id }]"
          >
            <div class="conv-name">{{ conv.name }}</div>
            <div class="conv-users">{{ conv.users?.length || 0 }} usuarios</div>
          </div>
        </div>
      </div>

      <div class="chat-area">
        <div v-if="selectedConversation" class="chat-content">
          <div class="chat-header">
            <div>
              <h3>{{ selectedConversation.name }}</h3>
              <p>{{ selectedConversation.description }}</p>
            </div>
            <div class="chat-status" :class="{ connected: isWebSocketConnected }">
              {{ isWebSocketConnected ? 'Conectado' : 'Desconectado' }}
            </div>
          </div>

          <div class="messages-container" ref="messagesContainer">
            <div
              v-for="message in messages"
              :key="message.id"
              :class="['message', { 'message-own': message.user_id === userId }]"
            >
              <div class="message-author">{{ message.user?.name }}</div>
              <div class="message-content">{{ message.content }}</div>
              <div class="message-time">{{ formatTime(message.created_at) }}</div>

              <div v-if="message.user_id === userId" class="message-actions">
                <button @click="editMessage(message)" class="btn-action">Editar</button>
                <button @click="deleteMessage(message.id)" class="btn-action btn-delete">Eliminar</button>
              </div>
            </div>

            <div v-if="messages.length === 0" class="no-messages">
              No hay mensajes aun
            </div>
          </div>

          <div class="message-input-area">
            <input
              v-model="newMessage"
              @keyup.enter="sendMessage"
              type="text"
              placeholder="Escribe un mensaje sobre la luna... 🌙"
              class="message-input"
            />
            <button @click="sendMessage" class="btn-send">Enviar</button>
          </div>
        </div>

        <div v-else class="no-conversation">
          <p>Selecciona una conversacion</p>
        </div>
      </div>
    </div>

    <div v-if="showNewConversationModal" class="modal-overlay" @click="showNewConversationModal = false">
      <div class="modal" @click.stop>
        <h3>Nueva Conversacion</h3>
        <input
          v-model="newConversationData.name"
          type="text"
          placeholder="Nombre"
          class="modal-input"
        />
        <textarea
          v-model="newConversationData.description"
          placeholder="Descripcion"
          class="modal-input"
          rows="3"
        ></textarea>
        <div class="modal-actions">
          <button @click="createConversation" class="btn-primary">Crear</button>
          <button @click="showNewConversationModal = false" class="btn-secondary">Cancelar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { useConversations } from '../composables/useConversations';
import { useMessages } from '../composables/useMessages';
import { useWebSocket } from '../composables/useWebSocket';

export default {
  name: 'ChatPage',
  data() {
    return {
      conversations: [],
      selectedConversationId: null,
      selectedConversation: null,
      messages: [],
      newMessage: '',
      newConversationData: { name: '', description: '' },
      showNewConversationModal: false,
      isWebSocketConnected: false,
      userId: null,
      token: null
    };
  },
  async mounted() {
    this.userId = parseInt(localStorage.getItem('user_id'));
    this.token = localStorage.getItem('auth_token');

    if (!this.token) {
      this.$router.push({ name: 'Login' });
      return;
    }

    this.isWebSocketConnected = useWebSocket().isConnected();
    await this.loadConversations();

    if (this.conversations.length > 0) {
      await this.selectConversation(this.conversations[0].id);
    }
  },
  methods: {
    // ── El composable devuelve response.data (axios)
    // ── El backend devuelve { data: [...] } para index
    // ── Entonces fetchConversations() → { data: [...] } → tomamos .data
    async loadConversations() {
      try {
        const response = await useConversations().fetchConversations(this.token);
        // response ya es response.data de axios → { data: [...] }
        this.conversations = Array.isArray(response)
          ? response
          : (response.data ?? []);
      } catch (error) {
        console.error('Error cargando conversaciones:', error);
      }
    },

    async selectConversation(conversationId) {
      this.selectedConversationId = conversationId;
      try {
        const response = await useConversations().getConversation(conversationId, this.token);
        // backend devuelve { data: {...} } para show
        this.selectedConversation = response.data ?? response;
        await this.loadMessages();
        this.setupWebSocketListeners();
      } catch (error) {
        console.error('Error seleccionando conversación:', error);
      }
    },

async loadMessages() {
  try {
    const response = await useMessages().fetchMessages(this.selectedConversationId, this.token);
    console.log('MESSAGES RAW:', JSON.stringify(response).substring(0, 500));
    const raw = response.data ?? response;
    this.messages = Array.isArray(raw) ? raw : (raw.data ?? []);
    this.$nextTick(() => this.scrollToBottom());
  } catch (error) {
    console.error('Error cargando mensajes:', error);
  }
},
    scrollToBottom() {
      const container = this.$refs.messagesContainer;
      if (container) container.scrollTop = container.scrollHeight;
    },

    setupWebSocketListeners() {
      useWebSocket().listenToConversation(this.selectedConversationId, {
        onMessageSent: (event) => {
          // Evitar duplicados
          if (!this.messages.find(m => m.id === event.id)) {
            this.messages.push(event);
            this.$nextTick(() => this.scrollToBottom());
          }
        },
        onMessageUpdated: (event) => {
          const index = this.messages.findIndex(m => m.id === event.id);
          if (index !== -1) this.messages[index] = event;
        },
        onMessageDeleted: (event) => {
          this.messages = this.messages.filter(m => m.id !== event.id);
        },
        onUserJoined: () => { this.loadConversations(); },
        onUserLeft: () => { this.loadConversations(); }
      });
    },

    async sendMessage() {
      if (!this.newMessage.trim()) return;
      const content = this.newMessage;
      this.newMessage = '';
      try {
        const response = await useMessages().createMessage(this.selectedConversationId, content, this.token);
        // Agregar el propio mensaje inmediatamente (sin esperar WebSocket)
        const msg = response.data ?? response;
        if (msg && msg.id && !this.messages.find(m => m.id === msg.id)) {
          this.messages.push(msg);
          this.$nextTick(() => this.scrollToBottom());
        }
      } catch (error) {
        this.newMessage = content;
        console.error('Error enviando mensaje:', error);
      }
    },

    async deleteMessage(messageId) {
      if (!confirm('Eliminar?')) return;
      try {
        await useMessages().deleteMessage(this.selectedConversationId, messageId, this.token);
        this.messages = this.messages.filter(m => m.id !== messageId);
      } catch (error) {
        console.error('Error eliminando mensaje:', error);
      }
    },

    editMessage(message) {
      const newContent = prompt('Editar:', message.content);
      if (newContent) {
        useMessages().updateMessage(this.selectedConversationId, message.id, newContent, this.token);
      }
    },

    async createConversation() {
      if (!this.newConversationData.name.trim()) return;
      try {
        const payload = {
          name: this.newConversationData.name,
          description: this.newConversationData.description,
          user_ids: [this.userId]
        };
        const response = await useConversations().createConversation(payload, this.token);
        this.newConversationData = { name: '', description: '' };
        this.showNewConversationModal = false;
        await this.loadConversations();
        // Seleccionar la nueva conversación automáticamente
        const newConv = response.data ?? response;
        if (newConv && newConv.id) {
          await this.selectConversation(newConv.id);
        }
      } catch (error) {
        console.error('Error creando conversación:', error.response?.data || error);
      }
    },

    logout() {
      localStorage.removeItem('auth_token');
      localStorage.removeItem('user_id');
      this.$router.push({ name: 'Login' });
    },

    formatTime(date) {
      return new Date(date).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' });
    }
  },
  beforeUnmount() {
    useWebSocket().stopListeningToAll();
  }
};
</script>

<style scoped>
.chat-page { width: 100%; height: 100vh; display: flex; background: #f5f5f5; }
.chat-container { display: flex; width: 100%; height: 100%; }
.sidebar { width: 300px; background: white; border-right: 1px solid #e0e0e0; display: flex; flex-direction: column; overflow-y: auto; }
.sidebar-header { padding: 20px; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center; }
.sidebar-header h2 { margin: 0; font-size: 24px; color: #667eea; }
.btn-logout { padding: 6px 12px; background: #ff6b6b; color: white; border: none; border-radius: 5px; cursor: pointer; font-size: 12px; }
.btn-new-conversation { margin: 15px; padding: 12px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; }
.btn-new-conversation:hover { background: #5568d3; }
.conversations-list { flex: 1; overflow-y: auto; }
.conversation-item { padding: 15px; border-bottom: 1px solid #f0f0f0; cursor: pointer; transition: background 0.2s; }
.conversation-item:hover { background: #f9f9f9; }
.conversation-item.active { background: #f0f0ff; border-left: 3px solid #667eea; padding-left: 12px; }
.conv-name { font-weight: 600; color: #333; margin-bottom: 4px; }
.conv-users { font-size: 12px; color: #999; }
.chat-area { flex: 1; display: flex; flex-direction: column; background: white; }
.chat-content { display: flex; flex-direction: column; height: 100%; }
.chat-header { padding: 20px; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center; }
.chat-header h3 { margin: 0 0 5px 0; color: #333; }
.chat-header p { margin: 0; color: #999; font-size: 14px; }
.chat-status { padding: 6px 12px; background: #fee; color: #c33; border-radius: 20px; font-size: 12px; font-weight: 600; }
.chat-status.connected { background: #efe; color: #3c3; }
.messages-container { flex: 1; overflow-y: auto; padding: 20px; display: flex; flex-direction: column; gap: 10px; }
.message { padding: 12px 16px; background: #f5f5f5; border-radius: 8px; max-width: 70%; word-wrap: break-word; }
.message-own { align-self: flex-end; background: #667eea; color: white; }
.message-author { font-size: 12px; font-weight: 600; margin-bottom: 4px; opacity: 0.8; }
.message-content { margin: 4px 0; font-size: 15px; }
.message-time { font-size: 11px; opacity: 0.7; margin-top: 4px; }
.message-actions { display: flex; gap: 5px; margin-top: 8px; }
.btn-action { padding: 4px 8px; font-size: 11px; background: rgba(255,255,255,0.2); color: white; border: none; border-radius: 3px; cursor: pointer; }
.btn-action:hover { background: rgba(255,255,255,0.3); }
.btn-action.btn-delete:hover { background: #ff6b6b; }
.no-messages { text-align: center; color: #999; padding: 40px; }
.message-input-area { padding: 20px; border-top: 1px solid #e0e0e0; display: flex; gap: 10px; }
.message-input { flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; }
.message-input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
.btn-send { padding: 12px 24px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; }
.btn-send:hover { background: #5568d3; }
.no-conversation { display: flex; align-items: center; justify-content: center; height: 100%; color: #999; font-size: 18px; }
.modal-overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.5); display: flex; align-items: center; justify-content: center; z-index: 1000; }
.modal { background: white; padding: 30px; border-radius: 10px; width: 100%; max-width: 400px; box-shadow: 0 10px 40px rgba(0,0,0,0.2); }
.modal h3 { margin-top: 0; color: #333; }
.modal-input { width: 100%; padding: 12px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 5px; font-size: 14px; box-sizing: border-box; }
.modal-input:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102,126,234,0.1); }
.modal-actions { display: flex; gap: 10px; }
.btn-primary, .btn-secondary { flex: 1; padding: 12px; border: none; border-radius: 5px; cursor: pointer; font-weight: 600; }
.btn-primary { background: #667eea; color: white; }
.btn-primary:hover { background: #5568d3; }
.btn-secondary { background: #f0f0f0; color: #333; }
.btn-secondary:hover { background: #e0e0e0; }
@media (max-width: 768px) { .sidebar { width: 250px; } .message { max-width: 85%; } }
</style>
