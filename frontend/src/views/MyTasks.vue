<template>
  <div>
    <div class="page-header-stack mb-6">
      <h1 class="page-title">My Assigned Tasks</h1>
      <div class="page-subtitle">Focus exclusively on the publications assigned to you for processing.</div>
    </div>

    <!-- FILTER BAR -->
    <div class="filters-bar">
      <div class="filter-group flex-[2]">
        <label>Search My Tasks</label>
        <input type="text" class="filter-input" v-model="searchQuery" placeholder="Title, Code, Authors...">
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
            <th>Current Status</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="filteredManuscripts.length === 0">
            <td colspan="5" class="table-empty">You have no tasks assigned to you right now.</td>
          </tr>
          <tr v-for="ms in filteredManuscripts" :key="ms.id">
            <td>
              <div class="td-title">{{ ms.code }}</div>
              <div class="td-sub">{{ ms.date || '-' }}</div>
            </td>
            <td>
              <div class="td-title mb-1">{{ ms.title || '-' }}</div>
              <div class="td-sub">
                <span v-if="currentRole === 'editor'" class="italic text-[var(--text3)]">[Hidden for Blind Review]</span>
                <span v-else>{{ ms.authors || '-' }}</span>
              </div>
            </td>
            <td>
              <span class="status-badge default">{{ ms.manuscript_type || ms.type || ms.contributor_type || '-' }}</span>
            </td>
            <td>
              <span :class="['status-badge mb-1', getStatusClass(ms.revStatus)]">
                {{ ms.revStatus || 'Pending' }}
              </span>
              <div class="td-sub truncate max-w-[120px]" :title="ms.summary">{{ ms.summary || '-' }}</div>
            </td>
            <td class="table-actions">
              <button @click="openModal(ms)" class="btn-primary btn-compact inline-flex">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" class="mr-1"><path d="M12 20h9M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
                PROCESS TASK
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
          <h2>{{ currentRole === 'repo_staff' ? 'REPO Dashboard: Processing' : 'Peer Review Dashboard' }}</h2>
          <button class="modal-close" @click="closeModal">X</button>
        </div>
        
        <div class="modal-tabs">
          <button @click="activeTab = 'processing'" :class="['modal-tab', { active: activeTab === 'processing' }]">Processing</button>
          <button @click="activeTab = 'reviewers'" :class="['modal-tab', { active: activeTab === 'reviewers' }]">Reviewers Tracking</button>
        </div>

        <div class="modal-body custom-scroll">
          
          <!-- TOP MANUSCRIPT INFO PANEL -->
          <div class="modal-panel" style="margin-bottom: 24px; display: block; overflow: hidden; height: auto;">
            <div class="meta-kicker mb-1" style="color: var(--accent);">{{ form.code }}</div>
            <div class="text-[var(--text)] font-semibold" style="margin-bottom: 12px;">{{ form.title }}</div>
            
            <div v-if="form.eic" style="margin-top: 16px; padding: 16px; border-radius: 6px; background-color: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444; color: #991b1b; display: block;">
              <strong style="color: #b91c1c; display: block; margin-bottom: 6px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">EIC Instructions / Routing Reason:</strong>
              <div style="font-size: 16px; font-weight: 600; line-height: 1.5;">{{ form.eic }}</div>
            </div>
          </div>

          <!-- TAB 1: PROCESSING -->
          <div v-show="activeTab === 'processing'" class="form-grid">
            
            <template v-if="currentRole === 'repo_staff'">
              <div class="form-group">
                <label>Contributor Type</label>
                <select v-model="form.contributor_type">
                  <option value="">-</option>
                  <option>External</option>
                  <option>Internal</option>
                  <option>International</option>
                </select>
              </div>
              <div class="form-group">
                <label>Manuscript Type</label>
                <select v-model="form.manuscript_type">
                  <option value="">-</option>
                  <option>Research Article</option>
                  <option>Research Note</option>
                  <option>Systematic Review</option>
                </select>
              </div>
              <div class="form-group full">
                <label>Turnitin Result (%)</label>
                <input v-model="form.turnitin" placeholder="e.g. 15%">
              </div>
              <div class="form-group full">
                <label>Turnitin Report (Upload File)</label>
                <div class="flex items-center gap-4">
                  <input type="file" @change="handleTurnitinUpload" class="!w-auto flex-1">
                  <span v-if="form.turnitin_file" class="text-[var(--accent)] font-bold text-[11px] uppercase whitespace-nowrap"><a :href="baseUrl + '/' + form.turnitin_file" target="_blank">View File</a></span>
                </div>
              </div>
              <div class="form-group full">
                <label>Or Add Turnitin Google Drive Link</label>
                <input type="url" v-model="form.turnitin_link" placeholder="https://drive.google.com/...">
              </div>
            </template>

            <template v-if="currentRole === 'editor'">
              <div class="p-5 rounded-lg bg-[var(--surface)] text-sm space-y-4 col-span-full" style="margin-bottom: 12px;">
                <h3 class="font-bold text-[11px] uppercase tracking-widest text-[var(--accent)] mb-2 border-b border-[var(--border)] pb-2">Manuscript Overview (Blind Review)</h3>
                <div class="grid grid-cols-2 gap-x-6 gap-y-3">
                  <div><span class="text-[var(--text3)] block text-xs">Contributor Type</span> <span class="font-semibold text-[var(--text2)]">{{ form.contributor_type || '-' }}</span></div>
                  <div><span class="text-[var(--text3)] block text-xs">Manuscript Type</span> <span class="font-semibold text-[var(--text2)]">{{ form.manuscript_type || '-' }}</span></div>
                  <div>
                    <span class="text-[var(--text3)] block text-xs">Turnitin Result</span> 
                    <a v-if="form.turnitin_link" :href="form.turnitin_link" target="_blank" class="text-[var(--accent)] font-semibold hover:underline" title="View Turnitin Report via Link">{{ form.turnitin || 'View Link' }}</a>
                    <a v-else-if="form.turnitin_file" :href="baseUrl + '/' + form.turnitin_file" target="_blank" class="text-[var(--accent)] font-semibold hover:underline" title="View Uploaded Turnitin Report">{{ form.turnitin || 'View File' }}</a>
                    <span v-else class="font-semibold text-[var(--text2)]">{{ form.turnitin || '-' }}</span>
                  </div>
                  <div>
                    <span class="text-[var(--text3)] block text-xs">Manuscript File</span> 
                    <a v-if="form.file_path" :href="baseUrl + '/' + form.file_path" target="_blank" class="text-[var(--accent)] hover:underline font-bold text-xs uppercase">View Document</a>
                    <span v-else class="text-[var(--text3)] italic">No file attached</span>
                  </div>
                </div>
                <div v-if="form.prenotes" class="pt-3 border-t border-[var(--border)] mt-3">
                  <span class="text-[var(--text3)] block text-xs mb-1">Initial Submission Notes</span>
                  <p class="text-[var(--text)] bg-[var(--surface2)] p-3 rounded text-[13px]">{{ form.prenotes }}</p>
                </div>
              </div>
            </template>

            <!-- DYNAMIC REVIEW ROUNDS -->
            <div class="form-group full mt-6 mb-2">
              <h3 class="font-bold text-[14px] text-[var(--text)] mb-4 border-b border-[var(--border)] pb-2">Review Rounds Tracking</h3>
              
              <div v-for="(round, idx) in reviewRounds" :key="idx" class="modal-panel mb-4 p-4 border border-[var(--border)] rounded-md bg-[var(--surface2)]">
                <div class="flex items-center justify-between mb-4">
                  <h4 class="text-[12px] font-bold text-[var(--accent)] uppercase tracking-widest">Review Round {{ round.round_number }}</h4>
                  <button v-if="reviewRounds.length > 1" @click="removeRound(idx)" class="text-red-400 hover:text-red-300 text-xs font-bold px-2 py-1 border border-red-900 rounded bg-red-900/20">X Remove</button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                  <div class="form-group">
                    <label>Revised Document (Upload)</label>
                    <div class="flex items-center gap-2">
                      <input type="file" @change="handleRoundRevisedUpload($event, idx)" class="!w-auto flex-1 text-xs">
                      <span v-if="round.revised_file_path" class="text-[var(--accent)] font-bold text-[10px] uppercase whitespace-nowrap"><a :href="baseUrl + '/' + round.revised_file_path" target="_blank">View File</a></span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label>Or Revised Document Link</label>
                    <input type="url" v-model="round.revised_link" placeholder="https://drive.google.com/...">
                  </div>

                  <div class="form-group">
                    <label>Action Taken Document (Upload)</label>
                    <div class="flex items-center gap-2">
                      <input type="file" @change="handleRoundActionUpload($event, idx)" class="!w-auto flex-1 text-xs">
                      <span v-if="round.action_taken_file_path" class="text-[var(--accent)] font-bold text-[10px] uppercase whitespace-nowrap"><a :href="baseUrl + '/' + round.action_taken_file_path" target="_blank">View File</a></span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label>Or Action Taken Link</label>
                    <input type="url" v-model="round.action_taken_link" placeholder="https://drive.google.com/...">
                  </div>

                  <div class="form-group col-span-2">
                    <label>Action Taken Status</label>
                    <select v-model="round.action_taken">
                      <option value="">-</option>
                      <option>Sent rejection letter</option>
                      <option>Sent for review</option>
                      <option>Sent for reviewers</option>
                      <option>Sent for author revision</option>
                    </select>
                  </div>
                </div>
              </div>
              
              <button v-if="currentRole !== 'editor'" @click="addRound" class="btn-outline btn-compact w-full border-dashed">+ Add Review Round</button>
            </div>

            <div class="form-group full mt-4 pt-4 border-t border-[var(--border2)]">
              <label>Comments / Suggestions</label>
              <textarea v-model="form.editor_comments" rows="3" placeholder="Enter comments or suggestions..."></textarea>
            </div>
            
            <div class="form-group">
              <label>Overall Remarks</label>
              <select v-model="form.editor_remarks">
                <option value="">-</option>
                <option>Return to author for review</option>
                <option>Go for review</option>
                <option>For EIC before review</option>
                <option>Reject</option>
              </select>
            </div>

            <div class="form-group">
              <label>Prescreening Status</label>
              <select v-model="form.prescreen_status" :disabled="currentRole === 'editor'">
                <option value="">-</option>
                <option>Done</option>
                <option>Ongoing</option>
              </select>
            </div>

          </div>


          <!-- TAB 2: REVIEWERS TRACKING -->
          <div v-show="activeTab === 'reviewers'" class="form-grid">
            <div class="form-group full">
              <h3 class="font-bold text-[14px] text-[var(--text)] mb-4 border-b border-[var(--border)] pb-2">Reviewers Tracking</h3>
              
              <div v-for="(rev, idx) in suggestedReviewers" :key="idx" class="modal-panel mb-4 p-4 border border-[var(--border)] rounded-md bg-[var(--surface2)]">
                <div class="flex items-center justify-between mb-4">
                  <h4 class="text-[12px] font-bold text-[var(--accent)] uppercase tracking-widest">Reviewer {{ idx + 1 }}</h4>
                  <button @click="removeSuggestedReviewer(idx)" class="text-red-400 hover:text-red-300 text-xs font-bold px-2 py-1 border border-red-900 rounded bg-red-900/20">X Remove</button>
                </div>
                
                <div class="grid grid-cols-3 gap-4 mb-3">
                  <div class="form-group">
                    <label>Reviewer Name</label>
                    <input v-model="rev.name" placeholder="Name">
                  </div>
                  <div class="form-group">
                    <label>Affiliation</label>
                    <input v-model="rev.affiliation" placeholder="Affiliation">
                  </div>
                  <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" v-model="rev.email" placeholder="Email">
                  </div>
                </div>

                <div v-if="currentRole !== 'editor'" v-for="(rRound, rIdx) in rev.reviewer_rounds" :key="rIdx" class="mt-4 pt-4 border-t border-[var(--border2)]">
                  <div class="flex items-center justify-between mb-3">
                    <h5 class="text-[11px] font-bold text-[var(--accent)] uppercase tracking-widest">Review Round {{ rRound.round_number }}</h5>
                    <button v-if="rev.reviewer_rounds.length > 1" @click="removeReviewerRound(idx, rIdx)" class="text-red-400 hover:text-red-300 text-[10px] font-bold px-2 py-1 border border-red-900 rounded bg-red-900/20">X Round</button>
                  </div>
                  
                  <div class="grid grid-cols-2 gap-4 mb-3">
                    <div class="form-group">
                      <label>Availability / Status</label>
                      <select v-model="rRound.status">
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
                      <input type="date" v-model="rRound.date_sent">
                    </div>
                  </div>


                  <div class="grid grid-cols-2 gap-4">
                    <div class="form-group">
                      <label>Returned Review Document (Upload)</label>
                      <div class="flex items-center gap-2">
                        <input type="file" @change="handleReviewerUpload($event, idx, rIdx)" class="!w-auto flex-1 text-xs">
                        <span v-if="rRound.file_path" class="text-[var(--accent)] font-bold text-[10px] uppercase whitespace-nowrap"><a :href="baseUrl + '/' + rRound.file_path" target="_blank">View File</a></span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label>Or Review Document Link</label>
                      <input type="url" v-model="rRound.file_link" placeholder="https://drive.google.com/...">
                    </div>
                  </div>
                </div>

                <button v-if="currentRole !== 'editor'" @click="addReviewerRound(idx)" class="btn-outline btn-mini w-full mt-4 !text-[10px]">+ Add Round for this Reviewer</button>
              </div>

              <button @click="addSuggestedReviewer" class="btn-outline btn-compact w-full border-dashed">+ Add Reviewer</button>
            </div>
          </div>

        </div>
        <div class="modal-footer">
          <button class="btn-outline" @click="closeModal">Cancel</button>
          <button class="btn-primary" @click="saveTask">Save Updates</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const currentRole = ref(localStorage.getItem('user_role') || '');
