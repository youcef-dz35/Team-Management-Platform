import React from 'react';
import { useFormContext, Controller } from 'react-hook-form';
import { useQuery } from '@tanstack/react-query';
import { departmentsApi } from '../../lib/api/departments';
import { projectsApi, Project } from '../../lib/api/projects';
import { useAuthStore } from '../../store/authStore';
import { User } from '../../lib/api/projects';

interface EmployeeEntryRowProps {
    index: number;
    onRemove: () => void;
}

export const EmployeeEntryRow: React.FC<EmployeeEntryRowProps> = ({ index, onRemove }) => {
    const { register, control, formState: { errors } } = useFormContext();
    const user = useAuthStore((state) => state.user);

    const { data: departmentUsers, isLoading: isLoadingDeptUsers } = useQuery<User[]>({
        queryKey: ['departmentUsers', user?.department?.id],
        queryFn: () => departmentsApi.getDepartmentUsers(user!.department!.id),
        enabled: !!user?.department?.id,
    });

    const { data: projects, isLoading: isLoadingProjects } = useQuery<Project[]>({
        queryKey: ['projects'],
        queryFn: () => projectsApi.getProjects(),
    });

    return (
        <tr>
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
                    {...register(`entries.${index}.hours_allocated`, { required: 'Hours are required', valueAsNumber: true, min: 0, max: 168 })}
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
                <button type="button" onClick={onRemove} className="text-red-600 hover:text-red-900">
                    &times;
                </button>
            </td>
        </tr>
    );
};
