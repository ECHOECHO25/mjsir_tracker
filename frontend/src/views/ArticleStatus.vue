<template>
  <div>
    <div class="page-header-stack mb-6">
      <h1 class="page-title">Article Status Tracker</h1>
      <div class="page-subtitle">Track final decisions and manage accepted publications.</div>
    </div>

    <!-- FILTER BAR -->
    <div class="filters-bar">
      <div class="filter-group">
        <label>View Selection</label>
        <select class="filter-input" v-model="viewFilter">
          <option value="all">All Tables</option>
          <option value="pending">Pending Decisions</option>
          <option value="accepted">Accepted Only</option>
          <option value="rejected">Rejected Only</option>
        </select>
      </div>
      <div class="filter-group flex-[2]">
        <label>Search Publications</label>
        <input type="text" class="filter-input" v-model="searchQuery" placeholder="Title, Code, Authors...">
      </div>
    </div>

    <!-- PENDING/ACTIVE TABLE -->
    <div class="mb-8" v-show="viewFilter === 'all' || viewFilter === 'pending'">
      <h2 class="section-title" style="color: var(--accent);">Active & Pending Decisions</h2>
      <div class="table-container">
        <table class="tbl">
          <thead>
            <tr>
              <th>ID / Code</th>
              <th style="min-width: 250px;">Title</th>
              <th>Current Decision Status</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="pendingManuscripts.length === 0">
              <td colspan="4" class="table-empty">No pending decisions.</td>
            </tr>
            <tr v-for="ms in pendingManuscripts" :key="ms.id">
              <td>
                <div class="td-title">{{ ms.code }}</div>
                <div class="td-sub">{{ ms.date || '-' }}</div>
              </td>
              <td>
                <div class="td-title mb-1">{{ ms.title || '-' }}</div>
              </td>
              <td>
                <span :class="['status-badge mb-1', getDecisionClass(ms.final_decision)]">
                  {{ ms.final_decision || 'No Decision Yet' }}
                </span>
              </td>
              <td class="table-actions">
                <button @click="openModal(ms)" class="btn-primary btn-compact inline-flex">
                  UPDATE STATUS
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- ACCEPTED TABLE -->
    <div class="mb-8" v-show="viewFilter === 'all' || viewFilter === 'accepted'">
      <h2 class="section-title" style="color: var(--accent);">Accepted for Publication</h2>
      <div class="table-container">
        <table class="tbl">
          <thead>
            <tr>
              <th>ID / Code</th>
              <th style="min-width: 250px;">Title</th>
              <th>Date Accepted</th>
              <th>Date Last Revised</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="acceptedManuscripts.length === 0">
              <td colspan="5" class="table-empty">No accepted publications yet.</td>
            </tr>
            <tr v-for="ms in acceptedManuscripts" :key="ms.id">
              <td>
                <div class="td-title">{{ ms.code }}</div>
              </td>
              <td>
                <div class="td-title mb-1">{{ ms.title || '-' }}</div>
              </td>
              <td>
                <span class="font-bold text-[var(--text2)]">{{ ms.date_accepted || '-' }}</span>
              </td>
              <td>
                <span class="font-bold text-[var(--text2)]">{{ ms.date_revised || '-' }}</span>
              </td>
              <td class="table-actions">
                <button @click="openModal(ms)" class="btn-outline btn-compact inline-flex">
                  EDIT DATES
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- REJECTED TABLE -->
    <div class="mb-8" v-show="viewFilter === 'all' || viewFilter === 'rejected'">
      <h2 class="section-title text-red-500">Rejected Publications</h2>
      <div class="table-container">
        <table class="tbl">
          <thead>
            <tr>
              <th>ID / Code</th>
              <th style="min-width: 250px;">Title</th>
              <th>Final Decision</th>
              <th class="text-right">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr v-if="rejectedManuscripts.length === 0">
              <td colspan="4" class="table-empty">No rejected publications.</td>
            </tr>
            <tr v-for="ms in rejectedManuscripts" :key="ms.id">
              <td>
                <div class="td-title">{{ ms.code }}</div>
              </td>
              <td>
                <div class="td-title mb-1">{{ ms.title || '-' }}</div>
              </td>
              <td>
                <span class="status-badge rejected mb-1">
                  {{ ms.final_decision }}
                </span>
              </td>
              <td class="table-actions">
                <button @click="openModal(ms)" class="btn-outline btn-compact inline-flex hover:bg-red-500/10 hover:border-red-500 hover:text-red-500">
                  UPDATE STATUS
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- MODAL -->
    <div class="modal-overlay" :class="{ open: isModalOpen }">
      <div class="modal" style="width: min(96vw, 700px);">
        <div class="modal-header">
          <h2>Review & Update Decision</h2>
          <button class="modal-close" @click="closeModal">X</button>
        </div>
        
        <div class="modal-body custom-scroll bg-[var(--surface2)]">
          <div class="modal-panel mb-4" style="background: var(--bg);">
            <div class="meta-kicker mb-1">{{ form.code }}</div>
            <div class="text-[var(--text)] font-semibold">{{ form.title }}</div>
          </div>

          <!-- READ-ONLY MANUSCRIPT SUMMARY (FORM STYLE) -->
          <div v-if="fullMs" class="mb-6">
            <h3 class="font-bold text-[11px] uppercase tracking-widest text-[var(--accent)] mb-3 border-b border-[var(--border)] pb-2">Full Manuscript Overview</h3>
            
            <div class="form-grid">
              <div class="form-group">
                <label>Author(s)</label>
                <input :value="fullMs.authors || '-'" disabled>
              </div>
              <div class="form-group">
                <label>Contributor Type</label>
                <input :value="fullMs.contributor_type || '-'" disabled>
              </div>
              <div class="form-group full">
                <label>Affiliation</label>
                <input :value="fullMs.inst || '-'" disabled>
              </div>
              <div class="form-group">
                <label>Review Round</label>
                <input :value="fullMs.review_round ? fullMs.review_round + (fullMs.review_round == 1 ? 'st' : fullMs.review_round == 2 ? 'nd' : fullMs.review_round == 3 ? 'rd' : 'th') + ' Review' : '1st Review'" disabled class="font-bold text-[var(--accent)]">
              </div>
              <div class="form-group">
                <label>Assigned Editor</label>
                <input :value="fullMs.editor || 'Unassigned'" disabled>
              </div>
              <div class="form-group">
                <label>Turnitin Result (%)</label>
                <a v-if="fullMs.turnitin_link" :href="fullMs.turnitin_link" target="_blank" class="flex items-center h-[38px] px-3 bg-red-50 border border-red-200 rounded text-red-600 font-bold hover:bg-red-100 transition-colors">
                  {{ fullMs.turnitin || 'View Report' }} <span class="ml-auto text-lg">↗</span>
                </a>
                <a v-else-if="fullMs.turnitin_file" :href="baseUrl + '/' + fullMs.turnitin_file" target="_blank" class="flex items-center h-[38px] px-3 bg-red-50 border border-red-200 rounded text-red-600 font-bold hover:bg-red-100 transition-colors">
                  {{ fullMs.turnitin || 'View File' }} <span class="ml-auto text-lg">↗</span>
                </a>
                <input v-else :value="fullMs.turnitin || '-'" disabled>
              </div>
              
              <div class="form-group">
                <label>Original Manuscript</label>
                <a v-if="fullMs.link" :href="fullMs.link" target="_blank" class="flex items-center h-[38px] px-3 bg-[var(--accent)]/10 border border-[var(--accent)]/30 rounded text-[var(--accent)] font-bold hover:bg-[var(--accent)]/20 transition-colors truncate">
                  View External Link <span class="ml-auto text-lg">↗</span>
                </a>
                <a v-else-if="fullMs.file_path" :href="baseUrl + '/' + fullMs.file_path" target="_blank" class="flex items-center h-[38px] px-3 bg-[var(--accent)]/10 border border-[var(--accent)]/30 rounded text-[var(--accent)] font-bold hover:bg-[var(--accent)]/20 transition-colors truncate">
                  View Uploaded File <span class="ml-auto text-lg">↗</span>
                </a>
                <div v-else class="flex items-center h-[38px] px-3 bg-[var(--surface2)] border border-[var(--border)] rounded text-[13px] opacity-80 italic text-[var(--text3)]">
                  No file
                </div>
              </div>
              
              <!-- DYNAMIC REVIEW ROUNDS -->
              <div class="form-group full" v-if="fullMs.review_rounds && fullMs.review_rounds.length > 0">
                <h4 class="text-[10px] uppercase font-bold text-[var(--accent)] mb-2">Processing Rounds</h4>
                <div v-for="round in fullMs.review_rounds" :key="'round-'+round.id" class="p-3 bg-[var(--surface2)] border border-[var(--border2)] rounded mb-2">
                  <div class="font-bold text-[11px] mb-2">ROUND {{ round.round_number }}</div>
                  
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <label class="text-[9px] uppercase text-[var(--text3)]">Revised Document</label>
                      <a v-if="round.revised_link" :href="round.revised_link" target="_blank" class="block text-[var(--accent)] font-bold text-xs truncate">View Link ↗</a>
                      <a v-else-if="round.revised_file_path" :href="baseUrl + '/' + round.revised_file_path" target="_blank" class="block text-[var(--accent)] font-bold text-xs truncate">View File ↗</a>
                      <span v-else class="text-xs italic text-[var(--text3)]">No file</span>
                    </div>
                    <div>
                      <label class="text-[9px] uppercase text-[var(--text3)]">Action Taken</label>
                      <div class="text-xs font-semibold mb-1">{{ round.action_taken || 'Pending' }}</div>
                      <a v-if="round.action_taken_link" :href="round.action_taken_link" target="_blank" class="block text-[var(--accent)] font-bold text-xs truncate">View Action Link ↗</a>
                      <a v-else-if="round.action_taken_file_path" :href="baseUrl + '/' + round.action_taken_file_path" target="_blank" class="block text-[var(--accent)] font-bold text-xs truncate">View Action File ↗</a>
                    </div>
                  </div>
                </div>
              </div>

              <!-- DYNAMIC REVIEWERS -->
              <div class="form-group full" v-if="fullMs.suggested_reviewers && fullMs.suggested_reviewers.length > 0">
                <h4 class="text-[10px] uppercase font-bold text-[var(--accent)] mb-2 mt-2">Reviewers Tracking</h4>
                <div v-for="rev in fullMs.suggested_reviewers" :key="'rev-'+rev.id" class="p-3 bg-[var(--surface2)] border border-[var(--border2)] rounded mb-2">
                  <div class="font-bold text-[11px] text-[var(--text)] mb-2">{{ rev.name || 'Unnamed Reviewer' }} <span class="text-[var(--text3)] font-normal text-[10px]">({{ rev.email || 'No email' }})</span></div>
                  
                  <div v-for="rRound in rev.reviewer_rounds" :key="'rround-'+rRound.id" class="mt-2 pt-2 border-t border-[var(--border)] border-dashed">
                    <div class="text-[10px] font-bold text-[var(--accent)] mb-1">Round {{ rRound.round_number }}</div>
                    <div class="grid grid-cols-2 gap-2 text-xs">
                      <div><span class="text-[var(--text3)]">Status:</span> {{ rRound.status || 'Pending' }}</div>
                      <div><span class="text-[var(--text3)]">Sent:</span> {{ rRound.date_sent || '-' }}</div>
                      
                      <div>
                        <span class="text-[var(--text3)]">Returned Doc:</span> 
                        <a v-if="rRound.file_link" :href="rRound.file_link" target="_blank" class="text-[var(--accent)] font-bold ml-1">Link ↗</a>
                        <a v-else-if="rRound.file_path" :href="baseUrl + '/' + rRound.file_path" target="_blank" class="text-[var(--accent)] font-bold ml-1">File ↗</a>
                        <span v-else class="italic text-[var(--text3)] ml-1">None</span>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="form-group full" v-if="fullMs.editor_comments">
                <label>Editor's Comments / Remarks</label>
                <textarea :value="fullMs.editor_comments" rows="2" disabled></textarea>
              </div>
              <div class="form-group full" v-if="fullMs.eic">
                <label>EIC Instructions</label>
                <textarea :value="fullMs.eic" rows="2" disabled></textarea>
              </div>
              <div class="form-group full" v-if="fullMs.prenotes">
                <label>Initial Submission Notes</label>
                <textarea :value="fullMs.prenotes" rows="2" disabled></textarea>
              </div>
            </div>
          </div>

          <h3 class="font-bold text-[12px] uppercase tracking-widest text-[var(--text2)] mb-3 border-b border-[var(--border)] pb-2">Decision Status</h3>

          <div class="form-group mb-5">
            <label class="accent-label">Final Decision</label>
            <select v-model="form.final_decision" :disabled="!canEditStatus">
              <option value="">- Select Decision -</option>
              <option>Returned to author for review</option>
              <option>Return to reviewer for final</option>
              <option>Under review of EIC</option>
              <option>Reject</option>
              <option>Accept for publication</option>
            </select>
          </div>

          <div v-if="['Returned to author for review', 'Return to reviewer for final', 'Under review of EIC'].includes(form.final_decision)" class="form-group mb-5">
            <label class="accent-label">Reason / Instructions for Staff</label>
            <textarea v-model="form.routing_reason" rows="3" placeholder="Provide specific instructions or reasons for returning this manuscript..."></textarea>
          </div>

          <div v-if="form.final_decision === 'Accept for publication'" class="grid grid-cols-2 gap-4 mt-4 p-4 border border-[var(--accent)]/30 bg-[var(--accent)]/5 rounded-lg">
            <div class="form-group">
              <label class="accent-label">Date Accepted</label>
              <input type="date" v-model="form.date_accepted" :disabled="!canEditStatus">
            </div>
            <div class="form-group">
              <label class="accent-label">Date Last Revised</label>
              <input type="date" v-model="form.date_revised" :disabled="!canEditStatus">
            </div>
          </div>

        </div>

        <div class="modal-footer">
          <button class="btn-outline" @click="closeModal">Cancel</button>
          <button v-if="canEditStatus" class="btn-primary" @click="saveStatus">Save Decision</button>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute } from 'vue-router';
