<template>
  <div>
    <div class="page-header">
      <div class="page-header-stack">
        <h1 class="page-title">Prescreening Process</h1>
        <div class="page-subtitle">Filter, review, and update publication records with consistent metadata.</div>
      </div>
      <button 
        v-if="canAddManuscript"
        class="btn-primary" 
        @click="openModal()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Add to Prescreening
      </button>
    </div>

    <!-- FILTER BAR -->
    <div class="filters-bar">
      <div class="filter-group">
        <label>Year</label>
        <select class="filter-input" v-model="yearFilter">
          <option value="">All Years</option>
          <option>2026</option>
          <option>2025</option>
          <option>2024</option>
        </select>
      </div>
      <div class="filter-group">
        <label>Type / Origin</label>
        <select class="filter-input" v-model="typeFilter">
          <option value="">All Types</option>
          <option>External</option>
          <option>Internal</option>
          <option>International</option>
        </select>
      </div>
      <div class="filter-group flex-[2]">
        <label>Search</label>
        <input type="text" class="filter-input" v-model="searchQuery" placeholder="Title, authors, ID...">
      </div>
    </div>

    <!-- DATA TABLE -->
    <div class="table-container">
      <table class="tbl">
        <thead>
          <tr>
            <th style="width: 40px"></th>
            <th>ID / Year</th>
            <th style="min-width: 250px;">Title & Authors</th>
            <th>Type</th>
            <th>Assignment</th>
            <th>Status</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filteredManuscripts.length === 0">
            <td colspan="7" class="table-empty">No manuscripts found.</td>
          </tr>
          <tr v-for="ms in filteredManuscripts" :key="ms.id">
            <td><input type="checkbox" class="accent-[var(--accent)]" /></td>
            <td>
              <div class="td-title">{{ ms.code }}</div>
              <div class="td-sub">{{ ms.year || ms.date || '-' }}</div>
            </td>
            <td>
              <div class="td-title mb-1">{{ ms.title || '-' }}</div>
              <div class="td-sub">
                <span v-if="currentRole === 'editor'" class="italic text-[var(--text3)]">[Hidden for Blind Review]</span>
                <span v-else>{{ ms.authors || '-' }}</span>
              </div>
              <div v-if="ms.file_path" class="mt-1">
                <a :href="baseUrl + '/' + ms.file_path" target="_blank" class="text-[var(--teal)] text-[10px] font-bold uppercase hover:underline">View Document</a>
              </div>
            </td>
            <td>
              <span class="status-badge default">{{ ms.type || ms.contributor_type || '-' }}</span>
            </td>
            <td>
              <div class="td-sub"><span class="font-bold text-[var(--text2)]">REPO:</span> {{ ms.repo || 'None' }}</div>
              <div class="td-sub"><span class="font-bold text-[var(--text2)]">EDITOR:</span> {{ ms.editor || 'None' }}</div>
            </td>
            <td>
              <span :class="['status-badge mb-1', getStatusClass(ms.prescreen_status || ms.editor_remarks)]">
                {{ ms.editor_remarks || 'Pending' }}
              </span>
              <div class="td-sub truncate max-w-[120px]" :title="ms.action_taken">{{ ms.action_taken || '-' }}</div>
            </td>
            <td class="table-actions">
              <button @click="openModal(ms)" class="btn-outline btn-compact">
                EDIT
              </button>
              <button v-if="canDeleteManuscript" @click="deleteManuscript(ms.id)" class="btn-outline btn-compact btn-danger-ghost">
                DEL
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- MODAL -->
    <div class="modal-overlay" :class="{ open: isModalOpen }">
      <div class="modal">
        <div class="modal-header">
          <h2>{{ editingId ? 'Update Record' : 'Add to Prescreening' }}</h2>
          <button class="modal-close" @click="closeModal">X</button>
        </div>
        
        <div class="modal-tabs">
          <button v-if="canViewInitial" @click="activeTab = 'submission'" :class="['modal-tab', { active: activeTab === 'submission' }]">Submission Details</button>
          <button v-if="canViewInitial" @click="activeTab = 'repo_processing'" :class="['modal-tab', { active: activeTab === 'repo_processing' }]">REPO Processing</button>
          <button v-if="canViewReview" @click="activeTab = 'reviewers'" :class="['modal-tab', { active: activeTab === 'reviewers' }]">Associate Editor</button>
        </div>

        <div class="modal-body custom-scroll">
          
          <div v-if="errorMessage" class="mb-4 p-3 rounded-md bg-red-900/20 border border-red-500/30 text-red-400 text-sm font-semibold flex items-center gap-2">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            {{ errorMessage }}
          </div>

          <!-- TAB 1: Submission Details -->
          <div v-show="activeTab === 'submission'" class="form-grid">
            <div class="form-group">
              <label>Contributor Type</label>
              <select v-model="form.contributor_type" :disabled="!canEditInitial">
                <option value="">-</option>
                <option>External</option>
                <option>Internal</option>
                <option>International</option>
              </select>
            </div>
            <div class="form-group">
              <label>Submission Form</label>
              <select v-model="form.submission_form" :disabled="!canEditInitial">
                <option value="">-</option>
                <option>OJS</option>
                <option>Email</option>
              </select>
            </div>
            <div class="form-group">
              <label>Manuscript Code</label>
              <input v-model="form.code" placeholder="MJSIR2026_01" :disabled="!canEditInitial">
            </div>
            <div class="form-group">
              <label>Date of Submission</label>
              <input type="date" v-model="form.date" :disabled="!canEditInitial">
            </div>
            <div class="form-group full">
              <label>Title of Article</label>
              <textarea v-model="form.title" rows="2" :disabled="!canEditInitial"></textarea>
            </div>
            <div class="form-group">
              <label>Author(s)</label>
              <input :value="currentRole === 'editor' ? '[Hidden for Blind Review]' : form.authors" @input="form.authors = $event.target.value" placeholder="John Doe, Jane Smith" :disabled="!canEditInitial">
            </div>
            <div class="form-group">
              <label>Email Address</label>
              <input type="text" :value="currentRole === 'editor' ? '[Hidden for Blind Review]' : form.author_email" @input="form.author_email = $event.target.value" placeholder="author@email.com" :disabled="!canEditInitial">
            </div>
            <div class="form-group full">
              <label>Affiliation / Agency</label>
              <input :value="currentRole === 'editor' ? '[Hidden for Blind Review]' : form.inst" @input="form.inst = $event.target.value" placeholder="University / Organization" :disabled="!canEditInitial">
            </div>
            <div class="form-group">
              <label>Upload File (Manuscript)</label>
              <div class="flex items-center gap-4">
                <input type="file" @change="handleFileUpload" class="!w-auto flex-1">
                <span v-if="form.file_path" class="text-[var(--accent)] font-bold text-[11px] uppercase whitespace-nowrap"><a :href="baseUrl + '/' + form.file_path" target="_blank">View File</a></span>
              </div>
            </div>
            <div class="form-group">
              <label>Or Add File Link</label>
              <input type="url" v-model="form.link" placeholder="https://drive.google.com/..." :disabled="!canEditInitial">
            </div>
            <div class="form-group full mt-2 pt-2 border-t border-[var(--border2)]">
              <label>Submission Notes / Remarks</label>
              <textarea v-model="form.prenotes" rows="2" placeholder="Enter any initial notes or special instructions..." :disabled="!canEditInitial"></textarea>
            </div>
          </div>

          <!-- TAB 2: REPO Processing -->
          <div v-show="activeTab === 'repo_processing'" class="form-grid">
            <div class="form-group full">
              <label>Manuscript Type</label>
              <select v-model="form.manuscript_type" :disabled="!canEditInitial">
                <option value="">-</option>
                <option>Research Article</option>
                <option>Research Note</option>
                <option>Systematic Review</option>
              </select>
            </div>
            <div class="form-group full">
              <label>Turnitin Result (%)</label>
              <input v-model="form.turnitin" placeholder="e.g. 15%" :disabled="!canEditInitial">
            </div>

            <div class="form-group full" v-if="canEditInitial">
              <label>Turnitin Report (Upload File)</label>
              <div class="flex items-center gap-4">
                <input type="file" @change="handleTurnitinUpload" class="!w-auto flex-1">
                <span v-if="form.turnitin_file" class="text-[var(--accent)] font-bold text-[11px] uppercase whitespace-nowrap"><a :href="baseUrl + '/' + form.turnitin_file" target="_blank">View File</a></span>
              </div>
            </div>

            <div class="form-group full" v-if="canEditInitial">
              <label>Or Add Turnitin Google Drive Link</label>
              <input type="url" v-model="form.turnitin_link" placeholder="https://drive.google.com/..." :disabled="!canEditInitial">
            </div>
            

            <!-- STATUS AND REMARKS FOR REPO STAFF -->
            <div class="form-group full mt-2 pt-4 border-t border-[var(--border2)]">
              <label>Comments / Suggestions</label>
              <textarea v-model="form.editor_comments" rows="3" :disabled="!canEditInitial" placeholder="Enter comments or suggestions..."></textarea>
            </div>

            <!-- DYNAMIC SUGGESTED REVIEWERS FOR REPO STAFF -->
            <div class="form-group full modal-panel mt-4">
              <div class="flex items-center justify-between mb-4">
                <h4 class="text-[11px] font-bold text-[var(--accent)] uppercase tracking-widest">Suggested Reviewers</h4>
                <button v-if="canEditInitial" @click="addSuggestedReviewer" class="btn-outline btn-mini">+ Add Reviewer</button>
              </div>
              
              <div v-for="(rev, idx) in suggestedReviewers" :key="idx" class="grid grid-cols-10 gap-4 mb-2 items-end">
                <div class="form-group col-span-3">
                  <label v-if="idx === 0">Reviewer Name</label>
                  <input v-model="rev.name" :disabled="!canEditInitial" placeholder="Name">
                </div>
                <div class="form-group col-span-3">
                  <label v-if="idx === 0">Affiliation</label>
                  <input v-model="rev.affil" :disabled="!canEditInitial" placeholder="Affiliation">
                </div>
                <div class="form-group col-span-3">
                  <label v-if="idx === 0">Email Address</label>
                  <input type="email" v-model="rev.email" :disabled="!canEditInitial" placeholder="Email">
                </div>
                <div class="form-group col-span-1 text-right pb-1">
                  <button v-if="canEditInitial && suggestedReviewers.length > 1" @click="removeSuggestedReviewer(idx)" class="text-red-400 hover:text-red-300 text-xs font-bold px-2 py-2 border border-red-900 rounded bg-red-900/20">X</button>
                </div>
              </div>
            </div>
            
            <div class="form-group">
              <label>Remarks</label>
              <select v-model="form.editor_remarks" :disabled="!canEditInitial">
                <option value="">-</option>
                <option>Return to author for review</option>
                <option>Go for review</option>
                <option>For EIC before review</option>
                <option>Reject</option>
              </select>
            </div>

            <div class="form-group">
              <label>Prescreening Status</label>
              <select v-model="form.prescreen_status" :disabled="!canEditInitial">
                <option value="">-</option>
                <option>Done</option>
                <option>Ongoing</option>
              </select>
            </div>

            <div class="form-group full">
              <label>Action Taken</label>
              <select v-model="form.action_taken" :disabled="!canEditInitial">
                <option value="">-</option>
                <option>Sent rejection letter</option>
                <option>Sent for review</option>
                <option>Sent for reviewers</option>
                <option>Sent for author revision</option>
              </select>
            </div>
          </div>





          <!-- TAB 4: Associate Editor Dashboard -->
          <div v-show="activeTab === 'reviewers'" class="form-grid">
            
            <div class="form-group full modal-panel mb-2">
              <div class="flex items-center justify-between mb-4">
                <h4 class="text-[11px] font-bold text-[var(--accent)] uppercase tracking-widest">Suggested Reviewers</h4>
                <button v-if="canEditReview" @click="addSuggestedReviewer" class="btn-outline btn-mini">+ Add Reviewer</button>
              </div>
              
              <div v-for="(rev, idx) in suggestedReviewers" :key="idx" class="grid grid-cols-10 gap-4 mb-2 items-end">
                <div class="form-group col-span-3">
                  <label v-if="idx === 0">Reviewer Name</label>
                  <input v-model="rev.name" :disabled="!canEditReview" placeholder="Name">
                </div>
                <div class="form-group col-span-3">
                  <label v-if="idx === 0">Affiliation</label>
                  <input v-model="rev.affil" :disabled="!canEditReview" placeholder="Affiliation">
                </div>
                <div class="form-group col-span-3">
                  <label v-if="idx === 0">Email Address</label>
                  <input type="email" v-model="rev.email" :disabled="!canEditReview" placeholder="Email">
                </div>
                <div class="form-group col-span-1 text-right pb-1">
                  <button v-if="canEditReview && suggestedReviewers.length > 1" @click="removeSuggestedReviewer(idx)" class="text-red-400 hover:text-red-300 text-xs font-bold px-2 py-2 border border-red-900 rounded bg-red-900/20">X</button>
                </div>
              </div>
            </div>

            <div class="form-group full mt-2">
              <label>Comments / Suggestions</label>
              <textarea v-model="form.editor_comments" rows="3" :disabled="!canEditReview" placeholder="Enter comments or suggestions..."></textarea>
            </div>
            
            <div class="form-group">
              <label>Remarks</label>
              <select v-model="form.editor_remarks" :disabled="!canEditReview">
                <option value="">-</option>
                <option>Return to author for review</option>
                <option>Go for review</option>
                <option>For EIC before review</option>
                <option>Reject</option>
              </select>
            </div>

            <div class="form-group">
              <label>Prescreening Status</label>
              <select v-model="form.prescreen_status" :disabled="!canEditReview">
                <option value="">-</option>
                <option>Done</option>
                <option>Ongoing</option>
              </select>
            </div>

            <div class="form-group full">
              <label>Action Taken</label>
              <select v-model="form.action_taken" :disabled="!canEditReview">
                <option value="">-</option>
                <option>Sent rejection letter</option>
                <option>Sent for review</option>
                <option>Sent for reviewers</option>
                <option>Sent for author revision</option>
              </select>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button class="btn-outline" @click="closeModal">Cancel</button>
          <button class="btn-primary" @click="saveManuscript" v-if="canSaveForm">Save Publication</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

