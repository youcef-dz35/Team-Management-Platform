import api from '../axios';

export interface User {
    id: number;
    name: string;
    email: string;
}

export interface Project {
    id: number;
    name: string;
}

export const projectsApi = {
    // Get all projects
    getProjects: async (): Promise<Project[]> => {
        const response = await api.get('/projects');
        return response.data;
    },

    // Get users for a project
    getProjectUsers: async (projectId: string | number): Promise<User[]> => {
        const response = await api.get(`/projects/${projectId}/assigned-users`);
        return response.data;
    },
};

