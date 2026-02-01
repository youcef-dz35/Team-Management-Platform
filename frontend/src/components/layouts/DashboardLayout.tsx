import React from 'react';
import { useAuthstore } from '../../store/authStore';
import { useAuth } from '../../hooks/useAuth';
import { Link } from 'react-router-dom';

export const DashboardLayout = ({ children }: { children: React.ReactNode }) => {
    const user = useAuthStore((state) => state.user);
    const { logout } = useAuth();

    return (
        <div className="min-h-screen bg-gray-50 flex flex-col">
            {/* Header */}
            <header className="bg-white shadow-sm">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex items-center">
                            <h1 className="text-xl font-bold text-gray-900">Team Platform</h1>
                        </div>
                        <div className="flex items-center gap-4">
                            <span className="text-sm text-gray-600">
                                {user?.name} ({user?.roles[0]})
                            </span>
                            <button
                                onClick={() => logout.mutate()}
                                className="text-sm font-medium text-red-600 hover:text-red-500"
                            >
                                Logout
                            </button>
                        </div>
                    </div>
                </div>
            </header>

            {/* Main Content */}
            <main className="flex-1 py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {children}
                </div>
            </main>
        </div>
    );
};
