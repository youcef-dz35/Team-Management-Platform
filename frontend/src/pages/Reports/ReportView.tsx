import React, { useState } from 'react';
import { useParams, Link } from 'react-router-dom';
import { useReports } from '../../hooks/useReports';
import { format } from 'date-fns';

const ReportView = () => {
    const { id } = useParams();
    const { useGetReport, submitReport, amendReport } = useReports();
    const { data: report, isLoading } = useGetReport(id!);

    const [isAmendModalOpen, setIsAmendModalOpen] = useState(false);
    const [amendReason, setAmendReason] = useState('');
    // For amendment, we basically re-use the entries logic, but simpler for this modal: just cloning entries to state?
    // Ideally we'd reuse ReportForm in "Amend Mode".
    // For MVP, I'll just allow amending the Notes/Hours of existing entries in a raw way or redirect to an amend page?
    // Let's implement a simple Amendment Modal here that clones entries for editing.

    // Actually, standard pattern: Reuse Form.
    // But amendment requires a "Reason". 
    // Strategy: Redirect to /reports/:id/amend?

    if (isLoading) return <div>Loading...</div>;
    if (!report) return <div>Report not found</div>;

    const isSubmitted = report.status === 'submitted';

    return (
        <div className="max-w-4xl mx-auto space-y-6">
            {/* Header */}
            <div className="bg-white shadow rounded-lg p-6 flex justify-between items-start">
                <div>
                    <h1 className="text-2xl font-bold text-gray-900">Report #{report.id}</h1>
                    <p className="text-sm text-gray-500">
                        {report.project?.name} | {format(new Date(report.period_start), 'MMM d')} - {format(new Date(report.period_end), 'MMM d, yyyy')}
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
                        <button
                            onClick={() => setIsAmendModalOpen(true)}
                            className="bg-yellow-600 text-white px-3 py-1 rounded hover:bg-yellow-700 text-sm"
                        >
                            Amend
                        </button>
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

            {/* Amendment Modal (Simplified for MVP) */}
            {isAmendModalOpen && (
                <div className="fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center p-4">
                    <div className="bg-white rounded-lg p-6 max-w-lg w-full">
                        <h3 className="text-lg font-bold mb-4">Amend Report</h3>
                        <p className="text-sm text-gray-500 mb-4">
                            Note: A full amendment interface would go here. For this MVP, please implement the Amend Form properly or reuse the Edit form with 'amend' mode.
                            <br />
                            (Placeholder: Only supporting reason entry + simplistic cloning for now)
                        </p>
                        {/* In a real app, I'd render the ReportForm here pre-filled, but wrapped in an amendment context. */}

                        <div className="mt-4 flex justify-end gap-2">
                            <button
                                onClick={() => setIsAmendModalOpen(false)}
                                className="bg-gray-100 text-gray-700 px-4 py-2 rounded"
                            >
                                Cancel
                            </button>
                            {/* Redirect to a dedicated amend route would be better */}
                            <Link
                                to={`/reports/${report.id}/amend`}
                                className="bg-blue-600 text-white px-4 py-2 rounded"
                            >
                                Proceed to Amendment Form
                            </Link>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

export default ReportView;
