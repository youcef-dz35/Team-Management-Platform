import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useReports } from '../../hooks/useReports';
import { useAuthStore } from '../../store/authStore';
import { format, startOfWeek, endOfWeek, addWeeks } from 'date-fns';

const ReportForm = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const isEdit = !!id;

    const { createReport, updateReport, useGetReport } = useReports();
    const { data: reportData, isLoading: isLoadingReport } = isEdit ? useGetReport(id) : { data: null, isLoading: false };
    const user = useAuthStore((state) => state.user);

    // Form State
    const [projectId, setProjectId] = useState('');
    const [periodStart, setPeriodStart] = useState('');
    const [periodEnd, setPeriodEnd] = useState('');
    const [comments, setComments] = useState('');
    const [entries, setEntries] = useState<{ user_id: number; hours_worked: number; notes: string }[]>([
        { user_id: 0, hours_worked: 0, notes: '' } // Initial empty row
    ]);

    // Load data on edit
    useEffect(() => {
        if (reportData) {
            setProjectId(reportData.project_id.toString());
            setPeriodStart(reportData.period_start);
            setPeriodEnd(reportData.period_end);
            setComments(reportData.comments || '');
            setEntries(reportData.entries.map((e: any) => ({
                user_id: e.user_id,
                hours_worked: Number(e.hours_worked),
                notes: e.notes || ''
            })));
        } else {
            // Default to current week
            const now = new Date();
            const start = startOfWeek(now, { weekStartsOn: 1 }); // Monday
            const end = endOfWeek(now, { weekStartsOn: 1 });   // Sunday
            setPeriodStart(format(start, 'yyyy-MM-dd'));
            setPeriodEnd(format(end, 'yyyy-MM-dd'));
        }
    }, [reportData]);

    const handleEntryChange = (index: number, field: string, value: any) => {
        const newEntries = [...entries];
        newEntries[index] = { ...newEntries[index], [field]: value };
        setEntries(newEntries);
    };

    const addEntry = () => {
        setEntries([...entries, { user_id: 0, hours_worked: 0, notes: '' }]);
    };

    const removeEntry = (index: number) => {
        setEntries(entries.filter((_, i) => i !== index));
    };

    const handleSubmit = (status: 'draft' | 'submitted') => {
        const payload = {
            project_id: Number(projectId), // In real app, this might come from User's assigned project automatically
            period_start: periodStart,
            period_end: periodEnd,
            comments,
            status,
            entries: entries.map(e => ({ ...e, user_id: Number(e.user_id), hours_worked: Number(e.hours_worked) }))
        };

        if (isEdit) {
            updateReport.mutate({ id: id!, data: payload });
        } else {
            createReport.mutate(payload);
        }
    };

    if (isEdit && isLoadingReport) return <div>Loading...</div>;

    return (
        <div className="max-w-4xl mx-auto bg-white shadow rounded-lg p-6">
            <h1 className="text-2xl font-bold mb-6">{isEdit ? 'Edit Report' : 'New Weekly Report'}</h1>

            <div className="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <label className="block text-sm font-medium text-gray-700">Project ID (Temporary)</label>
                    <input
                        type="number"
                        value={projectId}
                        onChange={(e) => setProjectId(e.target.value)}
                        className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                        placeholder="Enter Project ID (e.g. 1)"
                    />
                    <p className="text-xs text-gray-500 mt-1">Found in DB. Use '1' for testing.</p>
                </div>

                <div className="grid grid-cols-2 gap-4">
                    <div>
                        <label className="block text-sm font-medium text-gray-700">Start Date</label>
                        <input
                            type="date"
                            value={periodStart}
                            onChange={(e) => setPeriodStart(e.target.value)}
                            className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                        />
                    </div>
                    <div>
                        <label className="block text-sm font-medium text-gray-700">End Date</label>
                        <input
                            type="date"
                            value={periodEnd}
                            onChange={(e) => setPeriodEnd(e.target.value)}
                            className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                        />
                    </div>
                </div>
            </div>

            <div className="mb-6">
                <label className="block text-sm font-medium text-gray-700 mb-2">Detailed Entries</label>
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
                        {entries.map((entry, index) => (
                            <tr key={index}>
                                <td className="px-4 py-2">
                                    <input
                                        type="number"
                                        value={entry.user_id}
                                        onChange={(e) => handleEntryChange(index, 'user_id', e.target.value)}
                                        className="w-full border-gray-300 rounded-md shadow-sm p-1 border"
                                        placeholder="ID"
                                    />
                                </td>
                                <td className="px-4 py-2">
                                    <input
                                        type="number"
                                        value={entry.hours_worked}
                                        onChange={(e) => handleEntryChange(index, 'hours_worked', e.target.value)}
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
                <button onClick={addEntry} className="mt-2 text-sm text-blue-600 hover:text-blue-900">
                    + Add Entry
                </button>
            </div>

            <div className="mb-6">
                <label className="block text-sm font-medium text-gray-700">Comments</label>
                <textarea
                    value={comments}
                    onChange={(e) => setComments(e.target.value)}
                    rows={4}
                    className="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2"
                />
            </div>

            <div className="flex justify-end gap-4">
                <button
                    onClick={() => navigate('/reports')}
                    className="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Cancel
                </button>
                <button
                    onClick={() => handleSubmit('draft')}
                    className="bg-gray-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-gray-700"
                >
                    Save Draft
                </button>
                {/* Only show submit if it's already saved? Or allow direct submit? Simplification: allow direct. */}
            </div>
        </div>
    );
};

export default ReportForm;
