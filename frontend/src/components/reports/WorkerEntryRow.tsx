import React from 'react';
import { useFormContext, Controller } from 'react-hook-form';
import { useQuery } from '@tanstack/react-query';
import { projectsApi, User } from '../../lib/api/projects';

interface WorkerEntryRowProps {
    index: number;
    projectId: string;
    onRemove: () => void;
}

export const WorkerEntryRow: React.FC<WorkerEntryRowProps> = ({ index, projectId, onRemove }) => {
    const { register, control, formState: { errors } } = useFormContext();

    const { data: projectUsers, isLoading: isLoadingUsers } = useQuery<User[]>({
        queryKey: ['projectUsers', projectId],
        queryFn: () => projectsApi.getProjectUsers(projectId),
        enabled: !!projectId,
    });

    return (
        <tr>
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
                {errors.entries?.[index]?.user_id && <p className="text-red-500 text-xs mt-1">{errors.entries[index].user_id.message}</p>}
            </td>
            <td className="px-4 py-2">
                <input
                    type="number"
                    {...register(`entries.${index}.hours_worked`, { required: 'Hours are required', valueAsNumber: true, min: { value: 0, message: 'Must be positive' }, max: { value: 168, message: 'Max 168' } })}
                    className="w-full border-gray-300 rounded-md shadow-sm p-1 border"
                />
                {errors.entries?.[index]?.hours_worked && <p className="text-red-500 text-xs mt-1">{errors.entries[index].hours_worked.message}</p>}
            </td>
            <td className="px-4 py-2">
                <input
                    type="text"
                    {...register(`entries.${index}.notes`)}
                    className="w-full border-gray-300 rounded-md shadow-sm p-1 border"
                />
            </td>
            <td className="px-4 py-2 text-center">
                <button type="button" onClick={onRemove} className="text-red-600 hover:text-red-900">
                    &times;
                </button>
            </td>
        </tr>
    );
};
