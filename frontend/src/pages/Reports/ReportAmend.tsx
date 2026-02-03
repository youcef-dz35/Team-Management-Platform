import React, { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import { useReports } from '../../hooks/useReports';
import { format } from 'date-fns';

const ReportAmend = () => {
    const { id } = useParams();
    const navigate = useNavigate();
    const { useGetReport, amendReport } = useReports();
    const { data: reportData, isLoading } = useGetReport(id!);

    const [reason, setReason] = useState('');
    const [comments, setComments] = useState('');
    const [entries, setEntries] = useState<{ user_id: number; hours_worked: number; notes: string }[]>([]);

    useEffect(() => {
        if (reportData) {
            setComments(reportData.comments || '');
            setEntries(reportData.entries.map((e: any) => ({
                user_id: e.user_id,
                hours_worked: Number(e.hours_worked),
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
        setEntries([...entries, { user_id: 0, hours_worked: 0, notes: '' }]);
    };

    const removeEntry = (index: number) => {
        setEntries(entries.filter((_, i) => i !== index));
    };

    const handleSubmit = () => {
        if (!reason) {
            alert('Reason is required for amendment');
            return;
        }

        amendReport.mutate({
            id: id!,
            data: {
                reason,
                comments,
                entries: entries.map(e => ({ ...e, user_id: Number(e.user_id), hours_worked: Number(e.hours_worked) }))
            }
        });
    };

    if (isLoading) return <div>Loading...</div>;

    return (
        <div className="max-w-4xl mx-auto bg-white shadow rounded-lg p-6 border-l-4 border-yellow-500">
            <h1 className="text-2xl font-bold mb-2">Amend Report #{id}</h1>
            <p className="text-sm text-gray-500 mb-6">You are creating a permanent amendment record for this submitted report.</p>

            <div className="mb-6">
                <label className="block text-sm font-medium text-gray-700 bg-yellow-50 p-2 rounded">Reason for Amendment (Required)</label>
                <textarea
                    value={reason}
                    onChange={(e) => setReason(e.target.value)}
                    rows={2}
                    className="mt-1 block w-full border border-yellow-300 rounded-md shadow-sm p-2 focus:ring-yellow-500 focus:border-yellow-500"
                    placeholder="e.g. Corrected overtime calculation for John Doe"
                />
            </div>

            {/* Reusing Form Logic - simplified for MVP (Duplication vs Component Reuse - Duplication safer for specific amendment logic right now) */}
            <div className="mb-6">
                <label className="block text-sm font-medium text-gray-700 mb-2">Entries</label>
                <table className="min-w-full divide-y divide-gray-200 border">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">User ID</th>
                            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Hours</th>
                            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Notes</th>
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
                    onClick={() => navigate(`/reports/${id}`)}
                    className="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50"
                >
                    Cancel
                </button>
                <button
                    onClick={handleSubmit}
                    className="bg-yellow-600 py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white hover:bg-yellow-700"
                >
                    Save Amendment
                </button>
            </div>
        </div>
    );
};

export default ReportAmend;
