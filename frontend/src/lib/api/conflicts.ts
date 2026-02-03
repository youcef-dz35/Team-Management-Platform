import api from '../axios';

export interface ConflictAlert {
    id: number;
    employee_id: number;
    reporting_period_start: string;
    reporting_period_end: string;
    source_a_hours: number;
    source_b_hours: number;
    discrepancy: number;
    status: 'open' | 'resolved' | 'escalated';
    resolved_by: number | null;
    resolution_notes: string | null;
    resolved_at: string | null;
    escalated_at: string | null;
    created_at: string;
    employee?: {
        id: number;
        name: string;
        email: string;
        department?: {
            id: number;
            name: string;
        };
    };
    resolver?: {
        id: number;
        name: string;
    };
}

export interface ConflictStats {
    total: number;
    open: number;
    escalated: number;
    resolved: number;
    unresolved: number;
}

export interface ResolveConflictPayload {
    resolution_notes: string;
}

export interface RunDetectionPayload {
    period_start: string;
    period_end: string;
}

export const conflictsApi = {
    // Get all conflict alerts
    getConflicts: async (page = 1, status?: string) => {
        const params = new URLSearchParams({ page: String(page) });
        if (status) params.append('status', status);
        const response = await api.get(`/conflicts?${params}`);
        return response.data;
    },

    // Get single conflict
    getConflict: async (id: number | string) => {
        const response = await api.get(`/conflicts/${id}`);
        return response.data;
    },

    // Get conflict stats
    getStats: async (): Promise<ConflictStats> => {
        const response = await api.get('/conflicts/stats');
        return response.data;
    },

    // Resolve a conflict
    resolveConflict: async (id: number | string, data: ResolveConflictPayload) => {
        const response = await api.post(`/conflicts/${id}/resolve`, data);
        return response.data;
    },

    // Manually run conflict detection (CEO/CFO only)
    runDetection: async (data: RunDetectionPayload) => {
        const response = await api.post('/conflicts/run-detection', data);
        return response.data;
    },
};
