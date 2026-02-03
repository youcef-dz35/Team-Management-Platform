import React, { useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useForm, useFieldArray, FormProvider } from 'react-hook-form';
import { useQuery } from '@tanstack/react-query';
import { deptReportsApi, DepartmentReport } from '../../lib/api/departmentReports';
import { departmentsApi } from '../../lib/api/departments';
import { projectsApi, Project } from '../../lib/api/projects';
import { useAuthStore } from '../../store/authStore';
import { format, startOfWeek, endOfWeek } from 'date-fns';
import { User } from '../../lib/api/projects';
import { EmployeeEntryRow } from '../../components/reports/EmployeeEntryRow';

type FormValues = {
    periodStart: string;
    periodEnd: string;
    comments: string;
    entries: {
        user_id: string;
        project_id: string;
        hours_allocated: number;
        notes: string;
    }[];
};

const DeptReportForm = () => {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const isEdit = !!id;
    const user = useAuthStore((state) => state.user);

    const { data: reportData, isLoading: isLoadingReport } = useQuery<DepartmentReport>({
        queryKey: ['deptReport', id],
        queryFn: () => deptReportsApi.getReport(id!),
        enabled: isEdit,
    });

    const methods = useForm<FormValues>({
        defaultValues: {
            periodStart: format(startOfWeek(new Date(), { weekStartsOn: 1 }), 'yyyy-MM-dd'),
            periodEnd: format(endOfWeek(new Date(), { weekStartsOn: 1 }), 'yyyy-MM-dd'),
            comments: '',
            entries: [{ user_id: '', project_id: '', hours_allocated: 0, notes: '' }],
        },
    });

    const { control, handleSubmit, reset, formState: { errors } } = methods;

    const { fields, append, remove } = useFieldArray({
        control,
        name: 'entries',
    });

    useEffect(() => {
        if (reportData) {
            reset({
                periodStart: reportData.period_start,
                periodEnd: reportData.period_end,
                comments: reportData.comments || '',
                entries: reportData.entries.map(e => ({
                    user_id: e.user_id.toString(),
                    project_id: e.project_id.toString(),
                    hours_allocated: Number(e.hours_allocated),
                    notes: e.notes || '',
                })),
            });
        }
    }, [reportData, reset]);

    const onSubmit = (data: FormValues) => {
        const payload = {
            department_id: user!.department!.id,
            period_start: data.periodStart,
            period_end: data.periodEnd,
            comments: data.comments,
            entries: data.entries.map(e => ({
                user_id: Number(e.user_id),
                project_id: Number(e.project_id),
                hours_allocated: Number(e.hours_allocated),
                notes: e.notes,
            })),
        };

        if (isEdit) {
            // updateReport.mutate({ id: id!, data: payload });
            console.log('update', payload);
        } else {
            // createReport.mutate(payload);
            console.log('create', payload);
        }
    };

    if (isLoadingReport) return <div>Loading...</div>;

    return (
        <FormProvider {...methods}>
            <div className="max-w-6xl mx-auto bg-white shadow rounded-lg p-6">
                <h1 className="text-2xl font-bold mb-6 text-indigo-900">{isEdit ? 'Edit Allocations' : 'New Weekly Allocation'}</h1>

                <form onSubmit={handleSubmit(onSubmit)}>
                    <div className="grid grid-cols-2 gap-4 mb-6 bg-indigo-50 p-4 rounded-lg">
                        <div>
                            <label className="block text-sm font-medium text-indigo-900">Start Date</label>
                            <input
                                type="date"
                                {...methods.register('periodStart', { required: 'Start date is required' })}
                                className="mt-1 block w-full border border-indigo-200 rounded-md shadow-sm p-2"
                            />
                            {errors.periodStart && <p className="text-red-500 text-xs mt-1">{errors.periodStart.message}</p>}
                        </div>
                        <div>
                            <label className="block text-sm font-medium text-indigo-900">End Date</label>
                            <input
                                type="date"
                                {...methods.register('periodEnd', { required: 'End date is required' })}
                                className="mt-1 block w-full border border-indigo-200 rounded-md shadow-sm p-2"
                            />
                            {errors.periodEnd && <p className="text-red-500 text-xs mt-1">{errors.periodEnd.message}</p>}
                        </div>
                    </div>

                    <div className="mb-6">
                        <label className="block text-sm font-medium text-gray-700 mb-2">Resource Allocations</label>
                        <table className="min-w-full divide-y divide-gray-200 border">
                            <thead className="bg-gray-50">
                                <tr>
                                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Employee</th>
                                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
                                    <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                                    <th className="px-4 py-2"></th>
                                </tr>
                            </thead>
                            <tbody className="bg-white divide-y divide-gray-200">
                                {fields.map((field, index) => (
                                    <EmployeeEntryRow
                                        key={field.id}
                                        index={index}
                                        onRemove={() => remove(index)}
                                    />
                                ))}
                            </tbody>
                        </table>
                        <button type="button" onClick={() => append({ user_id: '', project_id: '', hours_allocated: 0, notes: '' })} className="mt-2 text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                            + Add Allocation Line
                        </button>
                    </div>

                    <div className="flex justify-end gap-4 border-t pt-4">
                        <button
                            type="button"
                            onClick={() => navigate('/department-reports')}
                            className="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            className="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700"
                        >
                            Save Allocation
                        </button>
                    </div>
                </form>
            </div>
        </FormProvider>
    );
};

export default DeptReportForm;
