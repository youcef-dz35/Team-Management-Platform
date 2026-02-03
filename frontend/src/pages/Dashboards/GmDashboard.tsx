import React from 'react';
import { Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import api from '../../lib/axios';
import { LoadingSpinner } from '../../components/common/LoadingSpinner';

interface GmDashboardData {
    conflicts: {
        total: number;
        open: number;
        escalated: number;
        resolved: number;
    };
    reports: {
        projectReports: {
            total: number;
            thisWeek: number;
        };
        departmentReports: {
            total: number;
            thisWeek: number;
        };
    };
    recentConflicts: Array<{
        id: number;
        employee_name: string;
        department_name: string;
        discrepancy: number;
        status: string;
        created_at: string;
    }>;
    teamOverview: {
        totalEmployees: number;
        totalDepartments: number;
        totalProjects: number;
    };
}

const GmDashboard: React.FC = () => {
    const { data, isLoading, error } = useQuery<GmDashboardData>({
        queryKey: ['gm-dashboard'],
        queryFn: async () => {
            const response = await api.get('/dashboard/gm');
            return response.data;
        },
    });

    if (isLoading) {
        return (
            <div className="flex justify-center items-center h-64">
                <LoadingSpinner size="lg" text="Loading dashboard..." />
            </div>
        );
    }

    if (error) {
        return (
            <div className="bg-red-50 border border-red-200 rounded-lg p-4">
                <p className="text-red-700">Failed to load dashboard data. Please try again.</p>
            </div>
        );
    }

    const stats = data || {
        conflicts: { total: 0, open: 0, escalated: 0, resolved: 0 },
        reports: {
            projectReports: { total: 0, thisWeek: 0 },
            departmentReports: { total: 0, thisWeek: 0 },
        },
        recentConflicts: [],
        teamOverview: { totalEmployees: 0, totalDepartments: 0, totalProjects: 0 },
    };

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <h1 className="text-2xl font-bold text-gray-900">GM Dashboard</h1>
                <div className="flex gap-3">
                    <Link
                        to="/conflicts"
                        className="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 text-sm font-medium"
                    >
                        View Conflicts ({stats.conflicts.open + stats.conflicts.escalated})
                    </Link>
                </div>
            </div>

            {/* Conflict Alert Banner */}
            {(stats.conflicts.open > 0 || stats.conflicts.escalated > 0) && (
                <div className={`rounded-lg p-4 ${stats.conflicts.escalated > 0 ? 'bg-red-100' : 'bg-yellow-100'}`}>
                    <div className="flex items-center gap-3">
                        <svg className={`w-6 h-6 ${stats.conflicts.escalated > 0 ? 'text-red-600' : 'text-yellow-600'}`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <div>
                            <p className={`font-medium ${stats.conflicts.escalated > 0 ? 'text-red-800' : 'text-yellow-800'}`}>
                                {stats.conflicts.escalated > 0
                                    ? `${stats.conflicts.escalated} ESCALATED conflict(s) require immediate attention`
                                    : `${stats.conflicts.open} open conflict(s) need review`
                                }
                            </p>
                            <Link to="/conflicts" className={`text-sm underline ${stats.conflicts.escalated > 0 ? 'text-red-700' : 'text-yellow-700'}`}>
                                Review now
                            </Link>
                        </div>
                    </div>
                </div>
            )}

            {/* Stats Cards - Row 1: Conflicts */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div className="bg-white rounded-lg shadow p-6">
                    <div className="text-sm text-gray-500 mb-1">Total Conflicts</div>
                    <div className="text-3xl font-bold text-gray-900">{stats.conflicts.total}</div>
                </div>
                <div className="bg-red-50 rounded-lg shadow p-6">
                    <div className="text-sm text-red-600 mb-1">Escalated</div>
                    <div className="text-3xl font-bold text-red-700">{stats.conflicts.escalated}</div>
                    <div className="text-xs text-red-500 mt-1">Needs immediate action</div>
                </div>
                <div className="bg-yellow-50 rounded-lg shadow p-6">
                    <div className="text-sm text-yellow-600 mb-1">Open</div>
                    <div className="text-3xl font-bold text-yellow-700">{stats.conflicts.open}</div>
                    <div className="text-xs text-yellow-500 mt-1">Awaiting review</div>
                </div>
                <div className="bg-green-50 rounded-lg shadow p-6">
                    <div className="text-sm text-green-600 mb-1">Resolved</div>
                    <div className="text-3xl font-bold text-green-700">{stats.conflicts.resolved}</div>
                </div>
            </div>

            {/* Stats Cards - Row 2: Reports */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div className="bg-blue-50 rounded-lg shadow p-6">
                    <div className="text-sm text-blue-600 mb-1">Source A (Project Reports)</div>
                    <div className="text-3xl font-bold text-blue-700">{stats.reports.projectReports.total}</div>
                    <div className="text-xs text-blue-500 mt-1">{stats.reports.projectReports.thisWeek} this week</div>
                </div>
                <div className="bg-purple-50 rounded-lg shadow p-6">
                    <div className="text-sm text-purple-600 mb-1">Source B (Dept Reports)</div>
                    <div className="text-3xl font-bold text-purple-700">{stats.reports.departmentReports.total}</div>
                    <div className="text-xs text-purple-500 mt-1">{stats.reports.departmentReports.thisWeek} this week</div>
                </div>
                <div className="bg-gray-50 rounded-lg shadow p-6">
                    <div className="text-sm text-gray-600 mb-1">Team Overview</div>
                    <div className="text-sm text-gray-700 mt-2 space-y-1">
                        <div>{stats.teamOverview.totalEmployees} Employees</div>
                        <div>{stats.teamOverview.totalDepartments} Departments</div>
                        <div>{stats.teamOverview.totalProjects} Active Projects</div>
                    </div>
                </div>
            </div>

            {/* Recent Conflicts */}
            <div className="bg-white rounded-lg shadow">
                <div className="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h2 className="text-lg font-semibold text-gray-900">Recent Conflicts</h2>
                    <Link to="/conflicts" className="text-sm text-blue-600 hover:text-blue-800">View all</Link>
                </div>
                <div className="divide-y divide-gray-200">
                    {stats.recentConflicts.length === 0 ? (
                        <div className="p-6 text-center text-gray-500">
                            No conflicts detected. The dual-reporting system is in sync!
                        </div>
                    ) : (
                        stats.recentConflicts.map((conflict) => (
                            <Link
                                key={conflict.id}
                                to={`/conflicts/${conflict.id}`}
                                className={`flex items-center justify-between p-4 hover:bg-gray-50 ${
                                    conflict.status === 'escalated' ? 'bg-red-50' : ''
                                }`}
                            >
                                <div>
                                    <div className="font-medium text-gray-900">{conflict.employee_name}</div>
                                    <div className="text-sm text-gray-500">{conflict.department_name}</div>
                                </div>
                                <div className="flex items-center gap-3">
                                    <span className={`text-sm font-medium ${
                                        Math.abs(conflict.discrepancy) >= 10
                                            ? 'text-red-600'
                                            : 'text-yellow-600'
                                    }`}>
                                        {conflict.discrepancy > 0 ? '+' : ''}{conflict.discrepancy}h
                                    </span>
                                    <span
                                        className={`px-2 py-1 text-xs rounded-full ${
                                            conflict.status === 'escalated'
                                                ? 'bg-red-100 text-red-700'
                                                : conflict.status === 'open'
                                                ? 'bg-yellow-100 text-yellow-700'
                                                : 'bg-green-100 text-green-700'
                                        }`}
                                    >
                                        {conflict.status.toUpperCase()}
                                    </span>
                                </div>
                            </Link>
                        ))
                    )}
                </div>
            </div>
        </div>
    );
};

export default GmDashboard;
