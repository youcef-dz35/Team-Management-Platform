import React, { useState } from 'react';
import { useConflicts } from '../../hooks/useConflicts';
import { Link } from 'react-router-dom';
import { format } from 'date-fns';
import { ConflictAlert } from '../../lib/api/conflicts';

const ConflictList = () => {
    const [page, setPage] = useState(1);
    const [statusFilter, setStatusFilter] = useState<string>('');
    const { useGetConflicts, useGetStats } = useConflicts();
    const { data, isLoading, isError } = useGetConflicts(page, statusFilter || undefined);
    const { data: stats } = useGetStats();

    if (isLoading) return <div className="p-4">Loading conflict alerts...</div>;
    if (isError) return <div className="p-4 text-red-600">Error loading conflict alerts.</div>;

    const conflicts: ConflictAlert[] = data?.data || [];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'escalated': return 'bg-red-100 text-red-800';
            case 'open': return 'bg-yellow-100 text-yellow-800';
            case 'resolved': return 'bg-green-100 text-green-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    const getDiscrepancyColor = (discrepancy: number) => {
        const abs = Math.abs(discrepancy);
        if (abs >= 10) return 'text-red-600 font-bold';
        if (abs >= 5) return 'text-orange-600 font-semibold';
        return 'text-yellow-600';
    };

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <h1 className="text-2xl font-bold text-gray-900">Conflict Alerts</h1>
            </div>

            {/* Stats Cards */}
            {stats && (
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div className="bg-white p-4 rounded-lg shadow">
                        <div className="text-sm text-gray-500">Total Conflicts</div>
                        <div className="text-2xl font-bold">{stats.total}</div>
                    </div>
                    <div className="bg-red-50 p-4 rounded-lg shadow">
                        <div className="text-sm text-red-600">Escalated</div>
                        <div className="text-2xl font-bold text-red-700">{stats.escalated}</div>
                    </div>
                    <div className="bg-yellow-50 p-4 rounded-lg shadow">
                        <div className="text-sm text-yellow-600">Open</div>
                        <div className="text-2xl font-bold text-yellow-700">{stats.open}</div>
                    </div>
                    <div className="bg-green-50 p-4 rounded-lg shadow">
                        <div className="text-sm text-green-600">Resolved</div>
                        <div className="text-2xl font-bold text-green-700">{stats.resolved}</div>
                    </div>
                </div>
            )}

            {/* Filter */}
            <div className="flex items-center gap-4">
                <label className="text-sm font-medium text-gray-700">Filter by status:</label>
                <select
                    value={statusFilter}
                    onChange={(e) => { setStatusFilter(e.target.value); setPage(1); }}
                    className="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                >
                    <option value="">All</option>
                    <option value="escalated">Escalated</option>
                    <option value="open">Open</option>
                    <option value="resolved">Resolved</option>
                </select>
            </div>

            {/* Conflicts Table */}
            <div className="bg-white shadow overflow-hidden rounded-lg">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Employee</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source A (Project)</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source B (Dept)</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Discrepancy</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {conflicts.length === 0 ? (
                            <tr>
                                <td colSpan={7} className="px-6 py-4 text-center text-sm text-gray-500">
                                    No conflict alerts found.
                                </td>
                            </tr>
                        ) : (
                            conflicts.map((conflict) => (
                                <tr key={conflict.id} className={conflict.status === 'escalated' ? 'bg-red-50' : ''}>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm font-medium text-gray-900">
                                            {conflict.employee?.name || `Employee #${conflict.employee_id}`}
                                        </div>
                                        <div className="text-sm text-gray-500">
                                            {conflict.employee?.department?.name || 'Unknown Dept'}
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {format(new Date(conflict.reporting_period_start), 'MMM d')} - {format(new Date(conflict.reporting_period_end), 'MMM d, yyyy')}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {conflict.source_a_hours} hrs
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {conflict.source_b_hours} hrs
                                    </td>
                                    <td className={`px-6 py-4 whitespace-nowrap text-sm ${getDiscrepancyColor(conflict.discrepancy)}`}>
                                        {conflict.discrepancy > 0 ? '+' : ''}{conflict.discrepancy} hrs
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(conflict.status)}`}>
                                            {conflict.status.toUpperCase()}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link to={`/conflicts/${conflict.id}`} className="text-blue-600 hover:text-blue-900">
                                            {conflict.status === 'resolved' ? 'View' : 'Review'}
                                        </Link>
                                    </td>
                                </tr>
                            ))
                        )}
                    </tbody>
                </table>
            </div>
        </div>
    );
};

export default ConflictList;
