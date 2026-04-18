export function useConversations() {
    const baseURL = 'http://localhost:8000/api';

    const fetchConversations = async (token) => {
        const response = await window.axios.get(baseURL + '/conversations', {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            }
        });
        return response.data;
    };

    const getConversation = async (id, token) => {
        const response = await window.axios.get(baseURL + '/conversations/' + id, {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            }
        });
        return response.data;
    };

    const createConversation = async (data, token) => {
        const response = await window.axios.post(baseURL + '/conversations', data, {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            }
        });
        return response.data;
    };

    const updateConversation = async (id, data, token) => {
        const response = await window.axios.put(baseURL + '/conversations/' + id, data, {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            }
        });
        return response.data;
    };

    const deleteConversation = async (id, token) => {
        await window.axios.delete(baseURL + '/conversations/' + id, {
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            }
        });
    };

    return {
        fetchConversations,
        getConversation,
        createConversation,
        updateConversation,
        deleteConversation
    };
}
