import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { reportsApi, StoreReportPayload, UpdateReportPayload, AmendReportPayload } from '../lib/api/reports';
import { useNavigate } from 'react-router-dom';

export function useReports() {
    const queryClient = useQueryClient();
    const navigate = useNavigate();

    // Query: Get All Reports
    const useGetReports = (page = 1) => useQuery({
        queryKey: ['reports', page],
        queryFn: () => reportsApi.getReports(page),
    });

    // Query: Get Single Report
    const useGetReport = (id: string) => useQuery({
        queryKey: ['report', id],
        queryFn: () => reportsApi.getReport(id),
        enabled: !!id,
    });

    // Mutation: Create Report
    const createReport = useMutation({
        mutationFn: (data: StoreReportPayload) => reportsApi.createReport(data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['reports'] });
            navigate('/reports');
        },
    });

    // Mutation: Update Report
    const updateReport = useMutation({
        mutationFn: ({ id, data }: { id: string | number; data: UpdateReportPayload }) =>
            reportsApi.updateReport(id, data),
        onSuccess: (data) => {
            queryClient.invalidateQueries({ queryKey: ['reports'] });
            queryClient.invalidateQueries({ queryKey: ['report', data.id] });
            // Don't auto-navigate, might want to keep editing
        },
    });

    // Mutation: Submit Report
    const submitReport = useMutation({
        mutationFn: (id: string | number) => reportsApi.submitReport(id),
        onSuccess: (data) => {
            queryClient.invalidateQueries({ queryKey: ['reports'] });
            queryClient.invalidateQueries({ queryKey: ['report', data.report.id] }); // Backend returns { report: ... } or just report? Check controller.
            // Controller returns: ['message' => ..., 'report' => ...]
            navigate('/reports');
        },
    });

    // Mutation: Amend Report
    const amendReport = useMutation({
        mutationFn: ({ id, data }: { id: string | number; data: AmendReportPayload }) =>
            reportsApi.amendReport(id, data),
        onSuccess: (data) => {
            queryClient.invalidateQueries({ queryKey: ['reports'] });
            queryClient.invalidateQueries({ queryKey: ['report', data.id] });
            navigate(`/reports/${data.id}`);
        },
    });

    return {
        useGetReports,
        useGetReport,
        createReport,
        updateReport,
        submitReport,
        amendReport,
    };
}
