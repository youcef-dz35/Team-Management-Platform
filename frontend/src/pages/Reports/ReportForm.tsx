import React, { useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useForm, useFieldArray, FormProvider } from 'react-hook-form';
import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { reportsApi, ProjectReport } from '../../lib/api/reports';
import { format, startOfWeek, endOfWeek } from 'date-fns';

type FormValues = {
    projectId: string;
    periodStart: string;
    periodEnd: string;
    comments: string;
    entries: {
        user_id: string;
        hours_worked: number;
        notes: string;
    }[];
};

const ReportForm = () => {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const queryClient = useQueryClient();
    const isEdit = !!id;

    const { data: reportData, isLoading: isLoadingReport } = useQuery<ProjectReport>({
        queryKey: ['report', id],
        queryFn: () => reportsApi.getReport(id!),
        enabled: isEdit,
    });

    const createMutation = useMutation({
        mutationFn: reportsApi.createReport,
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['reports'] });
            navigate('/reports');
        },
    });

    const updateMutation = useMutation({
        mutationFn: ({ id, data }: { id: string; data: any }) => reportsApi.updateReport(id, data),
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['reports'] });
            navigate('/reports');
        },
    });

    const methods = useForm<FormValues>({
        defaultValues: {
            projectId: '',
            periodStart: format(startOfWeek(new Date(), { weekStartsOn: 1 }), 'yyyy-MM-dd'),
            periodEnd: format(endOfWeek(new Date(), { weekStartsOn: 1 }), 'yyyy-MM-dd'),
            comments: '',
            entries: [{ user_id: '', hours_worked: 0, notes: '' }],
        },
    });

    const { control, reset, handleSubmit, formState: { errors } } = methods;

    const { fields, append, remove } = useFieldArray({
        control,
        name: 'entries',
    });

    useEffect(() => {
        if (reportData) {
            reset({
                projectId: reportData.project_id.toString(),
                periodStart: reportData.reporting_period_start,
                periodEnd: reportData.reporting_period_end,
                comments: reportData.comments || '',
                entries: reportData.entries?.map(e => ({
                    user_id: e.user_id.toString(),
                    hours_worked: Number(e.hours_worked),
                    notes: e.notes || '',
                })) || [{ user_id: '', hours_worked: 0, notes: '' }],
            });
        }
    }, [reportData, reset]);

    const onSubmit = (data: FormValues) => {
        const payload = {
            project_id: Number(data.projectId),
            reporting_period_start: data.periodStart,
            reporting_period_end: data.periodEnd,
            comments: data.comments,
            entries: data.entries.map(e => ({
                user_id: Number(e.user_id),
                hours_worked: Number(e.hours_worked),
                notes: e.notes,
            })),
        };

        if (isEdit) {
            updateMutation.mutate({ id: id!, data: payload });
        } else {
            createMutation.mutate(payload);
        }
    };

    if (isEdit && isLoadingReport) return <div className="p-6">Loading...</div>;

    return (
        <FormProvider {...methods}>
            <div className="max-w-4xl mx-auto bg-white shadow rounded-lg p-6">
                <h1 className="text-2xl font-bold mb-6">{isEdit ? 'Edit Report' : 'New Weekly Report'}</h1>

                <form onSubmit={handleSubmit(onSubmit)}>
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label className="block text-sm font-medium text-gray-700">Project ID</label>
                            <input
                                type="number"
                                {...methods.register('projectId', { required: 'Project is required' })}
                                className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                                placeholder="Enter Project ID"
                            />
                            {errors.projectId && <p className="text-red-500 text-xs mt-1">{errors.projectId.message}</p>}
                        </div>

                        <div className="grid grid-cols-2 gap-4">
                            <div>
                                <label className="block text-sm font-medium text-gray-700">Start Date</label>
                                <input
                                    type="date"
                                    {...methods.register('periodStart', { required: 'Start date is required' })}
                                    className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                                />
                                {errors.periodStart && <p className="text-red-500 text-xs mt-1">{errors.periodStart.message}</p>}
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700">End Date</label>
                                <input
                                    type="date"
                                    {...methods.register('periodEnd', { required: 'End date is required' })}
                                    className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                                />
                                {errors.periodEnd && <p className="text-red-500 text-xs mt-1">{errors.periodEnd.message}</p>}
                            </div>
                        </div>
                    </div>

                    <div className="mb-6">
                        <label className="block text-sm font-medium text-gray-700 mb-2">Entries</label>
                        <table className="min-w-full divide-y divide-gray-200 border">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User ID</th>
                                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
                                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                    <th className="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {fields.map((field, index) => (
                                    <tr key={field.id}>
                                        <td className="px-4 py-2">
                                            <input
                                                type="number"
                                                {...methods.register(`entries.${index}.user_id` as const)}
                                                className="w-full border-gray-300 rounded-md shadow-sm p-1 border"
                                                placeholder="User ID"
                                            />
                                        </td>
                                        <td className="px-4 py-2">
                                            <input
                                                type="number"
                                                {...methods.register(`entries.${index}.hours_worked` as const)}
                                                className="w-full border-gray-300 rounded-md shadow-sm p-1 border"
                                            />
                                        </td>
                                        <td className="px-4 py-2">
                                            <input
                                                type="text"
                                                {...methods.register(`entries.${index}.notes` as const)}
                                                className="w-full border-gray-300 rounded-md shadow-sm p-1 border"
                                            />
                                        </td>
                                        <td className="px-4 py-2 text-center">
                                            {fields.length > 1 && (
                                                <button type="button" onClick={() => remove(index)} className="text-red-600 hover:text-red-900">
                                                    &times;
                                                </button>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                        <button type="button" onClick={() => append({ user_id: '', hours_worked: 0, notes: '' })} className="mt-2 text-sm text-blue-600 hover:text-blue-900">
                            + Add Entry
                        </button>
                    </div>

                    <div className="mb-6">
                        <label className="block text-sm font-medium text-gray-700">Comments</label>
                        <textarea
                            {...methods.register('comments')}
                            rows={4}
                            className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                        />
                    </div>

                    <div className="flex justify-end gap-4">
                        <button
                            type="button"
                            onClick={() => navigate('/reports')}
                            className="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            disabled={createMutation.isPending || updateMutation.isPending}
                            className="bg-blue-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-blue-700 disabled:opacity-50"
                        >
                            {createMutation.isPending || updateMutation.isPending ? 'Saving...' : 'Save'}
                        </button>
                    </div>
                </form>
            </div>
        </FormProvider>
    );
};

export default ReportForm;
