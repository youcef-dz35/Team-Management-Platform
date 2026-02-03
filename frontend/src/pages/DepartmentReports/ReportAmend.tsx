import React, { useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useForm, useFieldArray, Controller, FormProvider } from 'react-hook-form';
import { useQuery } from '@tanstack/react-query';
import { deptReportsApi, DepartmentReport } from '../../lib/api/departmentReports';
import { departmentsApi } from '../../lib/api/departments';
import { projectsApi, Project } from '../../lib/api/projects';
import { useAuthStore } from '../../store/authStore';
import { User } from '../../lib/api/projects';

type FormValues = {
    reason: string;
    comments: string;
    entries: {
        user_id: string;
        project_id: string;
        hours_allocated: number;
        notes: string;
    }[];
};

const DeptReportAmend = () => {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const user = useAuthStore((state) => state.user);

    const { data: reportData, isLoading: isLoadingReport } = useQuery<DepartmentReport>({
        queryKey: ['deptReport', id],
        queryFn: () => deptReportsApi.getReport(id!),
        enabled: !!id,
    });

    const methods = useForm<FormValues>({
        defaultValues: {
            reason: '',
            comments: '',
            entries: [],
        },
    });

    const { control, handleSubmit, reset, formState: { errors } } = methods;

    const { fields, append, remove } = useFieldArray({
        control,
        name: 'entries',
    });

    const { data: departmentUsers, isLoading: isLoadingDeptUsers } = useQuery<User[]>({
        queryKey: ['departmentUsers', user?.department?.id],
        queryFn: () => departmentsApi.getDepartmentUsers(user!.department!.id),
        enabled: !!user?.department?.id,
    });

    const { data: projects, isLoading: isLoadingProjects } = useQuery<Project[]>({
        queryKey: ['projects'],
        queryFn: () => projectsApi.getProjects(),
    });

    useEffect(() => {
        if (reportData) {
            reset({
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
            reason: data.reason,
            comments: data.comments,
            entries: data.entries.map(e => ({
                user_id: Number(e.user_id),
                project_id: Number(e.project_id),
                hours_allocated: Number(e.hours_allocated),
                notes: e.notes,
            })),
        };

        // amendReport.mutate({ id: id!, data: payload });
        console.log('amend', payload);
    };

    if (isLoadingReport) return <div>Loading...</div>;

    return (
        <FormProvider {...methods}>
            <div className="max-w-6xl mx-auto bg-white shadow rounded-lg p-6 border-l-4 border-yellow-500">
                <h1 className="text-2xl font-bold mb-2">Amend Department Report #{id}</h1>
                <p className="text-sm text-gray-500 mb-6">You are creating a permanent amendment record for this submitted report.</p>

                <form onSubmit={handleSubmit(onSubmit)}>
                    <div className="mb-6">
                        <label className="block text-sm font-medium text-gray-700 bg-yellow-50 p-2 rounded">Reason for Amendment (Required)</label>
                        <textarea
                            {...methods.register('reason', { required: 'Reason is required' })}
                            rows={2}
                            className="mt-1 block w-full border border-yellow-300 rounded-md shadow-sm p-2 focus:ring-yellow-500 focus:border-yellow-500"
                        />
                        {errors.reason && <p className="text-red-500 text-xs mt-1">{errors.reason.message}</p>}
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
                                    <tr key={field.id}>
                                        <td className="px-4 py-2">
                                            <Controller
                                                name={`entries.${index}.user_id`}
                                                control={control}
                                                rules={{ required: 'Employee is required' }}
                                                render={({ field }) => (
                                                    <select
                                                        {...field}
                                                        className="w-full border-gray-300 rounded-md shadow-sm p-1 border"
                                                        disabled={isLoadingDeptUsers || !departmentUsers}
                                                    >
                                                        <option value="">{isLoadingDeptUsers ? 'Loading...' : 'Select Employee'}</option>
                                                        {departmentUsers?.map(user => (
                                                            <option key={user.id} value={user.id}>
                                                                {user.name}
                                                            </option>
                                                        ))}
                                                    </select>
                                                )}
                                            />
                                        </td>
                                        <td className="px-4 py-2">
                                            <Controller
                                                name={`entries.${index}.project_id`}
                                                control={control}
                                                rules={{ required: 'Project is required' }}
                                                render={({ field }) => (
                                                    <select
                                                        {...field}
                                                        className="w-full border-gray-300 rounded-md shadow-sm p-1 border"
                                                        disabled={isLoadingProjects || !projects}
                                                    >
                                                        <option value="">{isLoadingProjects ? 'Loading...' : 'Select Project'}</option>
                                                        {projects?.map(project => (
                                                            <option key={project.id} value={project.id}>
                                                                {project.name}
                                                            </option>
                                                        ))}
                                                    </select>
                                                )}
                                            />
                                        </td>
                                        <td className="px-4 py-2">
                                            <input
                                                type="number"
                                                {...methods.register(`entries.${index}.hours_allocated`, { required: 'Hours are required', valueAsNumber: true, min: 0, max: 168 })}
                                                className="w-full border-gray-300 rounded-md shadow-sm p-1 border"
                                            />
                                        </td>
                                        <td className="px-4 py-2">
                                            <input
                                                type="text"
                                                {...methods.register(`entries.${index}.notes`)}
                                                className="w-full border-gray-300 rounded-md shadow-sm p-1 border"
                                            />
                                        </td>
                                        <td className="px-4 py-2 text-center">
                                            <button type="button" onClick={() => remove(index)} className="text-red-600 hover:text-red-900">
                                                &times;
                                            </button>
                                        </td>
                                    </tr>
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
                            onClick={() => navigate(`/department-reports/${id}`)}
                            className="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            className="bg-yellow-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-yellow-700"
                        >
                            Save Amendment
                        </button>
                    </div>
                </form>
            </div>
        </FormProvider>
    );
};

export default DeptReportAmend;
