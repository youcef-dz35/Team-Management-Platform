import api from '../axios';

export interface ProjectReportEntry {
    id?: number;
    user_id: number;
    user?: { name: string };
    hours_worked: number;
    notes?: string;
}

export interface ProjectReport {
    id: number;
    project_id: number;
    project?: { name: string };
    user_id: number;
    user?: { name: string };
    period_start: string;
    period_end: string;
    status: 'draft' | 'submitted' | 'amended';
    comments?: string;
    entries: ProjectReportEntry[];
    amendments?: any[];
    created_at: string;
}

export interface StoreReportPayload {
    project_id: number;
    period_start: string;
    period_end: string;
    entries: { user_id: number; hours_worked: number; notes?: string }[];
    comments?: string;
    status?: 'draft' | 'submitted';
}

export interface UpdateReportPayload {
    entries?: { user_id: number; hours_worked: number; notes?: string }[];
    comments?: string;
    status?: 'draft' | 'submitted';
}

export interface AmendReportPayload {
    reason: string;
    entries: { user_id: number; hours_worked: number; notes?: string }[];
    comments?: string;
}

export const reportsApi = {
    // List all reports (scoped by backend policy)
    getReports: async (page = 1) => {
        const response = await api.get(`/project-reports?page=${page}`);
        return response.data;
    },

    // Get single report details
    getReport: async (id: string | number) => {
        const response = await api.get(`/project-reports/${id}`);
        return response.data;
    },

    // Create new report (draft or submitted)
    createReport: async (data: StoreReportPayload) => {
        const response = await api.post('/project-reports', data);
        return response.data;
    },

    // Update existing draft
    updateReport: async (id: string | number, data: UpdateReportPayload) => {
        const response = await api.put(`/project-reports/${id}`, data);
        return response.data;
    },

    // Submit a report (status -> submitted)
    submitReport: async (id: string | number) => {
        const response = await api.post(`/project-reports/${id}/submit`);
        return response.data;
    },

    // Amend a submitted report
    amendReport: async (id: string | number, data: AmendReportPayload) => {
        const response = await api.post(`/project-reports/${id}/amend`, data);
        return response.data;
    },
};
