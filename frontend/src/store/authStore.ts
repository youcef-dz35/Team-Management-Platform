import { create } from 'zustand';
import { persist } from 'zustand/middleware';

export interface User {
    id: number;
    name: string;
    email: string;
    roles: string[];
    department?: {
        id: number;
        name: string;
    };
    permissions: string[];
    is_god_mode: boolean;
}

interface AuthState {
    user: User | null;
    token: string | null;
    isAuthenticated: boolean;
    setUser: (user: User | null) => void;
    setToken: (token: string | null) => void;
    setAuth: (user: User | null, token: string | null) => void;
    logout: () => void;
}

export const useAuthStore = create<AuthState>()(
    persist(
        (set) => ({
            user: null,
            token: null,
            isAuthenticated: false,
            setUser: (user) => set({ user, isAuthenticated: !!user }),
            setToken: (token) => set({ token }),
            setAuth: (user, token) => set({ user, token, isAuthenticated: !!(user && token) }),
            logout: () => set({ user: null, token: null, isAuthenticated: false }),
        }),
        {
            name: 'auth-storage',
        }
    )
);
