import { useState, useCallback } from 'react';
import api, { ApiError, isApiError, getValidationErrors } from '../lib/axios';
import { AxiosRequestConfig } from 'axios';

interface UseApiOptions {
    onSuccess?: (data: unknown) => void;
    onError?: (error: ApiError) => void;
}

interface UseApiState<T> {
    data: T | null;
    error: ApiError | null;
    isLoading: boolean;
    validationErrors: Record<string, string>;
}

interface UseApiReturn<T> extends UseApiState<T> {
    execute: (config?: AxiosRequestConfig) => Promise<T | null>;
    reset: () => void;
}

/**
 * Generic API hook for making HTTP requests with loading and error states.
 *
 * @example
 * const { data, isLoading, error, execute } = useApi<User[]>('/users');
 *
 * // GET request
 * useEffect(() => { execute(); }, []);
 *
 * // POST request
 * const handleSubmit = () => execute({ method: 'POST', data: formData });
 */
export function useApi<T = unknown>(
    url: string,
    defaultConfig?: AxiosRequestConfig,
    options?: UseApiOptions
): UseApiReturn<T> {
    const [state, setState] = useState<UseApiState<T>>({
        data: null,
        error: null,
        isLoading: false,
        validationErrors: {},
    });

    const execute = useCallback(
        async (overrideConfig?: AxiosRequestConfig): Promise<T | null> => {
            setState(prev => ({
                ...prev,
                isLoading: true,
                error: null,
                validationErrors: {},
            }));

            try {
                const config: AxiosRequestConfig = {
                    url,
                    method: 'GET',
                    ...defaultConfig,
                    ...overrideConfig,
                };

                const response = await api.request<T>(config);

                setState(prev => ({
                    ...prev,
                    data: response.data,
                    isLoading: false,
                }));

                options?.onSuccess?.(response.data);
                return response.data;
            } catch (err) {
                const apiError: ApiError = isApiError(err)
                    ? err
                    : { message: 'An unexpected error occurred', status: 0 };

                setState(prev => ({
                    ...prev,
                    error: apiError,
                    isLoading: false,
                    validationErrors: getValidationErrors(apiError),
                }));

                options?.onError?.(apiError);
                return null;
            }
        },
        [url, defaultConfig, options]
    );

    const reset = useCallback(() => {
        setState({
            data: null,
            error: null,
            isLoading: false,
            validationErrors: {},
        });
    }, []);

    return {
        ...state,
        execute,
        reset,
    };
}

/**
 * Simplified hook for GET requests that auto-fetches on mount.
 */
export function useFetch<T = unknown>(
    url: string,
    config?: AxiosRequestConfig,
    options?: UseApiOptions & { enabled?: boolean }
) {
    const { enabled = true, ...apiOptions } = options || {};
    const api = useApi<T>(url, { method: 'GET', ...config }, apiOptions);

    // Auto-fetch on mount if enabled
    useState(() => {
        if (enabled) {
            api.execute();
        }
    });

    return api;
}

/**
 * Hook for POST requests.
 */
export function usePost<T = unknown, D = unknown>(
    url: string,
    options?: UseApiOptions
) {
    const api = useApi<T>(url, { method: 'POST' }, options);

    const post = useCallback(
        (data: D, config?: AxiosRequestConfig) => {
            return api.execute({ data, ...config });
        },
        [api]
    );

    return { ...api, post };
}

/**
 * Hook for PUT requests.
 */
export function usePut<T = unknown, D = unknown>(
    url: string,
    options?: UseApiOptions
) {
    const api = useApi<T>(url, { method: 'PUT' }, options);

    const put = useCallback(
        (data: D, config?: AxiosRequestConfig) => {
            return api.execute({ data, ...config });
        },
        [api]
    );

    return { ...api, put };
}

/**
 * Hook for DELETE requests.
 */
export function useDelete<T = unknown>(
    url: string,
    options?: UseApiOptions
) {
    const api = useApi<T>(url, { method: 'DELETE' }, options);

    const del = useCallback(
        (config?: AxiosRequestConfig) => {
            return api.execute(config);
        },
        [api]
    );

    return { ...api, delete: del };
}

export default useApi;
