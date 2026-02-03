import React, { useState } from 'react';
import { useDepartmentReports } from '../../hooks/useDepartmentReports';
import { Link } from 'react-router-dom';
import { format } from 'date-fns';

const DeptReportList = () => {
    const [page, setPage] = useState(1);
    const { useGetReports } = useDepartmentReports();
    const { data, isLoading, isError } = useGetReports(page);

    if (isLoading) return <div className="p-4">Loading reports...</div>;
    if (isError) return <div className="p-4 text-red-600">Error loading reports.</div>;

    const reports = data?.data || [];

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'submitted': return 'bg-green-100 text-green-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <div className="space-y-6">
            <div className="flex justify-between items-center">
                <h1 className="text-2xl font-bold text-gray-900">Department Allocations</h1>
                <Link
                    to="/department-reports/new"
                    className="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 text-sm font-medium"
                >
                    New Weekly Allocation
                </Link>
            </div>

            <div className="bg-white shadow overflow-hidden rounded-lg">
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Department</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody className="bg-white divide-y divide-gray-200">
                        {reports.length === 0 ? (
                            <tr>
                                <td colSpan={4} className="px-6 py-4 text-center text-sm text-gray-500">
                                    No allocation reports found.
                                </td>
                            </tr>
                        ) : (
                            reports.map((report: any) => (
                                <tr key={report.id}>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {report.department?.name || 'My Department'}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {format(new Date(report.period_start), 'MMM d')} - {format(new Date(report.period_end), 'MMM d, yyyy')}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className={`px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${getStatusColor(report.status)}`}>
                                            {report.status.toUpperCase()}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <Link to={`/department-reports/${report.id}/edit`} className="text-indigo-600 hover:text-indigo-900">
                                            Manage
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

export default DeptReportList;
