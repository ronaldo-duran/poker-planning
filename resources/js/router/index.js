import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '../stores/auth';

const routes = [
    {
        path: '/',
        component: () => import('../views/HomeView.vue'),
        name: 'home',
    },
    {
        path: '/login',
        component: () => import('../views/auth/LoginView.vue'),
        name: 'login',
        meta: { guest: true },
    },
    {
        path: '/register',
        component: () => import('../views/auth/RegisterView.vue'),
        name: 'register',
        meta: { guest: true },
    },
    {
        path: '/profile',
        component: () => import('../views/ProfileView.vue'),
        name: 'profile',
        meta: { requiresAuth: true },
    },
    {
        path: '/rooms',
        component: () => import('../views/rooms/RoomsView.vue'),
        name: 'rooms',
        meta: { requiresAuth: true },
    },
    {
        path: '/rooms/create',
        component: () => import('../views/rooms/CreateRoomView.vue'),
        name: 'rooms.create',
        meta: { requiresAuth: true },
    },
    {
        path: '/rooms/:id',
        component: () => import('../views/rooms/RoomView.vue'),
        name: 'rooms.show',
        meta: { requiresAuth: true },
    },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const auth = useAuthStore();
    if (to.meta.requiresAuth && !auth.isLoggedIn) {
        next({ name: 'login' });
    } else if (to.meta.guest && auth.isLoggedIn) {
        next({ name: 'rooms' });
    } else {
        next();
    }
});

export default router;
