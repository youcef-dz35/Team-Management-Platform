import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { deptReportsApi, StoreDeptReportPayload } from '../lib/api/departmentReports';
import { useNavigate } from 'react-router-dom';

export function useDepartmentReports() {
    const queryClient = useQueryClient();
    const navigate = useNavigate();

    const useGetReports = (page = 1, status = '') => useQuery({
        queryKey: ['dept-reports', page, status],
        queryFn: () => deptReportsApi.getReports(page, status),
    });

    const useGetReport = (id: string) => useQuery({
        queryKey: ['dept-report', id],
        queryFn: () => deptReportsApi.getReport(id),
        enabled: !!id,
    });

    const createReport = useMutation({
        mutationFn: (data: StoreDeptReportPayload) => deptReportsApi.createReport(data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['dept-reports'] });
            navigate('/department-reports');
        },
    });

    const updateReport = useMutation({
        mutationFn: ({ id, data }: { id: string | number; data: Partial<StoreDeptReportPayload> }) =>
            deptReportsApi.updateReport(id, data),
        onSuccess: (data) => {
            queryClient.invalidateQueries({ queryKey: ['dept-reports'] });
            queryClient.invalidateQueries({ queryKey: ['dept-report', data.id] });
        },
    });

    return {
        useGetReports,
        useGetReport,
        createReport,
        updateReport,
    };
}
