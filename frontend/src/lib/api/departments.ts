import api from '../axios';
import { User } from './projects';

export interface Department {
    id: number;
    name: string;
}

export const departmentsApi = {
    // Get all departments
    getDepartments: async (): Promise<Department[]> => {
        const response = await api.get('/departments');
        return response.data;
    },

    // Get users for a department
    getDepartmentUsers: async (departmentId: string | number): Promise<User[]> => {
        const response = await api.get(`/departments/${departmentId}/employees`);
        return response.data;
    },
};