// --- ACCESS CONTROL ---
const currentRole = ref(localStorage.getItem('user_role') || '');

const canViewInitial = computed(() => true);
const canEditInitial = computed(() => ['eic', 'repo_staff'].includes(currentRole.value));

const canViewReview = computed(() => true);
const canEditReview = computed(() => currentRole.value === 'eic');

const canAddManuscript = computed(() => ['eic', 'repo_staff'].includes(currentRole.value));
const canDeleteManuscript = computed(() => ['eic', 'repo_staff'].includes(currentRole.value));

// Compute if the user has any edit rights on the current active tab
const canSaveForm = computed(() => {
  if (activeTab.value === 'submission') return canEditInitial.value;
  if (activeTab.value === 'repo_processing') return canEditInitial.value;
  if (activeTab.value === 'reviewers') return canEditReview.value;
  return false;
});

// --- DATA LOGIC ---
const manuscripts = ref([]);
const usersList = ref([]);
const searchQuery = ref('');
const typeFilter = ref('');
const yearFilter = ref('');

const isModalOpen = ref(false);
const editingId = ref(null);
const activeTab = ref('submission');
const selectedFile = ref(null);
const turnitinFile = ref(null);
const suggestedReviewers = ref([{ name: '', affil: '', email: '' }]);
const errorMessage = ref('');

