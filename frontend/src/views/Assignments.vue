<template>
  <div>
    <div class="page-header-stack mb-6">
      <h1 class="page-title">EIC Assignments Board</h1>
      <div class="page-subtitle">Assign publications to specific REPO staff and Associate Editors for processing.</div>
    </div>

    <!-- FILTER BAR -->
    <div class="filters-bar">
      <div class="filter-group">
        <label>Filter by Assignment</label>
        <select class="filter-input" v-model="assignmentFilter">
          <option value="all">All Submissions</option>
          <option value="unassigned">Unassigned Only</option>
          <option value="assigned">Assigned Only</option>
        </select>
      </div>
      <div class="filter-group flex-[2]">
        <label>Search Manuscripts</label>
        <input type="text" class="filter-input" v-model="searchQuery" placeholder="Title, authors, ID...">
      </div>
    </div>

    <!-- DATA TABLE -->
    <div class="table-container">
      <table class="tbl">
        <thead>
          <tr>
            <th>ID / Code</th>
            <th style="min-width: 250px;">Title & Authors</th>
            <th>Type</th>
            <th>Current REPO Staff</th>
            <th>Current Editor</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filteredManuscripts.length === 0">
            <td colspan="6" class="table-empty">No manuscripts found for assignments.</td>
          </tr>
          <tr v-for="ms in filteredManuscripts" :key="ms.id">
            <td>
              <div class="td-title">{{ ms.code }}</div>
              <div class="td-sub">{{ ms.date || '-' }}</div>
            </td>
            <td>
              <div class="td-title mb-1">{{ ms.title || '-' }}</div>
              <div class="td-sub">{{ ms.authors || '-' }}</div>
            </td>
            <td>
              <span class="status-badge default">{{ ms.type || ms.contributor_type || '-' }}</span>
            </td>
            <td>
              <span :class="['font-bold', ms.repo ? 'text-[var(--accent)]' : 'text-[var(--text3)]']">
                {{ ms.repo || 'Unassigned' }}
              </span>
            </td>
            <td>
              <span :class="['font-bold', ms.editor ? 'text-[var(--accent)]' : 'text-[var(--text3)]']">
                {{ ms.editor || 'Unassigned' }}
              </span>
            </td>
            <td class="table-actions">
              <button @click="openModal(ms)" class="btn-primary btn-compact inline-flex">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="mr-1"><path d="m15 3 6 6-6 6M21 9H9a6 6 0 0 0-6 6v6"/></svg>
                ASSIGN
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- MODAL -->
    <div class="modal-overlay" :class="{ open: isModalOpen }">
      <div class="modal" style="width: min(96vw, 640px);">
        <div class="modal-header">
          <h2>Assign Personnel</h2>
          <button class="modal-close" @click="closeModal">X</button>
        </div>
        
        <div class="modal-body custom-scroll bg-[var(--surface2)]">
          <div class="modal-panel mb-6" style="background: var(--bg);">
            <div class="meta-kicker mb-1">Editing Manuscript</div>
            <div class="text-[var(--text)] font-semibold">{{ form.code }}</div>
            <div class="text-xs text-[var(--text2)] mt-1 truncate">{{ form.title }}</div>
          </div>

          <div class="form-group mb-5">
            <label class="accent-label">Assign REPO Staff</label>
            <select v-model="form.repo">
              <option value="">- Unassigned -</option>
              <option v-for="staff in repoStaffList" :key="staff.id" :value="staff.name">{{ staff.name }}</option>
            </select>
            <div class="modal-note">Responsible for prescreening and initial processing.</div>
          </div>

          <div class="form-group mb-5">
            <label class="accent-label">Assign Associate Editor</label>
            <select v-model="form.editor">
              <option value="">- Unassigned -</option>
              <option v-for="ed in editorList" :key="ed.id" :value="ed.name">{{ ed.name }}</option>
            </select>
            <div class="modal-note">Responsible for managing peer reviews and final decision status.</div>
          </div>

          <div class="form-group">
            <label>EIC Remarks & Instructions</label>
            <textarea v-model="form.eic" rows="3" placeholder="Enter special instructions for the team..."></textarea>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn-outline" @click="closeModal">Cancel</button>
          <button class="btn-primary" @click="saveAssignment">Save Assignment</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const manuscripts = ref([]);
const usersList = ref([]);
const searchQuery = ref('');
const assignmentFilter = ref('all');

const isModalOpen = ref(false);
const editingId = ref(null);
const form = ref({ code: '', title: '', repo: '', editor: '', eic: '' });

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost/mjsir_tracker/backend/public/index.php/api';
const api = axios.create({ baseURL: API_BASE_URL });

onMounted(async () => {
  await fetchManuscripts();
  await fetchUsers();
});

const fetchManuscripts = async () => {
  try {
    const response = await api.get('/manuscripts');
    manuscripts.value = response.data;
  } catch (error) {
    console.error("Error fetching data:", error);
  }
};

const fetchUsers = async () => {
  try {
    const response = await api.get('/users');
    usersList.value = response.data;
  } catch (error) {
    console.error("Error fetching users:", error);
  }
};

const repoStaffList = computed(() => usersList.value.filter(u => u.role === 'repo_staff'));
const editorList = computed(() => usersList.value.filter(u => u.role === 'editor'));

const filteredManuscripts = computed(() => {
  let result = manuscripts.value;
  
  if (assignmentFilter.value === 'unassigned') {
    result = result.filter(m => !m.repo && !m.editor);
  } else if (assignmentFilter.value === 'assigned') {
    result = result.filter(m => m.repo || m.editor);
  }

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(m => 
      (m.code || '').toLowerCase().includes(q) || 
      (m.title || '').toLowerCase().includes(q) || 
      (m.authors || '').toLowerCase().includes(q)
    );
  }
  return result;
});

const openModal = (ms) => {
  editingId.value = ms.id;
  // We only pull the fields we care about for updating assignments
  form.value = {
    code: ms.code,
    title: ms.title,
    repo: ms.repo || '',
    editor: ms.editor || '',
    eic: ms.eic || ''
  };
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  editingId.value = null;
};

const saveAssignment = async () => {
  try {
    // Send only the updated fields to the server
    const updateData = {
      repo: form.value.repo,
      editor: form.value.editor,
      eic: form.value.eic
    };
    
    await api.post(`/manuscripts/${editingId.value}`, updateData, {
      headers: { 'Content-Type': 'application/json' }
    });
    
    try {
      const message = `EIC assigned ${form.value.code} to your workflow`;
      if (form.value.repo) {
        await api.post('/notifications', { target_user: form.value.repo, message });
      }
      if (form.value.editor) {
        await api.post('/notifications', { target_user: form.value.editor, message });
      }
    } catch(e) { console.error(e); }
    
    await fetchManuscripts();
    closeModal();
  } catch (error) {
    console.error("Error saving assignment:", error);
    alert("An error occurred while saving the assignment.");
  }
};
</script>



