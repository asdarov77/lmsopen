import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import api from '@/services/api.service';

export const useCourseStore = defineStore('course', () => {
  const courses = ref([]);
  const currentCourse = ref(null);
  const categories = ref([]);
  const aircrafts = ref([]);
  const group2learnings = ref([]);
  const isLoading = ref(false);
  const error = ref(null);
  const totalCourses = ref(0);
  const totalCategories = ref(0);

  const pagination = ref({
    page: 1,
    perPage: 15,
    total: 0,
    totalPages: 0,
  });

  const category = ref({
    id: null,
    title: '',
    description: '',
    parent_id: null,
  });

  const group2learning = ref({
    id: null,
    course_id: null,
    group_id: null,
    category_id: null,
    parent_id: null,
    teacher: '',
    typeOfLesson: '',
    study_from: '',
    study_to: '',
  });

  const courseList = computed(() =>
    courses.value.map(course => ({
      id: course.id,
      title: course.title,
      short_description: course.short_description,
      long_description: course.long_description,
      path: course.path,
      aircraft_id: course.aircraft_id,
      visible: course.visible,
      aircraft: course.aircraft?.title,
      categories: course.categories || [],
    }))
  );

  const categoryList = computed(() =>
    categories.value.map(cat => ({
      id: cat.id,
      title: cat.title,
      description: cat.description,
      parent_id: cat.parent_id,
    }))
  );

  const aircraftList = computed(() =>
    aircrafts.value.map(a => ({
      id: a.id,
      title: a.title,
      path: a.path,
    }))
  );

  const group2learningList = computed(() =>
    group2learnings.value.map(g => ({
      id: g.id,
      course_id: g.course_id,
      group_id: g.group_id,
      category_id: g.category_id,
      parent_id: g.parent_id,
      teacher: g.teacher,
      typeOfLesson: g.typeOfLesson,
      study_from: g.study_from,
      study_to: g.study_to,
    }))
  );

  async function fetchCourses(params = {}) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get('/api/courses', { params });
      const items = response.data.data || [];
      const meta = response.data.meta || {};
      const pag = meta.pagination || pagination.value;

      totalCourses.value = pag.total || items.length;
      pagination.value = pag;
      courses.value = items;

      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка загрузки курсов';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchCoursesFilter(params) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get('/api/courses/filter', { params });
      const items = response.data.data || [];
      const meta = response.data.meta || {};
      const pag = meta.pagination || pagination.value;

      totalCourses.value = pag.total || items.length;
      pagination.value = pag;
      courses.value = items;

      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка фильтрации курсов';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchCourse(payload) {
    isLoading.value = true;
    error.value = null;

    try {
      let response;

      if (typeof payload === 'object') {
        const { course_id, category_id } = payload;
        if (course_id && category_id) {
          response = await api.get(`/api/courses/${course_id}/category/${category_id}`);
        } else if (course_id) {
          response = await api.get(`/api/courses/${course_id}`);
        } else if (category_id) {
          response = await api.get(`/api/categories/${category_id}/courses`);
        } else {
          response = await api.get('/api/courses');
        }
      } else {
        response = await api.get(`/api/courses/${payload}`);
      }

      currentCourse.value = response.data.data || response.data;
      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка загрузки курса';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchCategories() {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get('/api/categories');
      const catData = response.data.data || response.data || [];
      categories.value = Array.isArray(catData) ? catData.sort((a, b) => parseFloat(a.id) - parseFloat(b.id)) : [];
      totalCategories.value = categories.value.length;
      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка загрузки категорий';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchCategory(id) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get(`/api/categories/${id}`);
      category.value = response.data.data || response.data;
      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка загрузки категории';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function createCategory(data) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.post('/api/categories', data);
      categories.value = response.data.data || response.data;
      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка создания категории';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateCategory(id, data) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.put(`/api/categories/${id}`, data);
      const index = categories.value.findIndex(c => c.id === id);
      if (index !== -1) {
        categories.value[index] = response.data;
      }
      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка обновления категории';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function deleteCategory(id) {
    isLoading.value = true;
    error.value = null;

    try {
      await api.delete(`/api/categories/${id}`);
      categories.value = categories.value.filter(c => c.id !== id);
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка удаления категории';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchAircrafts() {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get('/api/aircrafts');
      aircrafts.value = response.data.data || [];
      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка загрузки классов ВС';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchAircraft(id) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get(`/api/aircrafts/${id}`);
      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка загрузки класса ВС';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function createCourse(data) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.post('/api/courses', data);
      courses.value.unshift(response.data);
      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка создания курса';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function updateCourse(id, data) {
    isLoading.value = true;
    error.value = null;

    try {
      const idx = courses.value.findIndex(c => c.id === id);
      const previous = idx >= 0 ? { ...courses.value[idx] } : null;

      if (idx >= 0) {
        courses.value[idx] = { ...courses.value[idx], ...data };
      }

      const response = await api.put(`/api/courses/${id}`, data);
      const updated = response.data.data || response.data;

      if (idx >= 0) {
        courses.value[idx] = updated;
      }
      if (currentCourse.value?.id === id) {
        currentCourse.value = updated;
      }

      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка обновления курса';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function deleteCourse(id) {
    isLoading.value = true;
    error.value = null;

    try {
      await api.delete(`/api/courses/${id}`);
      courses.value = courses.value.filter(c => c.id !== id);
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка удаления курса';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function fetchGroup2learnings(params) {
    isLoading.value = true;
    error.value = null;

    try {
      const response = await api.get('/api/group2learnings', { params });
      group2learnings.value = response.data;
      return response;
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка загрузки привязок групп';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  async function deleteGroup2learnings(id) {
    isLoading.value = true;
    error.value = null;

    try {
      await api.delete(`/api/group2learnings/${id}`);
      group2learnings.value = group2learnings.value.filter(g => g.id !== id);
    } catch (err) {
      error.value = err.response?.data?.message || 'Ошибка удаления привязки группы';
      throw err;
    } finally {
      isLoading.value = false;
    }
  }

  function clearCurrentCourse() {
    currentCourse.value = null;
  }

  function clearError() {
    error.value = null;
  }

  function reset() {
    courses.value = [];
    currentCourse.value = null;
    categories.value = [];
    aircrafts.value = [];
    group2learnings.value = [];
    isLoading.value = false;
    error.value = null;
    totalCourses.value = 0;
    totalCategories.value = 0;
    pagination.value = { page: 1, perPage: 15, total: 0, totalPages: 0 };
    category.value = { id: null, title: '', description: '', parent_id: null };
    group2learning.value = { id: null, course_id: null, group_id: null, category_id: null, parent_id: null, teacher: '', typeOfLesson: '', study_from: '', study_to: '' };
  }

  return {
    courses,
    currentCourse,
    categories,
    aircrafts,
    group2learnings,
    isLoading,
    error,
    totalCourses,
    totalCategories,
    pagination,
    category,
    group2learning,

    courseList,
    categoryList,
    aircraftList,
    group2learningList,

    fetchCourses,
    fetchCoursesFilter,
    fetchCourse,
    fetchCategories,
    fetchCategory,
    createCategory,
    updateCategory,
    deleteCategory,
    fetchAircrafts,
    fetchAircraft,
    createCourse,
    updateCourse,
    deleteCourse,
    fetchGroup2learnings,
    deleteGroup2learnings,
    clearCurrentCourse,
    clearError,
    reset,
  };
});