const baseUrl = import.meta.env.VITE_API_BASE_URL ? import.meta.env.VITE_API_BASE_URL.replace('/index.php/api', '') : 'http://localhost/mjsir_tracker/backend/public';

const form = ref({
  contributor_type: '', submission_form: '', manuscript_type: '', code: '', title: '', turnitin: '', file_path: '',
  date: '', authors: '', author_email: '', inst: '', link: '', prenotes: '', turnitin_link: '',
  criteria_scope: 0, criteria_methodology: 0, criteria_format: 0,
  editor_comments: '', editor_remarks: '', prescreen_status: '', action_taken: '',
  srev: '', srev1_affil: '', srev1_email: '',
  srev2_name: '', srev2_affil: '', srev2_email: '',
  srev3_name: '', srev3_affil: '', srev3_email: '',
  eic: ''
});

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

const getStatusClass = (status) => {
  if (!status) return 'default';
  const s = status.toLowerCase();
  if (s.includes('accept') || s.includes('done')) return 'accepted';
  if (s.includes('reject') || s.includes('return')) return 'rejected';
  if (s.includes('review') || s.includes('recheck') || s.includes('ongoing')) return 'review';
  return 'default';
};

const filteredManuscripts = computed(() => {
  let result = manuscripts.value;
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(m => 
      (m.code || '').toLowerCase().includes(q) || 
      (m.title || '').toLowerCase().includes(q) || 
      (m.authors || '').toLowerCase().includes(q)
    );
  }
  if (typeFilter.value) {
    result = result.filter(m => m.contributor_type === typeFilter.value || m.type === typeFilter.value);
  }
  if (yearFilter.value) {
    result = result.filter(m => (m.year || '').includes(yearFilter.value) || (m.date || '').includes(yearFilter.value));
  }
  return result;
});

