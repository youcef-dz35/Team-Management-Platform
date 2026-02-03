import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { conflictsApi, ResolveConflictPayload, RunDetectionPayload } from '../lib/api/conflicts';

export function useConflicts() {
    const queryClient = useQueryClient();

    // Query: Get All Conflicts
    const useGetConflicts = (page = 1, status?: string) => useQuery({
        queryKey: ['conflicts', page, status],
        queryFn: () => conflictsApi.getConflicts(page, status),
    });

    // Query: Get Single Conflict
    const useGetConflict = (id: string | number) => useQuery({
        queryKey: ['conflict', id],
        queryFn: () => conflictsApi.getConflict(id),
        enabled: !!id,
    });

    // Query: Get Stats
    const useGetStats = () => useQuery({
        queryKey: ['conflicts-stats'],
        queryFn: () => conflictsApi.getStats(),
        refetchInterval: 30000, // Refresh every 30 seconds
    });

    // Mutation: Resolve Conflict
    const resolveConflict = useMutation({
        mutationFn: ({ id, data }: { id: number | string; data: ResolveConflictPayload }) =>
            conflictsApi.resolveConflict(id, data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['conflicts'] });
            queryClient.invalidateQueries({ queryKey: ['conflicts-stats'] });
        },
    });

    // Mutation: Run Detection
    const runDetection = useMutation({
        mutationFn: (data: RunDetectionPayload) => conflictsApi.runDetection(data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['conflicts'] });
            queryClient.invalidateQueries({ queryKey: ['conflicts-stats'] });
        },
    });

    return {
        useGetConflicts,
        useGetConflict,
        useGetStats,
        resolveConflict,
        runDetection,
    };
}
