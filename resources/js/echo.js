import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER ?? 'mt1',
    wsHost: import.meta.env.VITE_PUSHER_HOST ?? undefined,
    wsPort: import.meta.env.VITE_PUSHER_PORT ?? undefined,
    forceTLS: (import.meta.env.VITE_PUSHER_SCHEME ?? 'https') === 'https',
    encrypted: true,
    authEndpoint: '/broadcasting/auth',
    auth: {
        headers: {
            Authorization: localStorage.getItem('auth_token')
                ? 'Bearer ' + localStorage.getItem('auth_token')
                : '',
        },
    },
});
window.conversationListeners = {};

window.subscribeToConversation = (conversationId, callbacks = {}) => {
    const channelName = `conversation.${conversationId}`;
    
    if (window.conversationListeners[conversationId]) {
        return;
    }
    
    const channel = window.Echo.private(channelName);
    
    channel.listen('MessageSent', (event) => {
        console.log('Nuevo mensaje:', event);
        if (callbacks.onMessageSent) callbacks.onMessageSent(event);
    });
    
    channel.listen('MessageUpdated', (event) => {
        console.log('Mensaje actualizado:', event);
        if (callbacks.onMessageUpdated) callbacks.onMessageUpdated(event);
    });
    
    channel.listen('MessageDeleted', (event) => {
        console.log('Mensaje eliminado:', event);
        if (callbacks.onMessageDeleted) callbacks.onMessageDeleted(event);
    });
    
    channel.listen('UserJoinedConversation', (event) => {
        console.log('Usuario se unio:', event);
        if (callbacks.onUserJoined) callbacks.onUserJoined(event);
    });
    
    channel.listen('UserLeftConversation', (event) => {
        console.log('Usuario se fue:', event);
        if (callbacks.onUserLeft) callbacks.onUserLeft(event);
    });
    
    channel.listen('ConversationUpdated', (event) => {
        console.log('Conversacion actualizada:', event);
        if (callbacks.onConversationUpdated) callbacks.onConversationUpdated(event);
    });

    channel.listen('UserTyping', (event) => {
        console.log('Usuario escribiendo:', event);
        if (callbacks.onUserTyping) callbacks.onUserTyping(event);
    });
    
    window.conversationListeners[conversationId] = channel;
    console.log(`Suscrito a conversacion ${conversationId}`);
};

window.unsubscribeFromConversation = (conversationId) => {
    if (window.conversationListeners[conversationId]) {
        window.Echo.leaveChannel(`private-conversation.${conversationId}`);
        delete window.conversationListeners[conversationId];
        console.log(`Desuscrito de conversacion ${conversationId}`);
    }
};

window.unsubscribeFromAllConversations = () => {
    Object.keys(window.conversationListeners).forEach((conversationId) => {
        window.unsubscribeFromConversation(conversationId);
    });
};

export default window.Echo;
