import { createSlice, PayloadAction } from '@reduxjs/toolkit';

export interface Toast {
    id: string;
    type: 'success' | 'error' | 'warning' | 'info';
    message: string;
    duration?: number;
}

interface UiState {
    // Global loading state
    isLoading: boolean;
    loadingMessage: string | null;

    // Toast notifications
    toasts: Toast[];

    // Sidebar state
    sidebarCollapsed: boolean;

    // Modal state
    activeModal: string | null;
    modalData: Record<string, unknown> | null;

    // Global error
    globalError: string | null;
}

const initialState: UiState = {
    isLoading: false,
    loadingMessage: null,
    toasts: [],
    sidebarCollapsed: false,
    activeModal: null,
    modalData: null,
    globalError: null,
};

const uiSlice = createSlice({
    name: 'ui',
    initialState,
    reducers: {
        // Loading
        setLoading: (state, action: PayloadAction<boolean>) => {
            state.isLoading = action.payload;
            if (!action.payload) {
                state.loadingMessage = null;
            }
        },
        setLoadingWithMessage: (state, action: PayloadAction<string>) => {
            state.isLoading = true;
            state.loadingMessage = action.payload;
        },

        // Toasts
        addToast: (state, action: PayloadAction<Omit<Toast, 'id'>>) => {
            const id = `toast-${Date.now()}-${Math.random().toString(36).substr(2, 9)}`;
            state.toasts.push({ ...action.payload, id });
        },
        removeToast: (state, action: PayloadAction<string>) => {
            state.toasts = state.toasts.filter(toast => toast.id !== action.payload);
        },
        clearToasts: (state) => {
            state.toasts = [];
        },

        // Sidebar
        toggleSidebar: (state) => {
            state.sidebarCollapsed = !state.sidebarCollapsed;
        },
        setSidebarCollapsed: (state, action: PayloadAction<boolean>) => {
            state.sidebarCollapsed = action.payload;
        },

        // Modal
        openModal: (state, action: PayloadAction<{ modal: string; data?: Record<string, unknown> }>) => {
            state.activeModal = action.payload.modal;
            state.modalData = action.payload.data || null;
        },
        closeModal: (state) => {
            state.activeModal = null;
            state.modalData = null;
        },

        // Global error
        setGlobalError: (state, action: PayloadAction<string | null>) => {
            state.globalError = action.payload;
        },
        clearGlobalError: (state) => {
            state.globalError = null;
        },
    },
});

export const {
    setLoading,
    setLoadingWithMessage,
    addToast,
    removeToast,
    clearToasts,
    toggleSidebar,
    setSidebarCollapsed,
    openModal,
    closeModal,
    setGlobalError,
    clearGlobalError,
} = uiSlice.actions;

// Helper action creators for common toast types
export const showSuccessToast = (message: string) => addToast({ type: 'success', message, duration: 3000 });
export const showErrorToast = (message: string) => addToast({ type: 'error', message, duration: 5000 });
export const showWarningToast = (message: string) => addToast({ type: 'warning', message, duration: 4000 });
export const showInfoToast = (message: string) => addToast({ type: 'info', message, duration: 3000 });

export default uiSlice.reducer;
