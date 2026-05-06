<template>
  <div class="app-shell">
    <div v-if="!role" class="login-shell">
      <div class="login-surface">
        <div class="login-brand">
          <img src="./assets/logo_repo.png" alt="MJSIR Logo" style="height: 48px; width: 48px; object-fit: cover; border-radius: 50%;">
          <div>
            <p class="login-brand-title">MJSIR Tracker</p>
            <p class="login-brand-subtitle">Editorial Workflow Platform</p>
          </div>
        </div>

        <div class="login-copy">
          <h1>Operate publication workflows with clarity.</h1>
          <p>Track submissions, coordinate assignments, and finalize decisions from one workspace designed for editorial efficiency.</p>
        </div>

        <form @submit.prevent="handleLogin" class="login-form">
          <p v-if="loginError" class="form-alert">{{ loginError }}</p>

          <label>
            <span>Email address</span>
            <input
              type="email"
              v-model="loginForm.email"
              required
              placeholder="name@organization.com"
            >
          </label>

          <label>
            <span>Password</span>
            <input
              type="password"
              v-model="loginForm.password"
              required
              placeholder="Enter password"
            >
          </label>

          <button type="submit" class="btn-primary login-button" :disabled="isLoading">
            {{ isLoading ? 'Signing in...' : 'Sign In' }}
          </button>
        </form>
      </div>
    </div>

    <div v-else class="layout">
      <aside class="sidebar">
        <div>
          <div class="sidebar-brand">
            <img src="./assets/logo_repo.png" alt="MJSIR Logo" style="height: 40px; width: 40px; object-fit: cover; border-radius: 50%;">
            <div>
              <p class="sidebar-brand-title">MJSIR Tracker</p>
              <p class="sidebar-brand-subtitle">Research Office Workspace</p>
            </div>
          </div>

          <nav class="sidebar-nav">
            <router-link
              v-for="item in navigationItems"
              :key="item.to"
              :to="item.to"
              class="sidebar-link"
              active-class="active"
            >
              <span>{{ item.label }}</span>
            </router-link>
          </nav>
        </div>

        <div class="sidebar-footer">
          <p>{{ userName }}</p>
          <span>{{ formatRole(role) }}</span>
        </div>
      </aside>

      <div class="workspace">
        <header class="header">
          <div>
            <p class="header-kicker">Active workspace</p>
            <h1 class="header-title">{{ pageTitle }}</h1>
          </div>
          <div class="flex items-center gap-6">
            <!-- NOTIFICATIONS BELL -->
            <div class="relative" ref="notifDropdown">
              <button @click="toggleNotifications" class="relative p-2 text-[var(--text2)] hover:text-[var(--accent)] transition-colors bg-transparent border-none cursor-pointer flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                <span v-if="unreadCount > 0" class="absolute top-0 right-0 bg-red-500 text-white text-[10px] font-bold h-4 w-4 rounded-full flex items-center justify-center" style="transform: translate(25%, -25%);">{{ unreadCount }}</span>
              </button>
              
              <div v-if="showNotifications" class="absolute right-0 z-50 flex flex-col bg-[var(--surface)] border border-[var(--border)] shadow-xl" style="width: 350px; top: 100%; margin-top: 12px; border-radius: 4px; overflow: hidden; max-height: 400px;">
                <div class="p-3 border-b border-[var(--border)] bg-[var(--surface2)] flex justify-between items-center">
                  <h4 class="font-bold text-[12px] text-[var(--accent)] m-0 uppercase tracking-wide">Notifications</h4>
                </div>
                <div class="flex-1 overflow-y-auto" style="max-height: 350px;">
                   <div v-for="notif in notifications" :key="notif.id" :class="['p-3 border-b border-[var(--border)] cursor-pointer hover:bg-[var(--surface2)] transition-colors text-sm', { 'bg-red-50/50 border-l-4 border-l-red-500': notif.is_read == 0 }]" @click="markAsRead(notif)" style="border-radius: 0;">
                      <p :class="['m-0 text-[var(--text)] text-[13px]', {'font-bold': notif.is_read == 0}]" style="word-wrap: break-word; white-space: normal;">{{ notif.message }}</p>
                      <small class="text-[10px] text-[var(--text3)] mt-1 block">{{ new Date(notif.created_at).toLocaleString() }}</small>
                   </div>
                   <div v-if="notifications.length === 0" class="p-4 text-center text-sm text-[var(--text3)] italic">No notifications</div>
                </div>
              </div>
            </div>
            
            <button @click="logout" class="btn-outline">Sign Out</button>
          </div>
        </header>

        <main class="main-content">
          <router-view></router-view>
        </main>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();

