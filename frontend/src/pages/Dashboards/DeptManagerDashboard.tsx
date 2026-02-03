import React from 'react';
import { Link } from 'react-router-dom';
import { useQuery } from '@tanstack/react-query';
import api from '../../lib/axios';
import { LoadingSpinner } from '../../components/common/LoadingSpinner';

interface DeptManagerDashboardData {
    department: {
        id: number;
        name: string;
        employee_count: number;
    };
    reports: {
        total: number;
        draft: number;
        submitted: number;
        thisWeek: number;
    };
    recentReports: Array<{
        id: number;
        period_start: string;
        period_end: string;
        status: string;
        entry_count: number;
        total_hours: number;
    }>;
}

const DeptManagerDashboard: React.FC = () => {
    const { data, isLoading, error } = useQuery<DeptManagerDashboardData>({
        queryKey: ['dept-manager-dashboard'],
        queryFn: async () => {
            const response = await api.get('/dashboard/dept-manager');
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
        department: { id: 0, name: 'Unknown', employee_count: 0 },
        reports: { total: 0, draft: 0, submitted: 0, thisWeek: 0 },
        recentReports: [],
    };

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Department Dashboard</h1>
                    <p className="text-gray-500">{stats.department.name} Department</p>
                </div>
                <Link
                    to="/department-reports/new"
                    className="bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 text-sm font-medium"
                >
                    + New Report
                </Link>
            </div>

            {/* Stats Cards */}
            <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div className="bg-white rounded-lg shadow p-6">
                    <div className="text-sm text-gray-500 mb-1">Team Size</div>
                    <div className="text-3xl font-bold text-purple-600">{stats.department.employee_count}</div>
                    <div className="text-xs text-gray-400 mt-1">employees</div>
                </div>
                <div className="bg-white rounded-lg shadow p-6">
                    <div className="text-sm text-gray-500 mb-1">Total Reports</div>
                    <div className="text-3xl font-bold text-gray-900">{stats.reports.total}</div>
                </div>
                <div className="bg-yellow-50 rounded-lg shadow p-6">
                    <div className="text-sm text-yellow-600 mb-1">Draft Reports</div>
                    <div className="text-3xl font-bold text-yellow-700">{stats.reports.draft}</div>
                    <div className="text-xs text-yellow-500 mt-1">Need to submit</div>
                </div>
                <div className="bg-green-50 rounded-lg shadow p-6">
                    <div className="text-sm text-green-600 mb-1">This Week</div>
                    <div className="text-3xl font-bold text-green-700">{stats.reports.thisWeek}</div>
                    <div className="text-xs text-green-500 mt-1">Reports submitted</div>
                </div>
            </div>

            {/* Quick Actions */}
            <div className="bg-white rounded-lg shadow p-6">
                <h2 className="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>
                <div className="flex flex-wrap gap-3">
                    <Link
                        to="/department-reports/new"
                        className="px-4 py-2 bg-purple-100 text-purple-700 rounded-md hover:bg-purple-200 text-sm"
                    >
                        Create Department Report
                    </Link>
                    <Link
                        to="/department-reports?status=draft"
                        className="px-4 py-2 bg-yellow-100 text-yellow-700 rounded-md hover:bg-yellow-200 text-sm"
                    >
                        View Drafts ({stats.reports.draft})
                    </Link>
                    <Link
                        to="/department-reports"
                        className="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm"
                    >
                        All Reports
                    </Link>
                </div>
            </div>

            {/* Recent Reports */}
            <div className="bg-white rounded-lg shadow">
                <div className="p-6 border-b border-gray-200">
                    <h2 className="text-lg font-semibold text-gray-900">Recent Reports</h2>
                </div>
                <div className="divide-y divide-gray-200">
                    {stats.recentReports.length === 0 ? (
                        <div className="p-6 text-center text-gray-500">
                            No reports yet. Create your first department report!
                        </div>
                    ) : (
                        stats.recentReports.map((report) => (
                            <Link
                                key={report.id}
                                to={`/department-reports/${report.id}`}
                                className="flex items-center justify-between p-4 hover:bg-gray-50"
                            >
                                <div>
                                    <div className="font-medium text-gray-900">
                                        Week of {report.period_start}
                                    </div>
                                    <div className="text-sm text-gray-500">
                                        {report.period_start} - {report.period_end}
                                    </div>
                                </div>
                                <div className="flex items-center gap-3">
                                    <span className="text-sm text-gray-500">
                                        {report.entry_count} entries • {report.total_hours}h
                                    </span>
                                    <span
                                        className={`px-2 py-1 text-xs rounded-full ${
                                            report.status === 'submitted'
                                                ? 'bg-green-100 text-green-700'
                                                : report.status === 'draft'
                                                ? 'bg-yellow-100 text-yellow-700'
                                                : 'bg-purple-100 text-purple-700'
                                        }`}
                                    >
                                        {report.status}
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

export default DeptManagerDashboard;
