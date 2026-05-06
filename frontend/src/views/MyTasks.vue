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

    <!-- MODAL (Tailored explicitly to the role) -->
    <div class="modal-overlay" :class="{ open: isModalOpen }">
      <div class="modal" :style="{ width: currentRole === 'repo_staff' ? '800px' : '900px' }">
        <div class="modal-header">
          <h2>{{ currentRole === 'repo_staff' ? 'REPO Dashboard: Processing' : 'Peer Review Dashboard' }}</h2>
          <button class="modal-close" @click="closeModal">X</button>
        </div>
        
        <div class="modal-body custom-scroll">
          
          <!-- TOP MANUSCRIPT INFO PANEL -->
          <div class="modal-panel" style="margin-bottom: 30px; display: block; overflow: hidden; height: auto;">
            <div class="meta-kicker mb-1" style="color: var(--accent);">{{ form.code }}</div>
            <div class="text-[var(--text)] font-semibold" style="margin-bottom: 12px;">{{ form.title }}</div>
            
            <div v-if="form.eic" style="margin-top: 16px; padding: 16px; border-radius: 6px; background-color: rgba(239, 68, 68, 0.1); border-left: 4px solid #ef4444; color: #991b1b; display: block;">
              <strong style="color: #b91c1c; display: block; margin-bottom: 6px; font-size: 14px; text-transform: uppercase; letter-spacing: 0.5px;">EIC Instructions / Routing Reason:</strong>
              <div style="font-size: 16px; font-weight: 600; line-height: 1.5;">{{ form.eic }}</div>
            </div>
          </div>

          <!-- REPO STAFF VIEW -->
          <div v-if="currentRole === 'repo_staff'" class="form-grid">
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
            
            <div class="form-group full" v-if="currentRole === 'repo_staff'">
              <label>Turnitin Report (Upload File)</label>
              <div class="flex items-center gap-4">
                <input type="file" @change="handleTurnitinUpload" class="!w-auto flex-1">
                <span v-if="form.turnitin_file" class="text-[var(--accent)] font-bold text-[11px] uppercase whitespace-nowrap"><a :href="baseUrl + '/' + form.turnitin_file" target="_blank">View File</a></span>
              </div>
            </div>
            
            <div class="form-group full" v-if="currentRole === 'repo_staff'">
              <label>Or Add Turnitin Google Drive Link</label>
              <input type="url" v-model="form.turnitin_link" placeholder="https://drive.google.com/...">
            </div>
            



            <!-- STATUS AND REMARKS FOR REPO STAFF -->
            <div class="form-group full mt-2 pt-4 border-t border-[var(--border2)]">
              <label>Comments / Suggestions</label>
              <textarea v-model="form.editor_comments" rows="3" placeholder="Enter comments or suggestions..."></textarea>
            </div>

            <!-- DYNAMIC SUGGESTED REVIEWERS FOR REPO STAFF -->
            <div class="form-group full modal-panel mt-4">
              <div class="flex items-center justify-between mb-4">
                <h4 class="text-[11px] font-bold text-[var(--accent)] uppercase tracking-widest">Suggested Reviewers</h4>
                <button @click="addSuggestedReviewer" class="btn-outline btn-mini">+ Add Reviewer</button>
              </div>
              
              <div v-for="(rev, idx) in suggestedReviewers" :key="idx" class="grid grid-cols-10 gap-4 mb-2 items-end">
                <div class="form-group col-span-3">
                  <label v-if="idx === 0">Reviewer Name</label>
                  <input v-model="rev.name" placeholder="Name">
                </div>
                <div class="form-group col-span-3">
                  <label v-if="idx === 0">Affiliation</label>
                  <input v-model="rev.affil" placeholder="Affiliation">
                </div>
                <div class="form-group col-span-3">
                  <label v-if="idx === 0">Email Address</label>
                  <input type="email" v-model="rev.email" placeholder="Email">
                </div>
                <div class="form-group col-span-1 text-right pb-1">
                  <button v-if="suggestedReviewers.length > 1" @click="removeSuggestedReviewer(idx)" class="text-red-400 hover:text-red-300 text-xs font-bold px-2 py-2 border border-red-900 rounded bg-red-900/20">X</button>
                </div>
              </div>
            </div>
            
            <div class="form-group">
              <label>Review Round</label>
              <select v-model="form.review_round">
                <option value="1">1st Review</option>
                <option value="2">2nd Review</option>
                <option value="3">3rd Review</option>
                <option value="4">4th Review</option>
                <option value="5">5th Review</option>
              </select>
            </div>
            
            <!-- 1ST REVIEW UPLOADS -->
            <!-- 1ST REVIEW UPLOADS -->
            <template v-if="form.review_round == 1">
              <div class="form-group" v-if="currentRole === 'repo_staff'">
                <label>Upload Revised Document (1st Review)</label>
                <div class="flex items-center gap-4">
                  <input type="file" @change="handleRevisedUpload" class="!w-auto flex-1">
                  <span v-if="form.revised_file" class="text-[var(--accent)] font-bold text-[11px] uppercase whitespace-nowrap"><a :href="baseUrl + '/' + form.revised_file" target="_blank">View File</a></span>
                </div>
              </div>
  
              <div class="form-group" v-if="currentRole === 'repo_staff'">
                <label>Or Add Revised Document Link (1st Review)</label>
                <input type="url" v-model="form.revised_link" placeholder="https://drive.google.com/...">
              </div>
            </template>

            <!-- 2ND REVIEW UPLOADS -->
            <template v-if="form.review_round == 2">
              <div class="form-group" v-if="currentRole === 'repo_staff'">
                <label>Upload Revised Document (2nd Review)</label>
                <div class="flex items-center gap-4">
                  <input type="file" @change="handleRevisedUpload2" class="!w-auto flex-1">
                  <span v-if="form.revised_file_2" class="text-[var(--accent)] font-bold text-[11px] uppercase whitespace-nowrap"><a :href="baseUrl + '/' + form.revised_file_2" target="_blank">View 2nd File</a></span>
                </div>
              </div>
  
              <div class="form-group" v-if="currentRole === 'repo_staff'">
                <label>Or Add Revised Document Link (2nd Review)</label>
                <input type="url" v-model="form.revised_link_2" placeholder="https://drive.google.com/...">
              </div>
            </template>

            <!-- 3RD REVIEW UPLOADS -->
            <template v-if="form.review_round >= 3">
              <div class="form-group" v-if="currentRole === 'repo_staff'">
                <label>Upload Revised Document (3rd Review)</label>
                <div class="flex items-center gap-4">
                  <input type="file" @change="handleRevisedUpload3" class="!w-auto flex-1">
                  <span v-if="form.revised_file_3" class="text-[var(--accent)] font-bold text-[11px] uppercase whitespace-nowrap"><a :href="baseUrl + '/' + form.revised_file_3" target="_blank">View 3rd File</a></span>
                </div>
              </div>
  
              <div class="form-group" v-if="currentRole === 'repo_staff'">
                <label>Or Add Revised Document Link (3rd Review)</label>
                <input type="url" v-model="form.revised_link_3" placeholder="https://drive.google.com/...">
              </div>
            </template>
            
            <div class="form-group mt-2">
              <label>Action Taken Document</label>
              <div class="flex items-center gap-2">
                <input type="file" @change="handleActionTakenUpload" class="!w-auto flex-1 text-xs">
                <span v-if="form.action_taken_file" class="text-[var(--accent)] font-bold text-[11px] uppercase whitespace-nowrap"><a :href="baseUrl + '/' + form.action_taken_file" target="_blank">View File</a></span>
              </div>
            </div>

            <div class="form-group mt-2">
              <label>Or Action Taken Link</label>
              <input type="url" v-model="form.action_taken_link" placeholder="https://drive.google.com/...">
            </div>

            <div class="form-group full mb-4">
              <label>Action Taken</label>
              <select v-model="form.action_taken">
                <option value="">-</option>
                <option>Sent rejection letter</option>
                <option>Sent for review</option>
                <option>Sent for reviewers</option>
                <option>Sent for author revision</option>
              </select>
            </div>

            <div class="form-group">
              <label>Remarks</label>
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
              <select v-model="form.prescreen_status">
                <option value="">-</option>
                <option>Done</option>
                <option>Ongoing</option>
              </select>
            </div>



          </div>

          <!-- EDITOR VIEW -->
          <div v-if="currentRole === 'editor'" class="form-grid" style="margin-top: 20px;">
            
            <!-- READ-ONLY MANUSCRIPT SUMMARY FOR EDITOR -->
            <div class="p-5 rounded-lg bg-[var(--surface)] text-sm space-y-4 col-span-full" style="margin-bottom: 24px;">
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

            <!-- ADDED FILE UPLOAD FOR EDITOR -->
            <template v-if="form.review_round == 1">
              <div class="form-group">
                <label>Upload Reviewed / Marked-up Document (1st Review)</label>
                <div class="flex items-center gap-4">
                  <input type="file" @change="handleRevisedUpload" class="!w-auto flex-1">
                  <span v-if="form.revised_file" class="text-[var(--accent)] font-bold text-[11px] uppercase whitespace-nowrap"><a :href="baseUrl + '/' + form.revised_file" target="_blank">Current Revised File</a></span>
                </div>
              </div>
  
              <div class="form-group">
                <label>Or Add Revised Document Link (1st Review)</label>
                <input type="url" v-model="form.revised_link" placeholder="https://drive.google.com/...">
              </div>
            </template>

            <template v-if="form.review_round == 2">
              <div class="form-group">
                <label>Upload Reviewed / Marked-up Document (2nd Review)</label>
                <div class="flex items-center gap-4">
                  <input type="file" @change="handleRevisedUpload2" class="!w-auto flex-1">
                  <span v-if="form.revised_file_2" class="text-[var(--accent)] font-bold text-[11px] uppercase whitespace-nowrap"><a :href="baseUrl + '/' + form.revised_file_2" target="_blank">Current 2nd Revised File</a></span>
                </div>
              </div>
  
              <div class="form-group">
                <label>Or Add Revised Document Link (2nd Review)</label>
                <input type="url" v-model="form.revised_link_2" placeholder="https://drive.google.com/...">
              </div>
            </template>

            <template v-if="form.review_round >= 3">
              <div class="form-group">
                <label>Upload Reviewed / Marked-up Document (3rd Review)</label>
                <div class="flex items-center gap-4">
                  <input type="file" @change="handleRevisedUpload3" class="!w-auto flex-1">
                  <span v-if="form.revised_file_3" class="text-[var(--accent)] font-bold text-[11px] uppercase whitespace-nowrap"><a :href="baseUrl + '/' + form.revised_file_3" target="_blank">Current 3rd Revised File</a></span>
                </div>
              </div>
  
              <div class="form-group">
                <label>Or Add Revised Document Link (3rd Review)</label>
                <input type="url" v-model="form.revised_link_3" placeholder="https://drive.google.com/...">
              </div>
            </template>

            <div class="form-group full mb-4 mt-2">
              <label>Action Taken</label>
              <select v-model="form.action_taken">
                <option value="">-</option>
                <option>Sent rejection letter</option>
                <option>Sent for review</option>
                <option>Sent for reviewers</option>
                <option>Sent for author revision</option>
              </select>
            </div>



            <div class="form-group full modal-panel mb-2">
              <div class="flex items-center justify-between mb-4">
                <h4 class="text-[11px] font-bold text-[var(--accent)] uppercase tracking-widest">Suggested Reviewers</h4>
                <button @click="addSuggestedReviewer" class="btn-outline btn-mini">+ Add Reviewer</button>
              </div>
              
              <div v-for="(rev, idx) in suggestedReviewers" :key="idx" class="grid grid-cols-10 gap-4 mb-2 items-end">
                <div class="form-group col-span-3">
                  <label v-if="idx === 0">Reviewer Name</label>
                  <input v-model="rev.name" placeholder="Name">
                </div>
                <div class="form-group col-span-3">
                  <label v-if="idx === 0">Affiliation</label>
                  <input v-model="rev.affil" placeholder="Affiliation">
                </div>
                <div class="form-group col-span-3">
                  <label v-if="idx === 0">Email Address</label>
                  <input type="email" v-model="rev.email" placeholder="Email">
                </div>
                <div class="form-group col-span-1 text-right pb-1">
                  <button v-if="suggestedReviewers.length > 1" @click="removeSuggestedReviewer(idx)" class="text-red-400 hover:text-red-300 text-xs font-bold px-2 py-2 border border-red-900 rounded bg-red-900/20">X</button>
                </div>
              </div>
            </div>

            <div class="form-group full mt-2">
              <label>Comments / Suggestions</label>
              <textarea v-model="form.editor_comments" rows="3" placeholder="Enter comments or suggestions..."></textarea>
            </div>
            
            <div class="form-group">
              <label>Remarks</label>
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
              <select v-model="form.prescreen_status">
                <option value="">-</option>
                <option>Done</option>
                <option>Ongoing</option>
              </select>
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
const selectedFile = ref(null);
const turnitinFile = ref(null);
const revisedFile = ref(null);
const revisedFile2 = ref(null);
const revisedFile3 = ref(null);
const actionTakenFile = ref(null);