const role = ref(null);
const userName = ref('');
const isLoading = ref(false);
const loginError = ref('');

const loginForm = ref({
  email: '',
  password: ''
});

const showNotifications = ref(false);
const notifications = ref([]);
const notifDropdown = ref(null);
const notifInterval = ref(null);

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost/mjsir_tracker/backend/public/index.php/api';

const unreadCount = computed(() => {
  return notifications.value.filter(n => n.is_read == 0).length;
});

const toggleNotifications = () => {
  showNotifications.value = !showNotifications.value;
};

const fetchNotifications = async () => {
  if (!role.value) return;
  try {
    const res = await axios.get(`${API_BASE_URL}/notifications`, {
      params: {
        target_user: userName.value,
        role: role.value
      }
    });
    notifications.value = res.data;
  } catch (e) {
    console.error('Failed to fetch notifications', e);
  }
};

const markAsRead = async (notif) => {
  if (notif.is_read == 1) return;
  try {
    await axios.post(`${API_BASE_URL}/notifications/${notif.id}/read`);
    notif.is_read = 1;
  } catch (e) {
    console.error(e);
  }
};

const handleClickOutside = (event) => {
  if (notifDropdown.value && !notifDropdown.value.contains(event.target)) {
    showNotifications.value = false;
  }
};

const navigationItems = computed(() => {
  const items = [
    { to: '/', label: 'Dashboard' },
    { to: '/publications', label: 'Prescreening Process' },
    { to: '/article-status', label: 'Article Status' }
  ];

  if (role.value === 'repo_staff' || role.value === 'editor') {
    items.push({ to: '/my-tasks', label: 'My Assigned Tasks' });
  }

  if (role.value === 'eic') {
    items.push({ to: '/eic-review', label: 'EIC Review Queue' });
    items.push({ to: '/assignments', label: 'EIC Assignments' });
    items.push({ to: '/users', label: 'User Management' });
  }

  return items;
});

const pageTitle = computed(() => {
  if (!route?.name) return 'Workspace';
  return String(route.name).replace(/([A-Z])/g, ' $1').trim();
});

onMounted(() => {
  role.value = localStorage.getItem('user_role');
  userName.value = localStorage.getItem('user_name') || '';
  if (role.value) {
    fetchNotifications();
    notifInterval.value = setInterval(fetchNotifications, 10000);
  }
  document.addEventListener('mousedown', handleClickOutside);
});

onUnmounted(() => {
  if (notifInterval.value) clearInterval(notifInterval.value);
  document.removeEventListener('mousedown', handleClickOutside);
});

const handleLogin = async () => {
  isLoading.value = true;
  loginError.value = '';
  try {
    const res = await axios.post(`${API_BASE_URL}/auth/login`, loginForm.value);
    const user = res.data.user;

    role.value = user.role;
    userName.value = user.name;

    localStorage.setItem('user_role', user.role);
    localStorage.setItem('user_name', user.name);
    localStorage.setItem('user_id', user.id);

    window.location.href = '/';
  } catch (err) {
    if (err.response?.data?.messages) {
      loginError.value = err.response.data.messages.error || 'Invalid credentials.';
    } else {
      loginError.value = 'Unable to connect to the server.';
    }
  } finally {
    isLoading.value = false;
  }
};

const logout = () => {
  role.value = null;
  userName.value = '';
  localStorage.removeItem('user_role');
  localStorage.removeItem('user_name');
  localStorage.removeItem('user_id');
  window.location.href = '/';
};

const formatRole = (r) => {
  if (r === 'eic') return 'Editor-in-Chief';
  if (r === 'repo_staff') return 'REPO Staff';
  if (r === 'editor') return 'Associate Editor';
  return r;
};
</script>