import axios from 'axios';

const route = useRoute();
const viewFilter = ref('all');
const manuscripts = ref([]);
const searchQuery = ref('');
const currentRole = ref(localStorage.getItem('user_role') || '');

const isModalOpen = ref(false);
const editingId = ref(null);
const fullMs = ref(null);
const form = ref({ code: '', title: '', final_decision: '', date_accepted: '', date_revised: '', routing_reason: '' });

// Assume EIC and Editor can update the final decision
const canEditStatus = computed(() => ['eic', 'editor', 'repo_staff'].includes(currentRole.value));

const baseUrl = import.meta.env.VITE_API_BASE_URL ? import.meta.env.VITE_API_BASE_URL.replace('/index.php/api', '') : 'http://localhost/mjsir_tracker/backend/public';
const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost/mjsir_tracker/backend/public/index.php/api';
const api = axios.create({ baseURL: API_BASE_URL });

onMounted(async () => {
  if (route.query.view) {
    viewFilter.value = route.query.view;
  }
  await fetchManuscripts();
});

watch(() => route.query.view, (newView) => {
  if (newView) {
    viewFilter.value = newView;
  }
});

const fetchManuscripts = async () => {
  try {
    const response = await api.get('/manuscripts');
    manuscripts.value = response.data;
  } catch (error) {
    console.error("Error fetching data:", error);
  }
};

