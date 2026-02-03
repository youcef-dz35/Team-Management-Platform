import api from '../axios';

export interface DepartmentReportEntry {
    id?: number;
    user_id: number;
    user?: { name: string };
    project_id: number;
    project?: { name: string };
    hours_allocated: number;
    notes?: string;
}

export interface DepartmentReport {
    id: number;
    department_id: number;
    department?: { name: string };
    user_id: number;
    user?: { name: string };
    period_start: string;
    period_end: string;
    status: 'draft' | 'submitted' | 'amended';
    comments?: string;
    entries: DepartmentReportEntry[];
    created_at: string;
}

export interface StoreDeptReportPayload {
    department_id: number;
    period_start: string;
    period_end: string;
    entries: { user_id: number; project_id: number; hours_allocated: number; notes?: string }[];
    comments?: string;
    status?: 'draft' | 'submitted';
}

export const deptReportsApi = {
    // List reports
    getReports: async (page = 1) => {
        const response = await api.get(`/department-reports?page=${page}`);
        return response.data;
    },

    // Get single report
    getReport: async (id: string | number) => {
        const response = await api.get(`/department-reports/${id}`);
        return response.data;
    },

    // Create report
    createReport: async (data: StoreDeptReportPayload) => {
        const response = await api.post('/department-reports', data);
        return response.data;
    },

    // Update report
    updateReport: async (id: string | number, data: Partial<StoreDeptReportPayload>) => {
        const response = await api.put(`/department-reports/${id}`, data);
        return response.data;
    },

    // Delete draft
    deleteReport: async (id: string | number) => {
        await api.delete(`/department-reports/${id}`);
    }
};