const handleFileUpload = (event) => {
  selectedFile.value = event.target.files[0];
};

const handleTurnitinUpload = (event) => {
  turnitinFile.value = event.target.files[0];
};

const openModal = (ms = null) => {
  // Always open on the first tab the user has access to
  activeTab.value = 'submission';
  errorMessage.value = '';

  selectedFile.value = null;
  turnitinFile.value = null;
  if (ms) {
    editingId.value = ms.id;
    form.value = { ...ms };
    if (ms.suggested_reviewers) {
      try {
        suggestedReviewers.value = JSON.parse(ms.suggested_reviewers);
      } catch (e) {
        suggestedReviewers.value = [{ name: '', affil: '', email: '' }];
      }
    } else {
      suggestedReviewers.value = [{ name: '', affil: '', email: '' }];
    }
  } else {
    editingId.value = null;
    form.value = {
      contributor_type: '', submission_form: '', manuscript_type: '', code: '', title: '', turnitin: '', file_path: '',
      date: '', authors: '', author_email: '', inst: '', link: '', prenotes: '', turnitin_link: '',
      criteria_scope: 0, criteria_methodology: 0, criteria_format: 0,
      editor_comments: '', editor_remarks: '', prescreen_status: '', action_taken: '',
      eic: ''
    };
    suggestedReviewers.value = [{ name: '', affil: '', email: '' }];
  }
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  editingId.value = null;
  errorMessage.value = '';
};