const suggestedReviewers = ref([{ name: '', affil: '', email: '' }]);

const baseUrl = import.meta.env.VITE_API_BASE_URL ? import.meta.env.VITE_API_BASE_URL.replace('/index.php/api', '') : 'http://localhost/mjsir_tracker/backend/public';
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost/mjsir_tracker/backend/public/index.php/api';
const api = axios.create({ baseURL: API_BASE_URL });

const form = ref({
  editor_comments: '', editor_remarks: '', prescreen_status: '', action_taken: ''
});

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
  // CRITICAL FILTER: Only show items explicitly assigned to the currently logged in user
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

const handleFileUpload = (event) => {
  selectedFile.value = event.target.files[0];
};

const handleTurnitinUpload = (event) => {
  turnitinFile.value = event.target.files[0];
};

const handleRevisedUpload = (event) => {
  revisedFile.value = event.target.files[0];
};

const handleRevisedUpload2 = (event) => {
  revisedFile2.value = event.target.files[0];
};

const handleRevisedUpload3 = (event) => {
  revisedFile3.value = event.target.files[0];
};

const handleActionTakenUpload = (event) => {
  actionTakenFile.value = event.target.files[0];
};

const openModal = (ms) => {
  editingId.value = ms.id;
  selectedFile.value = null;
  turnitinFile.value = null;
  revisedFile.value = null;
  revisedFile2.value = null;
  revisedFile3.value = null;
  actionTakenFile.value = null;
  form.value = { review_round: 1, ...ms };
  if (ms.suggested_reviewers) {
    try {
      suggestedReviewers.value = JSON.parse(ms.suggested_reviewers);
    } catch(e) {
      suggestedReviewers.value = [{ name: '', affil: '', email: '' }];
    }
  } else {
    suggestedReviewers.value = [{ name: '', affil: '', email: '' }];
  }
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  editingId.value = null;
};