const currentUserName = ref(localStorage.getItem('user_name') || '');

const manuscripts = ref([]);
const searchQuery = ref('');

const isModalOpen = ref(false);
const editingId = ref(null);
const activeTab = ref('processing');

const turnitinFile = ref(null);
const reviewRounds = ref([{ round_number: 1, action_taken: '' }]);
const suggestedReviewers = ref([{ name: '', affiliation: '', email: '', reviewer_rounds: [{ round_number: 1, status: '', date_sent: '' }] }]);

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
  let result = manuscripts.value.filter(m => {
    if (currentRole.value === 'repo_staff') return m.repo === currentUserName.value;
    if (currentRole.value === 'editor') return m.editor === currentUserName.value;
    return false;
  });

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(m => 
      (m.code || '').toLowerCase().includes(q) || 
      (m.title || '').toLowerCase().includes(q)
    );
  }
  return result;
});

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

const openModal = (ms) => {
  editingId.value = ms.id;
  activeTab.value = 'processing';
  turnitinFile.value = null;
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

  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  editingId.value = null;
};

const addRound = () => {
  const nextRoundNum = reviewRounds.value.length > 0 
    ? Math.max(...reviewRounds.value.map(r => r.round_number || 0)) + 1 
    : 1;
  reviewRounds.value.push({ round_number: nextRoundNum, action_taken: '' });
};

const removeRound = (idx) => {
  if (reviewRounds.value.length > 1) {
    reviewRounds.value.splice(idx, 1);
  }
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

const saveTask = async () => {
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
  
  for (const key in form.value) {
    if (key !== 'suggested_reviewers' && key !== 'review_rounds' && form.value[key] !== null && form.value[key] !== undefined) {
      formData.append(key, form.value[key]);
    }
  }

  if (turnitinFile.value) {
    formData.append('turnitin_file', turnitinFile.value);
  }
  
  try {
    const config = { headers: { 'Content-Type': 'multipart/form-data' } };
    await api.post(`/manuscripts/${editingId.value}`, formData, config);
    
    try {
      const roleName = currentRole.value === 'editor' ? 'Associate Editor' : 'REPO Staff';
      let message = `${roleName} updated processing for ${form.value.code}`;
      if (form.value.action_taken === 'Returned to Author') message = `${roleName} returned ${form.value.code} to author`;
      else if (form.value.prescreen_status === 'For EIC before review') message = `${roleName} routed ${form.value.code} to EIC`;
      
      await api.post('/notifications', { target_user: 'eic', message });
      if (currentRole.value !== 'repo_staff') {
         await api.post('/notifications', { target_user: 'repo_staff', message });
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
