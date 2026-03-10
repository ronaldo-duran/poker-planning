import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import axios from 'axios';

export const useAuthStore = defineStore('auth', () => {
    const user = ref(JSON.parse(localStorage.getItem('user') || 'null'));
    const token = ref(localStorage.getItem('token') || null);

    const isLoggedIn = computed(() => !!token.value);

    function setAuth(userData, tokenValue) {
        user.value = userData;
        token.value = tokenValue;
        localStorage.setItem('user', JSON.stringify(userData));
        localStorage.setItem('token', tokenValue);
        axios.defaults.headers.common['Authorization'] = `Bearer ${tokenValue}`;
    }

    function clearAuth() {
        user.value = null;
        token.value = null;
        localStorage.removeItem('user');
        localStorage.removeItem('token');
        delete axios.defaults.headers.common['Authorization'];
    }

    async function fetchUser() {
        if (!token.value) return;
        axios.defaults.headers.common['Authorization'] = `Bearer ${token.value}`;
        try {
            const { data } = await axios.get('/api/me');
            user.value = data;
            localStorage.setItem('user', JSON.stringify(data));
        } catch {
            clearAuth();
        }
    }

    return { user, token, isLoggedIn, setAuth, clearAuth, fetchUser };
});
