import { useMutation, useQuery, useQueryClient } from '@tanstack/react-query';
import api from '../lib/axios';
import { useAuthStore } from '../store/authStore';
import { useNavigate } from 'react-router-dom';

export function useAuth() {
    const queryClient = useQueryClient();
    const setAuth = useAuthStore((state) => state.setAuth);
    const logoutState = useAuthStore((state) => state.logout);
    const navigate = useNavigate();

    // Login Mutation
    const loginMutation = useMutation({
        mutationFn: async (credentials: { email: string; password: string }) => {
            const response = await api.post('/auth/login', credentials);
            return response.data;
        },
        onSuccess: (data) => {
            // Store both user and token
            setAuth(data.user, data.token);
            navigate('/');
        },
    });

    // Logout Mutation
    const logoutMutation = useMutation({
        mutationFn: async () => {
            try {
                await api.post('/auth/logout');
            } catch {
                // Ignore errors - we want to logout locally even if server fails
            }
        },
        onSuccess: () => {
            logoutState();
            queryClient.clear();
            navigate('/login');
        },
        onError: () => {
            // Still logout locally even if the API call failed
            logoutState();
            queryClient.clear();
            navigate('/login');
        },
    });

    // Fetch User Query (Check Auth Status)
    const token = useAuthStore((state) => state.token);
    const userQuery = useQuery({
        queryKey: ['authUser'],
        queryFn: async () => {
            const response = await api.get('/auth/me');
            return response.data.user;
        },
        retry: false,
        enabled: !!token, // Only fetch if we have a token
    });

    return {
        login: loginMutation,
        logout: logoutMutation,
        user: userQuery,
    };
}
