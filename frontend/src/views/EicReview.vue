<template>
  <div>
    <div class="page-header-stack mb-6">
      <h1 class="page-title">EIC Review Queue</h1>
      <div class="page-subtitle">Manuscripts flagged by Associate Editors for Editor-in-Chief review before proceeding.</div>
    </div>

    <!-- DATA TABLE -->
    <div class="table-container">
      <table class="tbl">
        <thead>
          <tr>
            <th>ID / Code</th>
            <th style="min-width: 250px;">Title & Authors</th>
            <th>Editor Remarks</th>
            <th>Editor Comments</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="eicReviewManuscripts.length === 0">
            <td colspan="5" class="table-empty">No manuscripts currently require EIC review.</td>
          </tr>
          <tr v-for="ms in eicReviewManuscripts" :key="ms.id">
            <td>
              <div class="td-title">{{ ms.code }}</div>
              <div class="td-sub">{{ ms.date || '-' }}</div>
            </td>
            <td>
              <div class="td-title mb-1">{{ ms.title || '-' }}</div>
              <div class="td-sub">{{ ms.authors || '-' }}</div>
            </td>
            <td>
              <span class="status-badge review mb-1">For EIC before review</span>
              <div class="td-sub">By: <span class="font-bold text-[var(--text)]">{{ ms.editor || 'Unknown' }}</span></div>
            </td>
            <td>
              <div class="td-sub max-w-[200px] truncate" :title="ms.editor_comments">{{ ms.editor_comments || 'No comments provided.' }}</div>
            </td>
            <td class="table-actions">
              <button @click="openModal(ms)" class="btn-primary btn-compact inline-flex">
                REVIEW & DECIDE
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- MODAL -->
    <div class="modal-overlay" :class="{ open: isModalOpen }">
      <div class="modal w-[800px]">
        <div class="modal-header">
          <h2>EIC Review Action</h2>
          <button class="modal-close" @click="closeModal">X</button>
        </div>
        
        <div class="modal-body custom-scroll">
          <div class="modal-panel mb-6">
            <div class="meta-kicker mb-1" style="color: var(--accent);">{{ form.code }}</div>
            <div class="text-[var(--text)] font-semibold text-lg">{{ form.title }}</div>
            
            <div class="mt-4 p-4 rounded bg-amber-500/10 border border-amber-500/20">
              <p class="text-xs font-bold text-amber-500 uppercase tracking-widest mb-1">Editor's Comments / Reason for Flagging</p>
              <p class="text-sm text-[var(--text2)]">{{ form.editor_comments || 'No comments were provided by the editor.' }}</p>
            </div>
          </div>

          <!-- FULL MANUSCRIPT OVERVIEW -->
          <div class="form-grid mb-6">
            <div class="form-group">
              <label>Author(s)</label>
              <input :value="form.authors || '-'" disabled>
            </div>
            <div class="form-group">
              <label>Contributor Type</label>
              <input :value="form.contributor_type || '-'" disabled>
            </div>
            <div class="form-group full">
              <label>Affiliation</label>
              <input :value="form.inst || '-'" disabled>
            </div>
            <div class="form-group">
              <label>Review Round</label>
              <input :value="form.review_round ? form.review_round + (form.review_round == 1 ? 'st' : form.review_round == 2 ? 'nd' : form.review_round == 3 ? 'rd' : 'th') + ' Review' : '1st Review'" disabled class="font-bold text-[var(--accent)]">
            </div>
            <div class="form-group">
              <label>Assigned Editor</label>
              <input :value="form.editor || 'Unassigned'" disabled>
            </div>
            <div class="form-group">
              <label>Turnitin Result (%)</label>
              <a v-if="form.turnitin_link" :href="form.turnitin_link" target="_blank" class="flex items-center h-[38px] px-3 bg-red-50 border border-red-200 rounded text-red-600 font-bold hover:bg-red-100 transition-colors">
                {{ form.turnitin || 'View Report' }} <span class="ml-auto text-lg">↗</span>
              </a>
              <a v-else-if="form.turnitin_file" :href="baseUrl + '/' + form.turnitin_file" target="_blank" class="flex items-center h-[38px] px-3 bg-red-50 border border-red-200 rounded text-red-600 font-bold hover:bg-red-100 transition-colors">
                {{ form.turnitin || 'View File' }} <span class="ml-auto text-lg">↗</span>
              </a>
              <input v-else :value="form.turnitin || '-'" disabled>
            </div>
            
            <div class="form-group">
              <label>Original Manuscript</label>
              <a v-if="form.link" :href="form.link" target="_blank" class="flex items-center h-[38px] px-3 bg-[var(--accent)]/10 border border-[var(--accent)]/30 rounded text-[var(--accent)] font-bold hover:bg-[var(--accent)]/20 transition-colors truncate">
                View External Link <span class="ml-auto text-lg">↗</span>
              </a>
              <a v-else-if="form.file_path" :href="baseUrl + '/' + form.file_path" target="_blank" class="flex items-center h-[38px] px-3 bg-[var(--accent)]/10 border border-[var(--accent)]/30 rounded text-[var(--accent)] font-bold hover:bg-[var(--accent)]/20 transition-colors truncate">
                View Uploaded File <span class="ml-auto text-lg">↗</span>
              </a>
              <div v-else class="flex items-center h-[38px] px-3 bg-[var(--surface2)] border border-[var(--border)] rounded text-[13px] opacity-80 italic text-[var(--text3)]">
                No file
              </div>
            </div>
            
            <div class="form-group">
              <label>Revised Document</label>
              <a v-if="form.revised_file" :href="baseUrl + '/' + form.revised_file" target="_blank" class="flex items-center h-[38px] px-3 bg-[var(--accent)]/10 border border-[var(--accent)]/30 rounded text-[var(--accent)] font-bold hover:bg-[var(--accent)]/20 transition-colors truncate">
                View Revised File <span class="ml-auto text-lg">↗</span>
              </a>
              <div v-else class="flex items-center h-[38px] px-3 bg-[var(--surface2)] border border-[var(--border)] rounded text-[13px] opacity-80 italic text-[var(--text3)]">
                No file
              </div>
            </div>
          </div>

          <div class="form-grid">
            <div class="form-group full">
              <label>EIC Instructions to Editor / Author</label>
              <textarea v-model="form.eic" rows="4" placeholder="Provide instructions, feedback, or decisions here..."></textarea>
            </div>

            <div class="form-group full">
              <label>Override Editor Remarks (Update Status)</label>
              <select v-model="form.editor_remarks">
                <option value="For EIC before review">Keep: For EIC before review</option>
                <option value="Return to author for review">Return to author for review</option>
                <option value="Go for review">Go for review</option>
                <option value="Reject">Reject</option>
              </select>
              <p class="text-[10px] text-[var(--text3)] mt-1">If you change this, it will be removed from this queue and return to normal processing.</p>
            </div>
          </div>
        </div>
        
        <div class="modal-footer">
          <button class="btn-outline" @click="closeModal">Cancel</button>
          <button class="btn-primary" @click="saveTask">Save EIC Decision</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const manuscripts = ref([]);
