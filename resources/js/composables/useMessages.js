export function useMessages() {
    const baseURL = 'http://localhost:8000/api';

    const fetchMessages = async (conversationId, token) => {
        const response = await window.axios.get(
            baseURL + '/conversations/' + conversationId + '/messages',
            {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json'
                }
            }
        );
        return response.data;
    };

    const createMessage = async (conversationId, content, token) => {
        const response = await window.axios.post(
            baseURL + '/conversations/' + conversationId + '/messages',
            { content: content },
            {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json'
                }
            }
        );
        return response.data;
    };

    const updateMessage = async (conversationId, messageId, content, token) => {
        const response = await window.axios.put(
            baseURL + '/messages/' + messageId,
            { content: content },
            {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json'
                }
            }
        );
        return response.data;
    };

    const deleteMessage = async (conversationId, messageId, token) => {
        await window.axios.delete(
            baseURL + '/messages/' + messageId,
            {
                headers: {
                    'Authorization': 'Bearer ' + token,
                    'Content-Type': 'application/json'
                }
            }
        );
    };

    return {
        fetchMessages,
        createMessage,
        updateMessage,
        deleteMessage
    };
}
