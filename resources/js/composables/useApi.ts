import axios from 'axios'

const api = axios.create({
  baseURL: '/api',
  headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
})

api.interceptors.request.use((config) => {
  const token = localStorage.getItem('itams_token')
  if (token) config.headers.Authorization = `Bearer ${token}`
  return config
})

api.interceptors.response.use(
  (res) => res,
  (err) => {
    if (err.response?.status === 401) {
      localStorage.removeItem('itams_token')
      window.location.href = '/login'
    }
    return Promise.reject(err)
  }
)

export default api

export function useAssets() {
  return {
    list:           (params = {}) => api.get('/assets', { params }),
    get:            (id:number) => api.get(`/assets/${id}`),
    create:         (data:any) => api.post('/assets', data),
    update:         (id:number, data:any) => api.put(`/assets/${id}`, data),
    remove:         (id:number) => api.delete(`/assets/${id}`),
    assign:         (id:number, data:any) => api.post(`/assets/${id}/assign`, data),
    unassign:       (id:number) => api.post(`/assets/${id}/unassign`),
    syncProperties: (id:number, properties:any) => api.post(`/assets/${id}/properties`, { properties }),
  }
}

export function useCategories() {
  return {
    list:           (params = {}) => api.get('/categories', { params }),
    get:            (id:number) => api.get(`/categories/${id}`),
    tree:           (id:number) => api.get(`/categories/${id}/tree`),
    create:         (data:any) => api.post('/categories', data),
    update:         (id:number, data:any) => api.put(`/categories/${id}`, data),
    remove:         (id:number) => api.delete(`/categories/${id}`),
    getProperties:  (id:number) => api.get(`/categories/${id}/property-definitions`),
    syncProperties: (id:number, definitions:any) => api.put(`/categories/${id}/property-definitions`, { definitions }),
  }
}

export function useTags() {
  return {
    list:   (params = {}) => api.get('/tags', { params }),
    create: (data:any) => api.post('/tags', data),
    update: (id:number, data:any) => api.put(`/tags/${id}`, data),
    remove: (id:number) => api.delete(`/tags/${id}`),
  }
}

export function useLocations() {
  return {
    list:   (params = {}) => api.get('/locations', { params }),
    get:    (id:number) => api.get(`/locations/${id}`),
    tree:   (id:number) => api.get(`/locations/${id}/tree`),
    create: (data:any) => api.post('/locations', data),
    update: (id:number, data:any) => api.put(`/locations/${id}`, data),
    remove: (id:number) => api.delete(`/locations/${id}`),
  }
}

export function useManufacturers() {
  return {
    list:   (params = {}) => api.get('/manufacturers', { params }),
    create: (data:any) => api.post('/manufacturers', data),
    update: (id:number, data:any) => api.put(`/manufacturers/${id}`, data),
    remove: (id:number) => api.delete(`/manufacturers/${id}`),
  }
}

export function useEmployees() {
  return {
    list: (params = {}) => api.get('/employees', { params }),
    get:  (id:number) => api.get(`/employees/${id}`),
  }
}