const getDecisionClass = (status) => {
  if (!status) return 'default';
  const s = status.toLowerCase();
  if (s.includes('accept')) return 'accepted';
  if (s.includes('reject')) return 'rejected';
  if (s.includes('review')) return 'review';
  return 'default';
};

const searchedManuscripts = computed(() => {
  let result = manuscripts.value;
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase();
    result = result.filter(m => 
      (m.code || '').toLowerCase().includes(q) || 
      (m.title || '').toLowerCase().includes(q)
    );
  }
  return result;
});

const pendingManuscripts = computed(() => {
  return searchedManuscripts.value.filter(m => m.final_decision !== 'Accept for publication' && m.final_decision !== 'Reject');
});

const acceptedManuscripts = computed(() => {
  return searchedManuscripts.value.filter(m => m.final_decision === 'Accept for publication');
});

const rejectedManuscripts = computed(() => {
  return searchedManuscripts.value.filter(m => m.final_decision === 'Reject');
});

const openModal = (ms) => {
  editingId.value = ms.id;
  fullMs.value = ms;
  form.value = {
    code: ms.code,
    title: ms.title,
    final_decision: ms.final_decision || '',
    date_accepted: ms.date_accepted || '',
    date_revised: ms.date_revised || '',
    routing_reason: ''
  };
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
  editingId.value = null;
  fullMs.value = null;
};

