import React, { useState } from 'react';
import { useParams, useNavigate, Link } from 'react-router-dom';
import { useConflicts } from '../../hooks/useConflicts';
import { format } from 'date-fns';

const ConflictDetail = () => {
    const { id } = useParams<{ id: string }>();
    const navigate = useNavigate();
    const { useGetConflict, resolveConflict } = useConflicts();
    const { data: conflict, isLoading, isError } = useGetConflict(id || '');

    const [resolutionNotes, setResolutionNotes] = useState('');
    const [isResolving, setIsResolving] = useState(false);

    if (isLoading) return <div className="p-4">Loading conflict details...</div>;
    if (isError || !conflict) return <div className="p-4 text-red-600">Error loading conflict details.</div>;

    const handleResolve = async () => {
        if (resolutionNotes.length < 10) {
            alert('Resolution notes must be at least 10 characters.');
            return;
        }

        setIsResolving(true);
        try {
            await resolveConflict.mutateAsync({
                id: conflict.id,
                data: { resolution_notes: resolutionNotes },
            });
            navigate('/conflicts');
        } catch (error) {
            console.error('Failed to resolve conflict:', error);
            alert('Failed to resolve conflict. Please try again.');
        } finally {
            setIsResolving(false);
        }
    };

    const getStatusColor = (status: string) => {
        switch (status) {
            case 'escalated': return 'bg-red-100 text-red-800';
            case 'open': return 'bg-yellow-100 text-yellow-800';
            case 'resolved': return 'bg-green-100 text-green-800';
            default: return 'bg-gray-100 text-gray-800';
        }
    };

    return (
        <div className="space-y-6">
            <div className="flex items-center gap-4">
                <Link to="/conflicts" className="text-blue-600 hover:text-blue-900">
                    ← Back to Conflicts
                </Link>
            </div>

            <div className="flex justify-between items-center">
                <h1 className="text-2xl font-bold text-gray-900">Conflict Alert Details</h1>
                <span className={`px-3 py-1 text-sm font-semibold rounded-full ${getStatusColor(conflict.status)}`}>
                    {conflict.status.toUpperCase()}
                </span>
            </div>

            {/* Employee Info */}
            <div className="bg-white shadow rounded-lg p-6">
                <h2 className="text-lg font-semibold text-gray-900 mb-4">Employee Information</h2>
                <dl className="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt className="text-sm text-gray-500">Name</dt>
                        <dd className="text-sm font-medium text-gray-900">
                            {conflict.employee?.name || `Employee #${conflict.employee_id}`}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-sm text-gray-500">Department</dt>
                        <dd className="text-sm font-medium text-gray-900">
                            {conflict.employee?.department?.name || 'Unknown'}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-sm text-gray-500">Reporting Period</dt>
                        <dd className="text-sm font-medium text-gray-900">
                            {format(new Date(conflict.reporting_period_start), 'MMM d, yyyy')} - {format(new Date(conflict.reporting_period_end), 'MMM d, yyyy')}
                        </dd>
                    </div>
                    <div>
                        <dt className="text-sm text-gray-500">Alert Created</dt>
                        <dd className="text-sm font-medium text-gray-900">
                            {format(new Date(conflict.created_at), 'MMM d, yyyy HH:mm')}
                        </dd>
                    </div>
                </dl>
            </div>

            {/* Hours Comparison */}
            <div className="bg-white shadow rounded-lg p-6">
                <h2 className="text-lg font-semibold text-gray-900 mb-4">Hours Discrepancy</h2>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div className="text-center p-4 bg-blue-50 rounded-lg">
                        <div className="text-sm text-blue-600 font-medium">Source A (Project Reports)</div>
                        <div className="text-3xl font-bold text-blue-700 mt-2">{conflict.source_a_hours} hrs</div>
                        <div className="text-xs text-blue-500 mt-1">Reported by SDD(s)</div>
                    </div>
                    <div className="text-center p-4 bg-purple-50 rounded-lg">
                        <div className="text-sm text-purple-600 font-medium">Source B (Department Report)</div>
                        <div className="text-3xl font-bold text-purple-700 mt-2">{conflict.source_b_hours} hrs</div>
                        <div className="text-xs text-purple-500 mt-1">Reported by Dept Manager</div>
                    </div>
                    <div className={`text-center p-4 rounded-lg ${Math.abs(conflict.discrepancy) >= 10 ? 'bg-red-50' : 'bg-yellow-50'}`}>
                        <div className={`text-sm font-medium ${Math.abs(conflict.discrepancy) >= 10 ? 'text-red-600' : 'text-yellow-600'}`}>
                            Discrepancy
                        </div>
                        <div className={`text-3xl font-bold mt-2 ${Math.abs(conflict.discrepancy) >= 10 ? 'text-red-700' : 'text-yellow-700'}`}>
                            {conflict.discrepancy > 0 ? '+' : ''}{conflict.discrepancy} hrs
                        </div>
                        <div className={`text-xs mt-1 ${Math.abs(conflict.discrepancy) >= 10 ? 'text-red-500' : 'text-yellow-500'}`}>
                            {conflict.discrepancy > 0 ? 'Project reports higher' : 'Department report higher'}
                        </div>
                    </div>
                </div>
            </div>

            {/* Resolution Section */}
            {conflict.status === 'resolved' ? (
                <div className="bg-green-50 shadow rounded-lg p-6">
                    <h2 className="text-lg font-semibold text-green-800 mb-4">Resolution</h2>
                    <dl className="space-y-4">
                        <div>
                            <dt className="text-sm text-green-600">Resolved By</dt>
                            <dd className="text-sm font-medium text-green-900">
                                {conflict.resolver?.name || 'Unknown'}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-sm text-green-600">Resolved At</dt>
                            <dd className="text-sm font-medium text-green-900">
                                {conflict.resolved_at ? format(new Date(conflict.resolved_at), 'MMM d, yyyy HH:mm') : 'N/A'}
                            </dd>
                        </div>
                        <div>
                            <dt className="text-sm text-green-600">Resolution Notes</dt>
                            <dd className="text-sm text-green-900 whitespace-pre-wrap bg-white p-3 rounded mt-1">
                                {conflict.resolution_notes}
                            </dd>
                        </div>
                    </dl>
                </div>
            ) : (
                <div className="bg-white shadow rounded-lg p-6">
                    <h2 className="text-lg font-semibold text-gray-900 mb-4">Resolve This Conflict</h2>
                    <p className="text-sm text-gray-600 mb-4">
                        Please investigate the discrepancy and provide a resolution explanation.
                        This could include contacting the relevant SDD and/or Department Manager to clarify the hours.
                    </p>
                    <div className="space-y-4">
                        <div>
                            <label htmlFor="resolution" className="block text-sm font-medium text-gray-700 mb-1">
                                Resolution Notes *
                            </label>
                            <textarea
                                id="resolution"
                                rows={4}
                                value={resolutionNotes}
                                onChange={(e) => setResolutionNotes(e.target.value)}
                                className="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
                                placeholder="Explain how this conflict was resolved (minimum 10 characters)..."
                            />
                        </div>
                        <button
                            onClick={handleResolve}
                            disabled={isResolving || resolutionNotes.length < 10}
                            className="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed text-sm font-medium"
                        >
                            {isResolving ? 'Resolving...' : 'Mark as Resolved'}
                        </button>
                    </div>
                </div>
            )}

            {/* Escalation Info */}
            {conflict.status === 'escalated' && (
                <div className="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div className="flex items-center gap-2">
                        <svg className="w-5 h-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span className="text-sm font-medium text-red-800">
                            This conflict was escalated on {conflict.escalated_at ? format(new Date(conflict.escalated_at), 'MMM d, yyyy') : 'N/A'} (unresolved for 7+ days)
                        </span>
                    </div>
                </div>
            )}
        </div>
    );
};

export default ConflictDetail;
