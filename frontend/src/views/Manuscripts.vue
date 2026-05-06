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
      <div class="modal" style="width: 900px">
        <div class="modal-header">
          <h2>{{ editingId ? 'Update Record' : 'Add to Prescreening' }}</h2>
          <button class="modal-close" @click="closeModal">X</button>
        </div>
        
        <div class="modal-tabs">
          <button v-if="canViewInitial" @click="activeTab = 'submission'" :class="['modal-tab', { active: activeTab === 'submission' }]">Submission Details</button>
          <button v-if="canViewInitial" @click="activeTab = 'repo_processing'" :class="['modal-tab', { active: activeTab === 'repo_processing' }]">Processing & Rounds</button>
          <button v-if="canViewReview" @click="activeTab = 'reviewers'" :class="['modal-tab', { active: activeTab === 'reviewers' }]">Reviewers Tracking</button>
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

          <!-- TAB 2: REPO Processing & Rounds -->
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

            <!-- DYNAMIC REVIEW ROUNDS -->
            <div class="form-group full mt-6 mb-2">
              <h3 class="font-bold text-[14px] text-[var(--text)] mb-4 border-b border-[var(--border)] pb-2">Review Rounds Tracking</h3>
              
              <div v-for="(round, idx) in reviewRounds" :key="idx" class="modal-panel mb-4 p-4 border border-[var(--border)] rounded-md bg-[var(--surface2)]">
                <div class="flex items-center justify-between mb-4">
                  <h4 class="text-[12px] font-bold text-[var(--accent)] uppercase tracking-widest">Review Round {{ round.round_number }}</h4>
                  <button v-if="reviewRounds.length > 1 && canEditInitial" @click="removeRound(idx)" class="text-red-400 hover:text-red-300 text-xs font-bold px-2 py-1 border border-red-900 rounded bg-red-900/20">X Remove</button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div class="form-group">
                    <label>Revised Document (Upload)</label>
                    <div class="flex items-center gap-2">
                      <input type="file" @change="handleRoundRevisedUpload($event, idx)" class="!w-auto flex-1 text-xs" :disabled="!canEditInitial">
                      <span v-if="round.revised_file_path" class="text-[var(--accent)] font-bold text-[10px] uppercase whitespace-nowrap"><a :href="baseUrl + '/' + round.revised_file_path" target="_blank">View File</a></span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label>Or Revised Document Link</label>
                    <input type="url" v-model="round.revised_link" placeholder="https://drive.google.com/..." :disabled="!canEditInitial">
                  </div>

                  <div class="form-group">
                    <label>Action Taken Document (Upload)</label>
                    <div class="flex items-center gap-2">
                      <input type="file" @change="handleRoundActionUpload($event, idx)" class="!w-auto flex-1 text-xs" :disabled="!canEditInitial">
                      <span v-if="round.action_taken_file_path" class="text-[var(--accent)] font-bold text-[10px] uppercase whitespace-nowrap"><a :href="baseUrl + '/' + round.action_taken_file_path" target="_blank">View File</a></span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label>Or Action Taken Link</label>
                    <input type="url" v-model="round.action_taken_link" placeholder="https://drive.google.com/..." :disabled="!canEditInitial">
                  </div>

                  <div class="form-group col-span-2">
                    <label>Action Taken Status</label>
                    <select v-model="round.action_taken" :disabled="!canEditInitial">
                      <option value="">-</option>
                      <option>Sent rejection letter</option>
                      <option>Sent for review</option>
                      <option>Sent for reviewers</option>
                      <option>Sent for author revision</option>
                    </select>
                  </div>
                </div>
              </div>
              
              <button v-if="canEditInitial" @click="addRound" class="btn-outline btn-compact w-full border-dashed">+ Add Review Round</button>
            </div>

            <div class="form-group full mt-2 pt-4 border-t border-[var(--border2)]">
              <label>Comments / Suggestions</label>
              <textarea v-model="form.editor_comments" rows="3" :disabled="!canEditInitial" placeholder="Enter comments or suggestions..."></textarea>
            </div>
            
            <div class="form-group">
              <label>Overall Remarks</label>
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
          </div>


          <!-- TAB 3: REVIEWERS TRACKING -->
          <div v-show="activeTab === 'reviewers'" class="form-grid">
            <div class="form-group full">
              <h3 class="font-bold text-[14px] text-[var(--text)] mb-4 border-b border-[var(--border)] pb-2">Reviewers Tracking</h3>
              
              <div v-for="(rev, idx) in suggestedReviewers" :key="idx" class="modal-panel mb-4 p-4 border border-[var(--border)] rounded-md bg-[var(--surface2)]">
                <div class="flex items-center justify-between mb-4">
                  <h4 class="text-[12px] font-bold text-[var(--accent)] uppercase tracking-widest">Reviewer {{ idx + 1 }}</h4>
                  <button v-if="canEditReview" @click="removeSuggestedReviewer(idx)" class="text-red-400 hover:text-red-300 text-xs font-bold px-2 py-1 border border-red-900 rounded bg-red-900/20">X Remove</button>
                </div>
                
                <div class="grid grid-cols-3 gap-4 mb-3">
                  <div class="form-group">
                    <label>Reviewer Name</label>
                    <input v-model="rev.name" placeholder="Name" :disabled="!canEditReview">
                  </div>
                  <div class="form-group">
                    <label>Affiliation</label>
                    <input v-model="rev.affiliation" placeholder="Affiliation" :disabled="!canEditReview">
                  </div>
                  <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" v-model="rev.email" placeholder="Email" :disabled="!canEditReview">
                  </div>
                </div>

                <div v-for="(rRound, rIdx) in rev.reviewer_rounds" :key="rIdx" class="mt-4 pt-4 border-t border-[var(--border2)]">
                  <div class="flex items-center justify-between mb-3">
                    <h5 class="text-[11px] font-bold text-[var(--accent)] uppercase tracking-widest">Review Round {{ rRound.round_number }}</h5>
                    <button v-if="rev.reviewer_rounds.length > 1 && canEditReview" @click="removeReviewerRound(idx, rIdx)" class="text-red-400 hover:text-red-300 text-[10px] font-bold px-2 py-1 border border-red-900 rounded bg-red-900/20">X Round</button>
                  </div>
                  
                  <div class="grid grid-cols-2 gap-4 mb-3">
                    <div class="form-group">
                      <label>Availability / Status</label>
                      <select v-model="rRound.status" :disabled="!canEditReview">
                        <option value="">-</option>
                        <option>Pending Response</option>
                        <option>Waiting for response</option>
                        <option>Sent</option>
                        <option>Available</option>
                        <option>Declined</option>
                        <option>Review Completed</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>Date Sent</label>
                      <input type="date" v-model="rRound.date_sent" :disabled="!canEditReview">
                    </div>
                  </div>


                  <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                      <label>Returned Review Document (Upload)</label>
                      <div class="flex items-center gap-2">
                        <input type="file" @change="handleReviewerUpload($event, idx, rIdx)" class="!w-auto flex-1 text-xs" :disabled="!canEditReview">
                        <span v-if="rRound.file_path" class="text-[var(--accent)] font-bold text-[10px] uppercase whitespace-nowrap"><a :href="baseUrl + '/' + rRound.file_path" target="_blank">View File</a></span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label>Or Review Document Link</label>
                      <input type="url" v-model="rRound.file_link" placeholder="https://drive.google.com/..." :disabled="!canEditReview">
                    </div>
                  </div>
                </div>

                <button v-if="canEditReview" @click="addReviewerRound(idx)" class="btn-outline btn-mini w-full mt-4 !text-[10px]">+ Add Round for this Reviewer</button>
              </div>

              <button v-if="canEditReview" @click="addSuggestedReviewer" class="btn-outline btn-compact w-full border-dashed">+ Add Reviewer</button>
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
// Allow both eic and repo staff to edit reviewers here as it is the Prescreening dashboard
const canEditReview = computed(() => ['eic', 'repo_staff'].includes(currentRole.value));

