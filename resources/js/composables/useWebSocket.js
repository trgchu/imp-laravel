export function useWebSocket() {
    
    const listenToConversation = (conversationId, callbacks) => {
        window.subscribeToConversation(conversationId, callbacks);
    };

    const stopListeningToConversation = (conversationId) => {
        window.unsubscribeFromConversation(conversationId);
    };

    const stopListeningToAll = () => {
        window.unsubscribeFromAllConversations();
    };

    const isConnected = () => {
        return window.Echo?.connector?.pusher?.connection?.state === 'connected';
    };

    return {
        listenToConversation,
        stopListeningToConversation,
        stopListeningToAll,
        isConnected
    };
}
