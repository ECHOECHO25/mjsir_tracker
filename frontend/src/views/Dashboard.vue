<template>
  <div class="w-full pb-12">
    <div class="grid grid-cols-1 xl:grid-cols-12 gap-7 mb-9">
      <section class="xl:col-span-8 relative overflow-hidden rounded-2xl border border-[var(--border)] bg-gradient-to-br from-[var(--surface)] to-[var(--surface2)] p-10">
        <div class="absolute -top-20 -right-24 h-64 w-64 rounded-full bg-[var(--accent)]/10 blur-3xl"></div>
        <div class="relative z-10">
          <div class="flex flex-wrap items-center gap-3 mb-3">
            <span class="px-3 py-1 rounded-full bg-[var(--accent)]/10 border border-[var(--accent)]/20 text-[11px] uppercase tracking-widest font-bold text-[var(--accent)]">
              {{ formatRole(currentRole) }} Portal
            </span>
            <span class="text-sm text-[var(--text3)] font-semibold">
              {{ new Date().toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
            </span>
          </div>

          <h1 class="text-4xl md:text-5xl font-medium text-[var(--text)] tracking-tight leading-tight">
            Welcome back, <span class="font-bold">{{ userName }}</span>.
          </h1>
          <p class="mt-3 text-lg text-[var(--text2)] max-w-2xl">
            Review pipeline health, monitor decisions, and jump into your core editorial tools from one operational board.
          </p>
        </div>
      </section>

      <section class="xl:col-span-4 rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-7">
        <p class="text-xs uppercase tracking-widest font-bold text-[var(--text3)] mb-4">At a Glance</p>

        <div class="space-y-4">
          <div class="flex items-center justify-between border border-[var(--border)] rounded-xl px-4 py-3 bg-[var(--surface2)]">
            <span class="text-sm font-semibold text-[var(--text2)]">Completion Rate</span>
            <span class="text-2xl font-bold text-[var(--accent)]">{{ completionRate }}%</span>
          </div>

          <div>
            <div class="h-2.5 rounded-full bg-[var(--surface3)] overflow-hidden">
              <div class="h-full rounded-full bg-gradient-to-r from-[var(--accent)] to-emerald-400" :style="{ width: completionRate + '%' }"></div>
            </div>
            <p class="text-xs text-[var(--text3)] mt-2">Based on accepted manuscripts over total records.</p>
          </div>

          <div class="grid grid-cols-2 gap-3 pt-1">
            <div class="rounded-xl border border-[var(--border)] p-3 bg-white/70">
              <p class="text-[10px] uppercase tracking-widest font-bold text-[var(--text3)]">Accepted</p>
              <p class="text-2xl font-bold text-[var(--accent)] mt-1">{{ acceptedCount }}</p>
            </div>
            <div class="rounded-xl border border-[var(--border)] p-3 bg-white/70">
              <p class="text-[10px] uppercase tracking-widest font-bold text-[var(--text3)]">Pending</p>
              <p class="text-2xl font-bold text-amber-500 mt-1">{{ pendingCount }}</p>
            </div>
          </div>
        </div>
      </section>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-10">
      <div class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-7">
        <p class="text-[11px] font-bold uppercase tracking-widest text-[var(--text3)]">Total Manuscripts</p>
        <p class="text-4xl font-semibold text-[var(--text)] mt-2">{{ manuscripts.length }}</p>
      </div>

      <router-link to="/article-status?view=accepted" class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-7 block hover:border-[var(--accent)] transition-colors">
        <p class="text-[11px] font-bold uppercase tracking-widest text-[var(--text3)]">Accepted Articles</p>
        <p class="text-4xl font-semibold text-[var(--accent)] mt-2">{{ acceptedCount }}</p>
      </router-link>

      <router-link to="/article-status?view=rejected" class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-7 block hover:border-red-400 transition-colors">
        <p class="text-[11px] font-bold uppercase tracking-widest text-[var(--text3)]">Rejected Articles</p>
        <p class="text-4xl font-semibold text-red-500 mt-2">{{ rejectedCount }}</p>
      </router-link>

      <router-link to="/article-status?view=pending" class="rounded-xl border border-[var(--border)] bg-[var(--surface)] p-7 block hover:border-amber-400 transition-colors">
        <p class="text-[11px] font-bold uppercase tracking-widest text-[var(--text3)]">Pending Review</p>
        <p class="text-4xl font-semibold text-amber-500 mt-2">{{ pendingCount }}</p>
      </router-link>

      <div v-if="currentRole === 'repo_staff' || currentRole === 'editor'" class="rounded-xl border border-[var(--accent)]/30 bg-[var(--accent)]/5 p-7">
        <p class="text-[11px] font-bold uppercase tracking-widest text-[var(--accent)]">My Active Tasks</p>
        <p class="text-4xl font-semibold text-[var(--accent)] mt-2">{{ myTasksCount }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-7">
      <section class="xl:col-span-8 rounded-2xl border border-[var(--border)] bg-[var(--surface)] overflow-hidden">
        <div class="px-6 py-4 border-b border-[var(--border)] flex items-center justify-between">
          <h2 class="text-base font-bold uppercase tracking-widest text-[var(--text2)]">Recent Pipeline Activity</h2>
          <router-link to="/publications" class="text-xs font-bold uppercase tracking-widest text-[var(--accent)] hover:underline">View All &rarr;</router-link>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-[var(--surface2)] border-b border-[var(--border)]">
                <th class="p-4 text-xs font-bold text-[var(--text3)] uppercase tracking-widest w-28">ID Code</th>
                <th class="p-4 text-xs font-bold text-[var(--text3)] uppercase tracking-widest">Manuscript Title</th>
                <th class="p-4 text-xs font-bold text-[var(--text3)] uppercase tracking-widest w-44 text-right">Current Status</th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="recentManuscripts.length === 0">
                <td colspan="3" class="p-10 text-center text-[var(--text3)] text-sm italic">No recent activity.</td>
              </tr>
              <tr v-for="ms in recentManuscripts" :key="ms.id" class="border-b border-[var(--border)]/60 hover:bg-[var(--surface2)]/70 transition-colors">
                <td class="p-4 text-sm font-mono font-semibold text-[var(--text2)]">{{ ms.code }}</td>
                <td class="p-4 text-base font-medium text-[var(--text)] truncate max-w-[420px]">{{ ms.title || 'Untitled Manuscript' }}</td>
                <td class="p-4 text-right">
                  <span v-if="ms.final_decision === 'Accept for publication'" class="px-2.5 py-1 rounded-md border border-[var(--accent)]/20 bg-[var(--accent)]/12 text-[var(--accent)] text-[11px] font-bold uppercase tracking-widest inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--accent)]"></span> Accepted
                  </span>
                  <span v-else class="px-2.5 py-1 rounded-md border border-amber-400/30 bg-amber-100/60 text-amber-700 text-[11px] font-bold uppercase tracking-widest inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

      <section class="xl:col-span-4 space-y-6">
        <div class="rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-6">
          <h2 class="text-base font-bold uppercase tracking-widest text-[var(--text2)] mb-4">Workspace Tools</h2>

          <div class="space-y-3">
            <router-link to="/publications" class="group flex items-center gap-3 rounded-xl border border-[var(--border)] bg-white/70 px-4 py-3 hover:border-[var(--accent)]/40 transition-colors">
              <div class="w-9 h-9 rounded-lg bg-[var(--surface2)] text-[var(--text2)] group-hover:text-[var(--accent)] flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
              </div>
              <div>
                <p class="text-base font-bold text-[var(--text)] leading-tight">Prescreening Process</p>
                <p class="text-xs uppercase tracking-wide text-[var(--text3)] mt-0.5">Manage prescreening pipeline</p>
              </div>
            </router-link>

            <router-link v-if="currentRole === 'repo_staff' || currentRole === 'editor'" to="/my-tasks" class="group flex items-center gap-3 rounded-xl border border-[var(--border)] bg-white/70 px-4 py-3 hover:border-[var(--accent)]/40 transition-colors">
              <div class="w-9 h-9 rounded-lg bg-[var(--accent)]/10 text-[var(--accent)] flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
              </div>
              <div>
                <p class="text-base font-bold text-[var(--text)] leading-tight">Task Queue</p>
                <p class="text-xs uppercase tracking-wide text-[var(--accent)] mt-0.5">Process assigned work</p>
              </div>
            </router-link>

            <router-link to="/article-status" class="group flex items-center gap-3 rounded-xl border border-[var(--border)] bg-white/70 px-4 py-3 hover:border-[var(--accent)]/40 transition-colors">
              <div class="w-9 h-9 rounded-lg bg-[var(--surface2)] text-[var(--text2)] group-hover:text-[var(--accent)] flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
              </div>
              <div>
                <p class="text-base font-bold text-[var(--text)] leading-tight">Article Status</p>
                <p class="text-xs uppercase tracking-wide text-[var(--text3)] mt-0.5">Track final decisions</p>
              </div>
            </router-link>
          </div>
        </div>

        <div class="rounded-2xl border border-[var(--border)] bg-[var(--surface)] p-6">
          <h3 class="text-sm font-bold uppercase tracking-widest text-[var(--text3)] mb-3">Workflow Snapshot</h3>
          <div class="space-y-2.5 text-sm text-[var(--text2)]">
            <div class="flex items-center justify-between">
              <span>Records in system</span>
              <strong class="text-[var(--text)]">{{ manuscripts.length }}</strong>
            </div>
            <div class="flex items-center justify-between">
              <span>Accepted decisions</span>
              <strong class="text-[var(--accent)]">{{ acceptedCount }}</strong>
            </div>
            <div class="flex items-center justify-between">
              <span>Open/pending</span>
              <strong class="text-amber-600">{{ pendingCount }}</strong>
            </div>
            <div class="flex items-center justify-between">
              <span>Rejected decisions</span>
              <strong class="text-red-500">{{ rejectedCount }}</strong>
            </div>
            <div v-if="currentRole === 'repo_staff' || currentRole === 'editor'" class="flex items-center justify-between border-t border-[var(--border)] pt-2 mt-2">
              <span>My active queue</span>
              <strong class="text-[var(--accent)]">{{ myTasksCount }}</strong>
            </div>
          </div>
        </div>
      </section>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const currentRole = ref(localStorage.getItem('user_role') || '');
const userName = ref(localStorage.getItem('user_name') || '');
const manuscripts = ref([]);

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost/mjsir_tracker/backend/public/index.php/api';
const api = axios.create({ baseURL: API_BASE_URL });

onMounted(async () => {
  try {
    const response = await api.get('/manuscripts');
    manuscripts.value = response.data;
  } catch (error) {
    console.error('Error fetching data:', error);
  }
});

const acceptedCount = computed(() => manuscripts.value.filter(m => m.final_decision === 'Accept for publication').length);
const rejectedCount = computed(() => manuscripts.value.filter(m => m.final_decision === 'Reject').length);
const pendingCount = computed(() => manuscripts.value.length - acceptedCount.value - rejectedCount.value);
const completionRate = computed(() => {
  if (!manuscripts.value.length) return 0;
  return Math.round((acceptedCount.value / manuscripts.value.length) * 100);
});

const myTasksCount = computed(() => {
  if (currentRole.value === 'repo_staff') {
    return manuscripts.value.filter(m => m.repo === userName.value).length;
  }
  if (currentRole.value === 'editor') {
    return manuscripts.value.filter(m => m.editor === userName.value).length;
  }
  return 0;
});

const recentManuscripts = computed(() => {
  return [...manuscripts.value].sort((a, b) => b.id - a.id).slice(0, 6);
});

const formatRole = (r) => {
  if (r === 'eic') return 'Editor-in-Chief';
  if (r === 'repo_staff') return 'REPO Staff';
  if (r === 'editor') return 'Associate Editor';
  return r;
};
</script>
