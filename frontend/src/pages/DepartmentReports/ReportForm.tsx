import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useDepartmentReports } from '../../hooks/useDepartmentReports';
import { useAuthStore } from '../../store/authStore';
import { format, startOfWeek, endOfWeek } from 'date-fns';
import api from '../../lib/axios'; // Direct access for fetching options if needed

const DeptReportForm = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const isEdit = !!id;
    const user = useAuthStore((state) => state.user);

    const { createReport, updateReport, useGetReport } = useDepartmentReports();
    const { data: reportData, isLoading } = isEdit ? useGetReport(id) : { data: null, isLoading: false };

    // Form State
    const [periodStart, setPeriodStart] = useState('');
    const [periodEnd, setPeriodEnd] = useState('');
    const [entries, setEntries] = useState<{ user_id: number; project_id: number; hours_allocated: number; notes: string }[]>([
        { user_id: 0, project_id: 0, hours_allocated: 0, notes: '' }
    ]);

    // Options State (Ideally these come from specific hooks)
    const [projectOptions, setProjectOptions] = useState<any[]>([]);
    // We need users FROM THE SAME DEPARTMENT.
    // Ideally backend provides an endpoint: /users?department_id=XXX
    // For MVP, if we don't have that, we might just use manual ID input or fetch all (not scalable).
    // Assuming specific department check is server-side, frontend UI needs to help user pick valid ones.
    // Let's rely on manual input + hint for MVP, or better: 
    // We can fetch `/api/v1/auth/me` ? returns dept id.
    // Actually, we can fetch all users and filter client side if list is small, OR add a `department-users` endpoint.
    // Let's assume for MVP manual ID entry or "User 1, User 2" is okay until we add the Dropdown hook.

    useEffect(() => {
        // Determine default dates
        if (!isEdit) {
            const now = new Date();
            const start = startOfWeek(now, { weekStartsOn: 1 });
            const end = endOfWeek(now, { weekStartsOn: 1 });
            setPeriodStart(format(start, 'yyyy-MM-dd'));
            setPeriodEnd(format(end, 'yyyy-MM-dd'));
        }
    }, [isEdit]);

    useEffect(() => {
        if (reportData) {
            setPeriodStart(reportData.period_start);
            setPeriodEnd(reportData.period_end);
            setEntries(reportData.entries.map((e: any) => ({
                user_id: e.user_id,
                project_id: e.project_id,
                hours_allocated: Number(e.hours_allocated),
                notes: e.notes || ''
            })));
        }
    }, [reportData]);

    const handleEntryChange = (index: number, field: string, value: any) => {
        const newEntries = [...entries];
        newEntries[index] = { ...newEntries[index], [field]: value };
        setEntries(newEntries);
    };

    const addEntry = () => {
        setEntries([...entries, { user_id: 0, project_id: 0, hours_allocated: 0, notes: '' }]);
    };

    const removeEntry = (index: number) => {
        setEntries(entries.filter((_, i) => i !== index));
    };

    const handleSubmit = (status: 'draft' | 'submitted') => {
        if (!user?.department?.id) {
            alert("You do not have a department assigned.");
            return;
        }

        const payload = {
            department_id: user.department.id,
            period_start: periodStart,
            period_end: periodEnd,
            status,
            entries: entries.map(e => ({
                ...e,
                user_id: Number(e.user_id),
                project_id: Number(e.project_id),
                hours_allocated: Number(e.hours_allocated)
            }))
        };

        if (isEdit) {
            updateReport.mutate({ id: id!, data: payload });
        } else {
            createReport.mutate(payload);
        }
    };

    if (isLoading) return <div>Loading...</div>;

    return (
        <div className="max-w-6xl mx-auto bg-white shadow rounded-lg p-6">
            <h1 className="text-2xl font-bold mb-6 text-indigo-900">{isEdit ? 'Edit Allocations' : 'New Weekly Allocation'}</h1>

            <div className="grid grid-cols-2 gap-4 mb-6 bg-indigo-50 p-4 rounded-lg">
                <div>
                    <label className="block text-sm font-medium text-indigo-900">Start Date</label>
                    <input
                        type="date"
                        value={periodStart}
                        onChange={(e) => setPeriodStart(e.target.value)}
                        className="mt-1 block w-full border border-indigo-200 rounded-md shadow-sm p-2"
                    />
                </div>
                <div>
                    <label className="block text-sm font-medium text-indigo-900">End Date</label>
                    <input
                        type="date"
                        value={periodEnd}
                        onChange={(e) => setPeriodEnd(e.target.value)}
                        className="mt-1 block w-full border border-indigo-200 rounded-md shadow-sm p-2"
                    />
                </div>
            </div>

            <div className="mb-6">
                <label className="block text-sm font-medium text-gray-700 mb-2">Resource Allocations</label>
                <table className="min-w-full divide-y divide-gray-200 border">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Employee ID</th>
                            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Project ID</th>
                            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Hours</th>
                            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                            <th className="px-4 py-2"></th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {entries.map((entry, index) => (
                            <tr key={index}>
                                <td className="px-4 py-2">
                                    <input
                                        type="number"
                                        value={entry.user_id}
                                        onChange={(e) => handleEntryChange(index, 'user_id', e.target.value)}
                                        className="w-full border-gray-300 rounded-md shadow-sm p-1 border"
                                        placeholder="Emp ID"
                                    />
                                </td>
                                <td className="px-4 py-2">
                                    <input
                                        type="number"
                                        value={entry.project_id}
                                        onChange={(e) => handleEntryChange(index, 'project_id', e.target.value)}
                                        className="w-full border-gray-300 rounded-md shadow-sm p-1 border"
                                        placeholder="Proj ID"
                                    />
                                </td>
                                <td className="px-4 py-2">
                                    <input
                                        type="number"
                                        value={entry.hours_allocated}
                                        onChange={(e) => handleEntryChange(index, 'hours_allocated', e.target.value)}
                                        className="w-full border-gray-300 rounded-md shadow-sm p-1 border"
                                    />
                                </td>
                                <td className="px-4 py-2">
                                    <input
                                        type="text"
                                        value={entry.notes}
                                        onChange={(e) => handleEntryChange(index, 'notes', e.target.value)}
                                        className="w-full border-gray-300 rounded-md shadow-sm p-1 border"
                                    />
                                </td>
                                <td className="px-4 py-2 text-center">
                                    <button onClick={() => removeEntry(index)} className="text-red-600 hover:text-red-900">
                                        &times;
                                    </button>
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
                <button onClick={addEntry} className="mt-2 text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                    + Add Allocation Line
                </button>
            </div>

            <div className="flex justify-end gap-4 border-t pt-4">
                <button
                    onClick={() => navigate('/department-reports')}
                    className="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Cancel
                </button>
                <button
                    onClick={() => handleSubmit('draft')}
                    className="bg-indigo-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-indigo-700"
                >
                    Save Allocation
                </button>
            </div>
        </div>
    );
};

export default DeptReportForm;