const isModalOpen = ref(false);
const editingId = ref(null);

const form = ref({});

const baseUrl = import.meta.env.VITE_API_BASE_URL ? import.meta.env.VITE_API_BASE_URL.replace('/index.php/api', '') : 'http://localhost/mjsir_tracker/backend/public';
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost/mjsir_tracker/backend/public/index.php/api';
const api = axios.create({ baseURL: API_BASE_URL });

onMounted(async () => {
  await fetchManuscripts();
});

const fetchManuscripts = async () => {
  try {
    const response = await api.get('/manuscripts');
    manuscripts.value = response.data;
  } catch (error) {
    console.error("Error fetching data:", error);
  }
};

const eicReviewManuscripts = computed(() => {
  return manuscripts.value.filter(m => m.editor_remarks === 'For EIC before review');
});

const openModal = (ms) => {
  editingId.value = ms.id;
  form.value = { ...ms };
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  editingId.value = null;
};

const saveTask = async () => {
  const formData = new FormData();
  
  formData.append('eic', form.value.eic);
  formData.append('editor_remarks', form.value.editor_remarks);

  try {
    const config = { headers: { 'Content-Type': 'multipart/form-data' } };
    await api.post(`/manuscripts/${editingId.value}`, formData, config);
    
    // Send Notification
    try {
      const message = `EIC updated instructions for ${form.value.code}`;
      await api.post('/notifications', { target_user: 'repo_staff', message });
      if (form.value.editor) {
        await api.post('/notifications', { target_user: form.value.editor, message });
      }
    } catch(e) { console.error(e); }

    await fetchManuscripts();
    closeModal();
  } catch (error) {
    console.error("Error saving task:", error);
    alert("An error occurred while saving.");
  }
};
</script>
