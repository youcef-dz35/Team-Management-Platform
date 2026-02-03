import React from 'react';
import { useParams, Link } from 'react-router-dom';
import { useReports } from '../../hooks/useReports';
import { format } from 'date-fns';

const ReportView = () => {
    const { id } = useParams();
    const { useGetReport, submitReport } = useReports();
    const { data: report, isLoading } = useGetReport(id!);

    if (isLoading) return <div>Loading...</div>;
    if (!report) return <div>Report not found</div>;

    return (
        <div className="max-w-4xl mx-auto space-y-6">
            {/* Header */}
            <div className="bg-white shadow rounded-lg p-6 flex justify-between items-start">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Report #{report.id}</h1>
                    <p className="text-sm text-gray-500">
                        {report.project?.name} | {format(new Date(report.reporting_period_start), 'MMM d')} - {format(new Date(report.reporting_period_end), 'MMM d, yyyy')}
                    </p>
                </div>
                <div className="flex gap-2">
                    <span className={`px-2 py-1 rounded-full text-xs font-semibold ${report.status === 'submitted' ? 'bg-green-100 text-green-800' :
                            report.status === 'amended' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'
                        }`}>
                        {report.status.toUpperCase()}
                    </span>

                    {report.status === 'draft' && (
                        <>
                            <Link to={`/reports/${report.id}/edit`} className="bg-gray-100 text-gray-700 px-3 py-1 rounded hover:bg-gray-200 text-sm">Edit</Link>
                            <button
                                onClick={() => submitReport.mutate(report.id)}
                                className="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 text-sm"
                            >
                                Submit
                            </button>
                        </>
                    )}

                    {report.status === 'submitted' && (
                        <Link to={`/reports/${report.id}/amend`} className="bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-700 text-sm">
                            Amend
                        </Link>
                    )}
                </div>
            </div>

            {/* Overview */}
            <div className="bg-white shadow rounded-lg p-6">
                <h3 className="text-lg font-medium text-gray-900 mb-4">Summary</h3>
                <p className="text-gray-700">{report.comments || 'No comments provided.'}</p>
            </div>

            {/* Entries */}
            <div className="bg-white shadow rounded-lg p-6">
                <h3 className="text-lg font-medium text-gray-900 mb-4">Entries</h3>
                <table className="min-w-full divide-y divide-gray-200">
                    <thead className="bg-gray-50">
                        <tr>
                            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Employee</th>
                            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Hours</th>
                            <th className="px-4 py-2 text-left text-xs font-medium text-gray-500">Notes</th>
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-gray-200">
                        {report.entries.map((entry: any) => (
                            <tr key={entry.id}>
                                <td className="px-4 py-2 text-sm text-gray-900">{entry.user?.name || `User #${entry.user_id}`}</td>
                                <td className="px-4 py-2 text-sm text-gray-500">{entry.hours_worked}</td>
                                <td className="px-4 py-2 text-sm text-gray-500">{entry.notes}</td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>

            {/* Amendments History */}
            {report.amendments && report.amendments.length > 0 && (
                <div className="bg-white shadow rounded-lg p-6">
                    <h3 className="text-lg font-medium text-gray-900 mb-4">Amendment History</h3>
                    <div className="space-y-4">
                        {report.amendments.map((amendment: any) => (
                            <div key={amendment.id} className="border-l-4 border-yellow-400 pl-4 py-2 bg-gray-50">
                                <p className="text-sm font-semibold text-gray-900">
                                    Amended by {amendment.user?.name} on {format(new Date(amendment.created_at), 'MMM d, yyyy HH:mm')}
                                </p>
                                <p className="text-sm text-gray-700 mt-1">Reason: {amendment.reason}</p>
                            </div>
                        ))}
                    </div>
                </div>
            )}
        </div>
    );
};

export default ReportView;
