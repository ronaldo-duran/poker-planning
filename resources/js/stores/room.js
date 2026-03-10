import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useRoomStore = defineStore('room', () => {
    const currentRoom = ref(null);
    const currentSession = ref(null);
    const myVote = ref(null);
    const revealed = ref(false);
    const countdown = ref(null);

    function setRoom(room) {
        currentRoom.value = room;
    }

    function setSession(session) {
        currentSession.value = session;
        myVote.value = null;
        revealed.value = false;
    }

    function setMyVote(vote) {
        myVote.value = vote;
    }

    function startReveal(sessionData) {
        let count = 3;
        countdown.value = count;
        const interval = setInterval(() => {
            count--;
            countdown.value = count;
            if (count <= 0) {
                clearInterval(interval);
                countdown.value = null;
                revealed.value = true;
                if (currentSession.value) {
                    currentSession.value = { ...currentSession.value, ...sessionData };
                }
            }
        }, 1000);
    }

    function updateRoomState(state) {
        if (currentRoom.value) {
            currentRoom.value = { ...currentRoom.value, state };
        }
    }

    function markUserVoted(userId) {
        if (currentRoom.value?.users) {
            currentRoom.value.users = currentRoom.value.users.map(u =>
                u.id === userId ? { ...u, _voted: true } : u
            );
        }
    }

    function addUser(user) {
        if (currentRoom.value?.users && !currentRoom.value.users.find(u => u.id === user.id)) {
            currentRoom.value.users.push(user);
        }
    }

    function removeUser(userId) {
        if (currentRoom.value?.users) {
            currentRoom.value.users = currentRoom.value.users.filter(u => u.id !== userId);
        }
    }

    async function loadRoom(id) {
        const { data } = await axios.get(`/api/rooms/${id}`);
        setRoom(data);
        return data;
    }

    return {
        currentRoom, currentSession, myVote, revealed, countdown,
        setRoom, setSession, setMyVote, startReveal, updateRoomState,
        markUserVoted, addUser, removeUser, loadRoom,
    };
});
