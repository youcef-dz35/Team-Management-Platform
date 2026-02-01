import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import api from '../lib/axios';
import { useAuthStore, User } from '../store/authStore';
import { useNavigate } from 'react-router-dom';

export function useAuth() {
    const queryClient = useQueryClient();
    const setUser = useAuthStore((state) => state.setUser);
    const logoutState = useAuthStore((state) => state.logout);
    const navigate = useNavigate();

    // Login Mutation
    const loginMutation = useMutation({
        mutationFn: async (credentials: any) => {
            // Sanctum CSRF protection
            await api.get('/sanctum/csrf-cookie', { baseURL: 'http://localhost:9000' });
            const response = await api.post('/auth/login', credentials);
            return response.data;
        },
        onSuccess: (data) => {
            setUser(data.user);
            navigate('/');
        },
    });

    // Logout Mutation
    const logoutMutation = useMutation({
        mutationFn: async () => {
            await api.post('/auth/logout');
        },
        onSuccess: () => {
            logoutState();
            queryClient.clear();
            navigate('/login');
        },
    });

    // Fetch User Query (Check Auth Status)
    const userQuery = useQuery({
        queryKey: ['authUser'],
        queryFn: async () => {
            const response = await api.get('/auth/me');
            return response.data.user;
        },
        retry: false,
        enabled: !!localStorage.getItem('auth-storage'), // Optimistic check
    });

    return {
        login: loginMutation,
        logout: logoutMutation,
        user: userQuery,
    };
}
