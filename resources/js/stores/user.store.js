import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/services/api.service';

export const useUserStore = defineStore('user', () => {
  const users = ref([]);
  const user = ref({
    id: null,
    fio: '',
    role: '',
    group_id: '',
    permissions: [],
  });
  const groups = ref([]);
  const allGroups = ref([]);
  const allPermissions = ref([]);
  const isLoading = ref(false);
  const error = ref(null);
  const totalUsers = ref(0);
  const totalGroups = ref(0);

  const pagination = ref({
    page: 1,
    perPage: 15,
    total: 0,
    totalPages: 0,
  });

  const group = ref({
    id: null,
    groupname: '',
    groupdescription: '',
    study_from: '',
    study_to: '',
    group2learnings: [],
  });

  const userList = computed(() =>
    users.value.map(u => ({
      id: u.id,
      fio: u.fio,
      role: u.role,
      group_id: u.group_id,
      group: u.group ? u.group.groupname : '',
      phonenumber: u.phonenumber,
      city: u.city,
      country: u.country,
      organization: u.organization,
      position: u.position,
      rank: u.rank,
      spfere: u.spfere,
      specialization: u.specialization,
      permissions: u.permissions,
    }))
  );

  const groupList = computed(() =>
    allGroups.value.map(g => ({
      id: g.id,
      groupname: g.groupname,
      groupdescription: g.groupdescription,
      group2learnings: g.group2learnings,
      courses: g.courses,
    }))
  );

  async function fetchUsers(params = {}) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get('/api/users', { params });
      const items = response.data.data || response.data;
      const pag = (response.data.meta && response.data.meta.pagination) || pagination.value;

      totalUsers.value = pag.total || items.length;
      pagination.value = pag;
      users.value = items;

      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка загрузки пользователей';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchUser(id) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get(`/api/users/${id}`);
      user.value = response.data;
      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка загрузки пользователя';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function createUser(data) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.post('/api/register', data);
      user.value = response.data.user || response.data.data || response.data;
      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка создания пользователя';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateUser(id, data) {
    isLoading.value = true;
    error.value = null;

    try {
      const idx = users.value.findIndex(u => u.id === id);
      const previous = idx >= 0 ? { ...users.value[idx] } : null;

      if (idx >= 0) {
        users.value[idx] = { ...users.value[idx], ...data };
      }

      const response = await api.put(`/api/users/${id}`, data);
      const updated = response.data.data || response.data;

      if (idx >= 0) {
        users.value[idx] = updated;
      }

      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка обновления пользователя';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function deleteUser(id) {
    isLoading.value = true;
    error.value = null;

    try {
      await api.delete(`/api/users/${id}`);
      users.value = users.value.filter(u => u.id !== id);
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка удаления пользователя';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function chpassUser(id, data) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.put(`/api/users/${id}/password`, data);
      const idx = users.value.findIndex(u => u.id === id);
      if (idx >= 0) {
        users.value[idx] = { ...users.value[idx], ...response.data };
      }
      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка смены пароля';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchGroups() {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get('/api/groups');
      const items = response.data.data || response.data;
      totalGroups.value = items.length || 0;
      allGroups.value = items;
      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка загрузки групп';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchGroup(id) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get(`/api/groups/${id}`);
      group.value = response.data;
      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка загрузки группы';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function createGroup(data) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.post('/api/groups', data);
      const created = response.data.data || response.data;
      allGroups.value.unshift(created);
      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка создания группы';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateGroup(id, data) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.put(`/api/groups/${id}`, data);
      const idx = allGroups.value.findIndex(g => g.id === id);
      if (idx !== -1) {
        allGroups.value[idx] = response.data;
      }
      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка обновления группы';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function deleteGroup(id) {
    isLoading.value = true;
    error.value = null;

    try {
      await api.delete(`/api/groups/${id}`);
      allGroups.value = allGroups.value.filter(g => g.id !== id);
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка удаления группы';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchPermissions() {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get('/api/permissions');
      allPermissions.value = response.data.data || response.data;
      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка загрузки разрешений';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  function clearError() {
    error.value = null;
  }

  return {
    users,
    user,
    groups,
    allGroups,
    allPermissions,
    isLoading,
    error,
    totalUsers,
    totalGroups,
    pagination,
    group,

    userList,
    groupList,

    fetchUsers,
    fetchUser,
    createUser,
    updateUser,
    deleteUser,
    chpassUser,
    fetchGroups,
    fetchGroup,
    createGroup,
    updateGroup,
    deleteGroup,
    fetchPermissions,
    clearError,
  };
});
