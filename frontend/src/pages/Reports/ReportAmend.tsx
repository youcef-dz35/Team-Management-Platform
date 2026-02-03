import React, { useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useForm, useFieldArray, Controller } from 'react-hook-form';
import { useQuery } from '@tanstack/react-query';
import { reportsApi, ProjectReport } from '../../lib/api/reports';
import { projectsApi, User } from '../../lib/api/projects';

type FormValues = {
    reason: string;
    comments: string;
    entries: {
        user_id: string;
        hours_worked: number;
        notes: string;
    }[];
};

const ReportAmend = () => {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();

    const { data: reportData, isLoading: isLoadingReport } = useQuery<ProjectReport>({
        queryKey: ['report', id],
        queryFn: () => reportsApi.getReport(id!),
        enabled: !!id,
    });

    const { register, handleSubmit, control, reset, formState: { errors } } = useForm<FormValues>({
        defaultValues: {
            reason: '',
            comments: '',
            entries: [],
        },
    });

    const { fields, append, remove } = useFieldArray({
        control,
        name: 'entries',
    });

    const { data: projectUsers, isLoading: isLoadingUsers } = useQuery<User[]>({
        queryKey: ['projectUsers', reportData?.project_id],
        queryFn: () => projectsApi.getProjectUsers(reportData!.project_id),
        enabled: !!reportData,
    });

    useEffect(() => {
        if (reportData) {
            reset({
                comments: reportData.comments || '',
                entries: reportData.entries.map(e => ({
                    user_id: e.user_id.toString(),
                    hours_worked: Number(e.hours_worked),
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
                hours_worked: Number(e.hours_worked),
                notes: e.notes,
            })),
        };

        // amendReport.mutate({ id: id!, data: payload });
        console.log('amend', payload);
    };

    if (isLoadingReport) return <div>Loading...</div>;

    return (
        <div className="max-w-4xl mx-auto bg-white shadow rounded-lg p-6 border-l-4 border-yellow-500">
            <h1 className="text-2xl font-bold mb-2">Amend Report #{id}</h1>
            <p className="text-sm text-gray-500 mb-6">You are creating a permanent amendment record for this submitted report.</p>

            <form onSubmit={handleSubmit(onSubmit)}>
                <div className="mb-6">
                    <label className="block text-sm font-medium text-gray-700 bg-yellow-50 p-2 rounded">Reason for Amendment (Required)</label>
                    <textarea
                        {...register('reason', { required: 'Reason is required' })}
                        rows={2}
                        className="mt-1 block w-full border border-yellow-300 rounded-md shadow-sm p-2 focus:ring-yellow-500 focus:border-yellow-500"
                    />
                    {errors.reason && <p className="text-red-500 text-xs mt-1">{errors.reason.message}</p>}
                </div>

                <div className="mb-6">
                    <label className="block text-sm font-medium text-gray-700 mb-2">Entries</label>
                    <table className="min-w-full divide-y divide-gray-200 border">
                        <thead className="bg-gray-50">
                            <tr>
                                <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
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
                                            rules={{ required: 'User is required' }}
                                            render={({ field }) => (
                                                <select
                                                    {...field}
                                                    className="w-full border-gray-300 rounded-md shadow-sm p-1 border"
                                                    disabled={isLoadingUsers || !projectUsers}
                                                >
                                                    <option value="">{isLoadingUsers ? 'Loading...' : 'Select User'}</option>
                                                    {projectUsers?.map(user => (
                                                        <option key={user.id} value={user.id}>
                                                            {user.name}
                                                        </option>
                                                    ))}
                                                </select>
                                            )}
                                        />
                                    </td>
                                    <td className="px-4 py-2">
                                        <input
                                            type="number"
                                            {...register(`entries.${index}.hours_worked`, { required: 'Hours are required', valueAsNumber: true, min: 0, max: 168 })}
                                            className="w-full border-gray-300 rounded-md shadow-sm p-1 border"
                                        />
                                    </td>
                                    <td className="px-4 py-2">
                                        <input
                                            type="text"
                                            {...register(`entries.${index}.notes`)}
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
                    <button type="button" onClick={() => append({ user_id: '', hours_worked: 0, notes: '' })} className="mt-2 text-sm text-blue-600 hover:text-blue-900">
                        + Add Entry
                    </button>
                </div>

                <div className="mb-6">
                    <label className="block text-sm font-medium text-gray-700">Comments</label>
                    <textarea
                        {...register('comments')}
                        rows={4}
                        className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                    />
                </div>

                <div className="flex justify-end gap-4">
                    <button
                        type="button"
                        onClick={() => navigate(`/reports/${id}`)}
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
    );
};

export default ReportAmend;