const saveTask = async () => {
  const round = form.value.review_round || 1;
  
  if (round == 1) {
    if (!revisedFile.value && !form.value.revised_link && !form.value.revised_file) {
      alert("Please upload a Revised Document (1st Review) or provide a link before saving.");
      return;
    }
  } else if (round == 2) {
    if (!revisedFile2.value && !form.value.revised_link_2 && !form.value.revised_file_2) {
      alert("Please upload a Revised Document (2nd Review) or provide a link before saving.");
      return;
    }
  } else if (round >= 3) {
    if (!revisedFile3.value && !form.value.revised_link_3 && !form.value.revised_file_3) {
      alert("Please upload a Revised Document (3rd Review) or provide a link before saving.");
      return;
    }
  }

  const formData = new FormData();
  
  form.value.suggested_reviewers = JSON.stringify(suggestedReviewers.value);
  
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
  
  if (revisedFile.value) {
    formData.append('revised_file', revisedFile.value);
  }

  if (revisedFile2.value) {
    formData.append('revised_file_2', revisedFile2.value);
  }

  if (revisedFile3.value) {
    formData.append('revised_file_3', revisedFile3.value);
  }

  if (actionTakenFile.value) {
    formData.append('action_taken_file', actionTakenFile.value);
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

const addSuggestedReviewer = () => {
  suggestedReviewers.value.push({ name: '', affil: '', email: '' });
};

const removeSuggestedReviewer = (index) => {
  if (suggestedReviewers.value.length > 1) {
    suggestedReviewers.value.splice(index, 1);
  }
};
</script>