const canAddManuscript = computed(() => ['eic', 'repo_staff'].includes(currentRole.value));
const canDeleteManuscript = computed(() => ['eic', 'repo_staff'].includes(currentRole.value));

const canSaveForm = computed(() => {
  if (activeTab.value === 'submission') return canEditInitial.value;
  if (activeTab.value === 'repo_processing') return canEditInitial.value;
  if (activeTab.value === 'reviewers') return canEditReview.value;
  return false;
});

// --- DATA LOGIC ---
const manuscripts = ref([]);
const searchQuery = ref('');
const typeFilter = ref('');
const yearFilter = ref('');

const isModalOpen = ref(false);
const editingId = ref(null);
const activeTab = ref('submission');
const selectedFile = ref(null);
const turnitinFile = ref(null);

const reviewRounds = ref([{ round_number: 1, action_taken: '' }]);
const suggestedReviewers = ref([{ name: '', affiliation: '', email: '', reviewer_rounds: [{ round_number: 1, status: '', date_sent: '' }] }]);

const errorMessage = ref('');

const baseUrl = import.meta.env.VITE_API_BASE_URL ? import.meta.env.VITE_API_BASE_URL.replace('/index.php/api', '') : 'http://localhost/mjsir_tracker/backend/public';
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost/mjsir_tracker/backend/public/index.php/api';
const api = axios.create({ baseURL: API_BASE_URL });

const form = ref({});

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

  // Filter out manuscripts that have completed prescreening (Done, Passed, or Rejected)
  result = result.filter(m => {
    const status = (m.prescreen_status || '').toLowerCase();
    return !status.includes('done') && !status.includes('passed') && !status.includes('reject');
  });

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

