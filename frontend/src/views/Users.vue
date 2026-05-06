<template>
  <div>
    <div class="page-header">
      <div class="page-header-stack">
        <h1 class="page-title">User Management</h1>
        <div class="page-subtitle">Add and manage REPO staff and Associate Editors for the system.</div>
      </div>
      <button class="btn-primary" @click="openModal()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
        Add User
      </button>
    </div>

    <div class="table-container">
      <table class="tbl">
        <thead>
          <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
            <th>Joined</th>
            <th class="text-right">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="users.length === 0">
            <td colspan="5" class="table-empty">Loading users...</td>
          </tr>
          <tr v-for="u in users" :key="u.id">
            <td>
              <div class="font-bold text-[var(--text)]">{{ u.name }}</div>
            </td>
            <td>{{ u.email }}</td>
            <td>
              <span class="status-badge review" v-if="u.role === 'eic'">Editor-in-Chief</span>
              <span class="status-badge accepted" v-else-if="u.role === 'repo_staff'">REPO Staff</span>
              <span class="status-badge" style="background: rgba(14, 165, 233, 0.1); color: #38bdf8; border: 1px solid rgba(14, 165, 233, 0.2);" v-else>Associate Editor</span>
            </td>
            <td class="td-sub">{{ new Date(u.created_at).toLocaleDateString() }}</td>
            <td class="table-actions">
              <button @click="editUser(u)" class="btn-outline btn-compact" v-if="u.role !== 'eic'" style="margin-right: 8px;">Edit</button>
              <button @click="deleteUser(u.id)" class="btn-outline btn-compact btn-danger-ghost" v-if="u.role !== 'eic'">Remove</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- MODAL -->
    <div class="modal-overlay" :class="{ open: isModalOpen }">
      <div class="modal" style="width: min(96vw, 560px);">
        <div class="modal-header">
          <h2>{{ editingUserId ? 'Edit User' : 'Add New User' }}</h2>
          <button class="modal-close" @click="closeModal">X</button>
        </div>
        <div class="modal-body modal-stack">
          <div class="form-group">
            <label>Full Name</label>
            <input v-model="form.name" placeholder="John Doe">
          </div>
          <div class="form-group">
            <label>Email Address</label>
            <input type="email" v-model="form.email" placeholder="email@example.com">
          </div>
          <div class="form-group">
            <label>Role</label>
            <select v-model="form.role">
              <option value="repo_staff">REPO Staff</option>
              <option value="editor">Associate Editor</option>
            </select>
          </div>
          <div class="form-group">
            <label>{{ editingUserId ? 'New Password (leave blank to keep current)' : 'Initial Password' }}</label>
            <input type="text" v-model="form.password" placeholder="e.g. Mjsir2026!">
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn-outline" @click="closeModal">Cancel</button>
          <button class="btn-primary" @click="saveUser">{{ editingUserId ? 'Save Changes' : 'Create User' }}</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const users = ref([]);
const isModalOpen = ref(false);
const editingUserId = ref(null);
const form = ref({ name: '', email: '', role: 'repo_staff', password: '' });

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost/mjsir_tracker/backend/public/index.php/api';
const api = axios.create({ baseURL: API_BASE_URL });

const fetchUsers = async () => {
  try {
    const res = await api.get('/users');
    users.value = res.data;
  } catch (error) {
    console.error("Error fetching users", error);
  }
};

onMounted(() => {
  fetchUsers();
});

const openModal = () => {
  editingUserId.value = null;
  form.value = { name: '', email: '', role: 'repo_staff', password: '' };
  isModalOpen.value = true;
};

const editUser = (user) => {
  editingUserId.value = user.id;
  form.value = { name: user.name, email: user.email, role: user.role, password: '' };
  isModalOpen.value = true;
};

const closeModal = () => {
  isModalOpen.value = false;
};

const saveUser = async () => {
  if (!form.value.name || !form.value.email || (!editingUserId.value && !form.value.password)) {
    alert("Please fill all required fields.");
    return;
  }
  try {
    if (editingUserId.value) {
      await api.put(`/users/${editingUserId.value}`, form.value);
    } else {
      await api.post('/users', form.value);
    }
    await fetchUsers();
    closeModal();
  } catch (err) {
    console.error(err);
    alert("Failed to save user. Ensure the email is unique.");
  }
};

const deleteUser = async (id) => {
  if (!confirm("Are you sure you want to remove this user?")) return;
  try {
    await api.delete(`/users/${id}`);
    await fetchUsers();
  } catch (err) {
    console.error(err);
  }
};
</script>