const saveManuscript = async () => {
  errorMessage.value = '';

  if (!form.value.code) {
    errorMessage.value = "Validation Error: Please enter a Manuscript Code / ID.";
    activeTab.value = 'submission';
    return;
  }
  if (!form.value.title) {
    errorMessage.value = "Validation Error: Please enter the Title of Article.";
    activeTab.value = 'submission';
    return;
  }
  if (!form.value.contributor_type) {
    errorMessage.value = "Validation Error: Please select a Contributor Type.";
    activeTab.value = 'submission';
    return;
  }
  
  if (!selectedFile.value && !form.value.link && !form.value.file_path) {
    errorMessage.value = "Validation Error: Please either upload a Manuscript file or provide a File Link.";
    activeTab.value = 'submission';
    return;
  }

  const formData = new FormData();
  
  form.value.suggested_reviewers = JSON.stringify(suggestedReviewers.value);

  if (form.value.contributor_type && !form.value.type) {
    form.value.type = form.value.contributor_type;
  }

  for (const key in form.value) {
    if (form.value[key] !== null && form.value[key] !== undefined) {
      formData.append(key, form.value[key]);
    }
  }

  if (selectedFile.value) {
    formData.append('upload_file', selectedFile.value);
  }
  
  if (turnitinFile.value) {
    formData.append('turnitin_file', turnitinFile.value);
  }

  try {
    const config = { headers: { 'Content-Type': 'multipart/form-data' } };
    if (editingId.value) {
      await api.post(`/manuscripts/${editingId.value}`, formData, config);
    } else {
      if (!form.value.year && form.value.code) {
        const match = form.value.code.match(/\d{4}/);
        if (match) formData.append('year', match[0]);
      }
      await api.post('/manuscripts', formData, config);
    }
    
    await fetchManuscripts();
    closeModal();
  } catch (error) {
    console.error("Error saving:", error);
    alert("An error occurred while saving.");
  }
};

const deleteManuscript = async (id) => {
  if (!confirm("Are you sure you want to delete this manuscript?")) return;
  try {
    await api.delete(`/manuscripts/${id}`);
    await fetchManuscripts();
  } catch (error) {
    console.error("Error deleting:", error);
  }
};

const addSuggestedReviewer = () => {
  suggestedReviewers.value.push({ name: '', affil: '', email: '' });
};

const removeSuggestedReviewer = (index) => {
  if (suggestedReviewers.value.length > 1) {
    suggestedReviewers.value.splice(index, 1);
  }
};
</script>