const handleRoundRevisedUpload = (event, idx) => {
  reviewRounds.value[idx].revisedFileObject = event.target.files[0];
};

const handleRoundActionUpload = (event, idx) => {
  reviewRounds.value[idx].actionFileObject = event.target.files[0];
};


const handleReviewerUpload = (event, revIdx, rIdx) => {
  suggestedReviewers.value[revIdx].reviewer_rounds[rIdx].fileObject = event.target.files[0];
};

const openModal = (ms = null) => {
  activeTab.value = 'submission';
  errorMessage.value = '';
  selectedFile.value = null;
  turnitinFile.value = null;

  if (ms) {
    editingId.value = ms.id;
    form.value = { ...ms };
    if (!form.value.prescreen_status) {
      form.value.prescreen_status = 'Ongoing';
    }
    
    if (ms.review_rounds && ms.review_rounds.length > 0) {
      reviewRounds.value = JSON.parse(JSON.stringify(ms.review_rounds));
    } else {
      reviewRounds.value = [{ round_number: 1, action_taken: '' }];
    }

    if (ms.suggested_reviewers && ms.suggested_reviewers.length > 0) {
      suggestedReviewers.value = JSON.parse(JSON.stringify(ms.suggested_reviewers));
      suggestedReviewers.value.forEach(r => {
        if (!r.reviewer_rounds || r.reviewer_rounds.length === 0) {
          r.reviewer_rounds = [{ round_number: 1, status: '', date_sent: '' }];
        }
      });
    } else {
      suggestedReviewers.value = [{ name: '', affiliation: '', email: '', reviewer_rounds: [{ round_number: 1, status: '', date_sent: '' }] }];
    }
  } else {
    editingId.value = null;
    form.value = { code: '', title: '', turnitin: '', file_path: '', date: '', authors: '', author_email: '', inst: '', link: '', prenotes: '', turnitin_link: '', prescreen_status: 'Ongoing' };
    reviewRounds.value = [{ round_number: 1, action_taken: '' }];
    suggestedReviewers.value = [{ name: '', affiliation: '', email: '', reviewer_rounds: [{ round_number: 1, status: '', date_sent: '' }] }];
  }
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  editingId.value = null;
  errorMessage.value = '';
};

const addRound = () => {
  const nextRoundNum = reviewRounds.value.length > 0 ? Math.max(...reviewRounds.value.map(r => r.round_number || 0)) + 1 : 1;
  reviewRounds.value.push({ round_number: nextRoundNum, action_taken: '' });
};

const removeRound = (idx) => {
  if (reviewRounds.value.length > 1) reviewRounds.value.splice(idx, 1);
};

const addSuggestedReviewer = () => {
  suggestedReviewers.value.push({ name: '', affiliation: '', email: '', reviewer_rounds: [{ round_number: 1, status: '', date_sent: '' }] });
};

const removeSuggestedReviewer = (idx) => {
  suggestedReviewers.value.splice(idx, 1);
};

const addReviewerRound = (revIdx) => {
  const rounds = suggestedReviewers.value[revIdx].reviewer_rounds;
  const nextRoundNum = rounds.length > 0 ? Math.max(...rounds.map(r => r.round_number || 0)) + 1 : 1;
  rounds.push({ round_number: nextRoundNum, status: '', date_sent: '' });
};

const removeReviewerRound = (revIdx, rIdx) => {
  if (suggestedReviewers.value[revIdx].reviewer_rounds.length > 1) {
    suggestedReviewers.value[revIdx].reviewer_rounds.splice(rIdx, 1);
  }
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
  
  const formData = new FormData();
  
  formData.append('review_rounds', JSON.stringify(reviewRounds.value));
  reviewRounds.value.forEach((round, idx) => {
    if (round.revisedFileObject) formData.append(`round_revised_file_${idx}`, round.revisedFileObject);
    if (round.actionFileObject) formData.append(`round_action_file_${idx}`, round.actionFileObject);
  });

  formData.append('suggested_reviewers', JSON.stringify(suggestedReviewers.value));
  suggestedReviewers.value.forEach((rev, revIdx) => {
    if (rev.reviewer_rounds) {
      rev.reviewer_rounds.forEach((round, rIdx) => {
        if (round.fileObject) formData.append(`reviewer_file_${revIdx}_${rIdx}`, round.fileObject);
        if (round.sentFileObject) formData.append(`reviewer_sent_file_${revIdx}_${rIdx}`, round.sentFileObject);
      });
    }
  });

  if (form.value.contributor_type && !form.value.type) {
    form.value.type = form.value.contributor_type;
  }

  for (const key in form.value) {
    if (key !== 'suggested_reviewers' && key !== 'review_rounds' && form.value[key] !== null && form.value[key] !== undefined) {
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
</script>
