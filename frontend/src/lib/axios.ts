import axios, { AxiosError, InternalAxiosRequestConfig } from 'axios';

// API error structure
export interface ApiError {
  message: string;
  errors?: Record<string, string[]>;
  status: number;
}

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost/api/v1',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  withCredentials: true,
  timeout: 30000,
});

// Helper to get token from localStorage (zustand persist)
const getAuthToken = (): string | null => {
  try {
    const stored = localStorage.getItem('auth-storage');
    if (stored) {
      const parsed = JSON.parse(stored);
      return parsed?.state?.token || null;
    }
  } catch {
    // Ignore parse errors
  }
  return null;
};

// Helper to get CSRF token from cookie
const getCsrfToken = (): string | null => {
  const value = `; ${document.cookie}`;
  const parts = value.split(`; XSRF-TOKEN=`);
  if (parts.length === 2) {
    const token = parts.pop()?.split(';').shift();
    return token ? decodeURIComponent(token) : null;
  }
  return null;
};

// Flag to track if we've initialized CSRF
let csrfInitialized = false;

// Initialize CSRF token by calling /sanctum/csrf-cookie
const initializeCsrf = async (): Promise<void> => {
  // Always check if cookie exists - don't rely on flag
  // The cookie might have expired or been cleared
  const existingToken = getCsrfToken();
  if (existingToken) {
    // Already have a valid token in cookies
    return;
  }

  try {
    await axios.get('http://localhost/sanctum/csrf-cookie', {
      withCredentials: true,
    });
  } catch (error) {
    console.error('[CSRF] Failed to initialize CSRF token:', error);
    // Don't throw - allow the request to proceed and let backend handle it
  }
};

// Request interceptor to add Bearer token and CSRF token
api.interceptors.request.use(
  async (config: InternalAxiosRequestConfig) => {
    // For state-changing methods, ensure CSRF token is available
    if (['post', 'put', 'patch', 'delete'].includes(config.method?.toLowerCase() || '')) {
      // Check for existing CSRF token first
      let csrfToken = getCsrfToken();

      // If no token exists, initialize CSRF
      if (!csrfToken) {
        await initializeCsrf();
        csrfToken = getCsrfToken();
      }

      // Add CSRF token header if available
      if (csrfToken) {
        config.headers['X-XSRF-TOKEN'] = csrfToken;
      }
    }

    // Add Bearer token for API authentication
    const token = getAuthToken();
    if (token) {
      config.headers['Authorization'] = `Bearer ${token}`;
    }

    // Log requests in development
    if (import.meta.env.DEV) {
      console.log(`[API] ${config.method?.toUpperCase()} ${config.url}`);
    }

    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor for error handling
api.interceptors.response.use(
  (response) => {
    return response;
  },
  async (error: AxiosError<{ message?: string; errors?: Record<string, string[]> }>) => {
    const status = error.response?.status || 0;

    // Handle CSRF token mismatch - reinitialize and retry once
    if (status === 419) {
      const config = error.config;
      if (config && !config.headers['X-Retry-After-CSRF']) {
        console.log('[CSRF] Token mismatch detected, reinitializing...');
        csrfInitialized = false;
        await initializeCsrf();

        // Mark as retry to prevent infinite loops
        config.headers['X-Retry-After-CSRF'] = 'true';

        // Retry the original request
        return api.request(config);
      }
    }

    // Handle specific status codes
    switch (status) {
      case 401:
        // Unauthorized - clear auth state and redirect to login
        localStorage.removeItem('auth-storage');
        if (window.location.pathname !== '/login') {
          window.location.href = '/login';
        }
        break;

      case 403:
        // Forbidden - user doesn't have permission
        console.error('[API] Access forbidden:', error.config?.url);
        break;

      case 404:
        // Not found
        console.error('[API] Resource not found:', error.config?.url);
        break;

      case 422:
        // Validation error - return as-is for form handling
        break;

      case 429:
        // Too many requests
        console.error('[API] Rate limited');
        break;

      case 500:
      case 502:
      case 503:
        // Server error
        console.error('[API] Server error:', status);
        break;

      default:
        if (!error.response) {
          // Network error
          console.error('[API] Network error - server may be unavailable');
        }
    }

    // Create a standardized error object
    const apiError: ApiError = {
      message: error.response?.data?.message || error.message || 'An unexpected error occurred',
      errors: error.response?.data?.errors,
      status,
    };

    return Promise.reject(apiError);
  }
);

// Helper to check if error is an API error
export const isApiError = (error: unknown): error is ApiError => {
  return typeof error === 'object' && error !== null && 'message' in error && 'status' in error;
};

// Helper to get validation errors from API error
export const getValidationErrors = (error: ApiError): Record<string, string> => {
  const result: Record<string, string> = {};
  if (error.errors) {
    Object.entries(error.errors).forEach(([field, messages]) => {
      result[field] = messages[0] || 'Invalid value';
    });
  }
  return result;
};

// Export CSRF initialization function for manual use if needed
export { initializeCsrf };

export default api;
