import { createRouter, createWebHistory } from 'vue-router';
import LoginPage from '../pages/LoginPage.vue';
import ChatPage from '../pages/ChatPage.vue';

const routes = [
    {
        path: '/',
        name: 'Login',
        component: LoginPage
    },
    {
        path: '/chat',
        name: 'Chat',
        component: ChatPage,
        meta: { requiresAuth: true }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

router.beforeEach((to, from, next) => {
    const token = localStorage.getItem('auth_token');
    
    if (to.meta.requiresAuth && !token) {
        next({ name: 'Login' });
    } else if (to.name === 'Login' && token) {
        next({ name: 'Chat' });
    } else {
        next();
    }
});

export default router;
