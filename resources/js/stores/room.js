import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useRoomStore = defineStore('room', () => {
    const currentRoom = ref(null);
    const currentSession = ref(null);
    const myVote = ref(null);
    const revealed = ref(false);
    const countdown = ref(null);
    const reactionTimeouts = new Map();

    function setRoom(room) {
        if (room?.users) {
            room.users = room.users.map(u => ({
                ...u,
                _voted: u._voted ?? false,
                _reaction: u._reaction ?? null,
                _online: u.pivot?.is_online ?? u._online ?? false,
            }));
        }

        currentRoom.value = room;
    }

    function setSession(session) {
        currentSession.value = session;
        myVote.value = null;
        revealed.value = false;
        countdown.value = null;

        // Start a clean round: everyone must vote again.
        if (currentRoom.value?.users) {
            currentRoom.value.users = currentRoom.value.users.map(u => ({ ...u, _voted: false }));
        }
    }

    function clearSession() {
        currentSession.value = null;
        myVote.value = null;
        revealed.value = false;
        countdown.value = null;
    }

    function setMyVote(vote) {
        myVote.value = vote;
    }

    function startReveal(sessionData) {
        // Prevent duplicate animation runs caused by overlapping HTTP + WebSocket updates.
        if (countdown.value !== null || revealed.value || currentSession.value?.status === 'revealed') {
            return;
        }

        if (currentSession.value) {
            currentSession.value = { ...currentSession.value, status: 'revealing' };
        }

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
                    currentSession.value = { ...currentSession.value, ...sessionData, status: 'revealed' };
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
            currentRoom.value.users.push({ ...user, _voted: false, _reaction: null });
        }
    }

    function setUserReaction(userId, emoji) {
        if (!currentRoom.value?.users) {
            return;
        }

        currentRoom.value.users = currentRoom.value.users.map(u =>
            u.id === userId ? { ...u, _reaction: emoji } : u
        );

        const previousTimeout = reactionTimeouts.get(userId);
        if (previousTimeout) {
            clearTimeout(previousTimeout);
        }

        const timeoutId = setTimeout(() => {
            if (currentRoom.value?.users) {
                currentRoom.value.users = currentRoom.value.users.map(u =>
                    u.id === userId ? { ...u, _reaction: null } : u
                );
            }

            reactionTimeouts.delete(userId);
        }, 1200);

        reactionTimeouts.set(userId, timeoutId);
    }

    function removeUser(userId) {
        if (currentRoom.value?.users) {
            currentRoom.value.users = currentRoom.value.users.filter(u => u.id !== userId);
        }
    }

    async function loadRoom(id) {
        const { data } = await axios.get(`/api/rooms/${id}`);
        setRoom(data);

        const sessions = data.vote_sessions ?? data.voteSessions ?? [];
        const activeSession = sessions.find(s => s.status === 'open') ?? sessions[0] ?? null;

        if (activeSession) {
            setSession(activeSession);
        } else {
            clearSession();
        }

        return data;
    }

    return {
        currentRoom, currentSession, myVote, revealed, countdown,
        setRoom, setSession, clearSession, setMyVote, startReveal, updateRoomState,
        markUserVoted, addUser, removeUser, setUserReaction, loadRoom,
    };
});
