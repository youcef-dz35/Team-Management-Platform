import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:9000/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true, // Crucial for Sanctum cookies
});

// Interceptor to handle 401 Unauthorized globally
api.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      // Logic to clear local state if needed (handled by auth store usually)
      // window.location.href = '/login'; // Optional: force redirect or let router handle it
    }
    return Promise.reject(error);
  }
);

export default api;
