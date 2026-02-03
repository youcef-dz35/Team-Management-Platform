import React from 'react';
import { useAuthStore } from '../../store/authStore';
import { useAuth } from '../../hooks/useAuth';

interface HeaderProps {
    title?: string;
    onMenuClick?: () => void;
}

const getRoleDisplay = (role: string): string => {
    const roleNames: Record<string, string> = {
        ceo: 'CEO',
        cfo: 'CFO',
        gm: 'General Manager',
        ops_manager: 'Ops Manager',
        director: 'Director',
        sdd: 'SDD',
        dept_manager: 'Dept Manager',
        worker: 'Worker',
    };
    return roleNames[role] || role;
};

const getRoleBadgeColor = (role: string): string => {
    const colors: Record<string, string> = {
        ceo: 'bg-purple-100 text-purple-800',
        cfo: 'bg-purple-100 text-purple-800',
        gm: 'bg-blue-100 text-blue-800',
        ops_manager: 'bg-blue-100 text-blue-800',
        director: 'bg-green-100 text-green-800',
        sdd: 'bg-yellow-100 text-yellow-800',
        dept_manager: 'bg-orange-100 text-orange-800',
        worker: 'bg-gray-100 text-gray-800',
    };
    return colors[role] || 'bg-gray-100 text-gray-800';
};

export const Header: React.FC<HeaderProps> = ({ title, onMenuClick }) => {
    const user = useAuthStore((state) => state.user);
    const { logout } = useAuth();

    const primaryRole = user?.roles[0] || 'unknown';

    return (
        <header className="bg-white shadow-sm border-b border-gray-200">
            <div className="flex items-center justify-between h-16 px-4 sm:px-6">
                {/* Left side */}
                <div className="flex items-center gap-4">
                    {onMenuClick && (
                        <button
                            onClick={onMenuClick}
                            className="p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 lg:hidden"
                        >
                            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    )}
                    {title && (
                        <h1 className="text-xl font-semibold text-gray-900">{title}</h1>
                    )}
                </div>

                {/* Right side - User info and actions */}
                <div className="flex items-center gap-4">
                    {/* Notifications placeholder */}
                    <button className="p-2 rounded-md text-gray-500 hover:text-gray-700 hover:bg-gray-100 relative">
                        <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                        </svg>
                    </button>

                    {/* User dropdown */}
                    <div className="flex items-center gap-3">
                        {/* Avatar */}
                        <div className="w-8 h-8 rounded-full bg-gray-300 flex items-center justify-center text-sm font-medium text-gray-700">
                            {user?.name?.charAt(0).toUpperCase() || '?'}
                        </div>

                        {/* User info */}
                        <div className="hidden sm:block">
                            <div className="text-sm font-medium text-gray-900">{user?.name}</div>
                            <div className="flex items-center gap-2">
                                <span className={`text-xs px-2 py-0.5 rounded-full ${getRoleBadgeColor(primaryRole)}`}>
                                    {getRoleDisplay(primaryRole)}
                                </span>
                                {user?.is_god_mode && (
                                    <span className="text-xs px-2 py-0.5 rounded-full bg-purple-600 text-white">
                                        God Mode
                                    </span>
                                )}
                            </div>
                        </div>

                        {/* Logout button */}
                        <button
                            onClick={() => logout.mutate()}
                            className="p-2 rounded-md text-gray-500 hover:text-red-600 hover:bg-red-50"
                            title="Logout"
                        >
                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </header>
    );
};

export default Header;