const saveStatus = async () => {
  try {
    const updateData = {
      final_decision: form.value.final_decision,
      date_accepted: form.value.date_accepted,
      date_revised: form.value.date_revised
    };
    
    // Automated routing: If returned or sent to EIC, it's not a "final" decision yet.
    // It routes back to the respective workflow queues.
    if (form.value.final_decision === 'Under review of EIC') {
      updateData.editor_remarks = 'For EIC before review';
      updateData.final_decision = ''; // Clear final decision so it stays pending
      if (form.value.routing_reason) updateData.eic = form.value.routing_reason;
    } else if (form.value.final_decision === 'Returned to author for review' || form.value.final_decision === 'Return to reviewer for final') {
      updateData.editor_remarks = form.value.final_decision;
      updateData.prescreen_status = 'Ongoing';
      updateData.final_decision = ''; // Clear final decision so it stays pending
      if (form.value.routing_reason) updateData.eic = form.value.routing_reason;
    }
    
    await api.post(`/manuscripts/${editingId.value}`, updateData, {
      headers: { 'Content-Type': 'application/json' }
    });
    
    try {
      const message = `EIC updated decision for ${fullMs.value.code}`;
      await api.post('/notifications', { target_user: 'repo_staff', message });
      if (fullMs.value.editor) {
        await api.post('/notifications', { target_user: fullMs.value.editor, message });
      }
    } catch(e) { console.error(e); }
    
    await fetchManuscripts();
    closeModal();
  } catch (error) {
    console.error("Error saving status:", error);
    alert("An error occurred while saving the decision.");
  }
};
</script>



