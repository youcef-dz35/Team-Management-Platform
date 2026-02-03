import React from 'react';
import { useAuthStore } from '../../store/authStore';
import { useAuth } from '../../hooks/useAuth';
import { Link, useLocation } from 'react-router-dom';

// Role-based navigation configuration
// Based on Constitution Principle I: Zero Trust Architecture
const getNavigationItems = (roles: string[]) => {
    const items: { path: string; label: string; roles: string[] }[] = [];

    // Check if user has any of the specified roles
    const hasRole = (allowedRoles: string[]) =>
        roles.some(role => allowedRoles.includes(role));

    // Dashboard - everyone sees this
    items.push({ path: '/', label: 'Dashboard', roles: ['all'] });

    // Project Reports (Source A) - NOT for dept_manager
    // SDDs, Directors, GM, Ops, CEO, CFO can see
    if (hasRole(['sdd', 'director', 'gm', 'ops_manager', 'ceo', 'cfo'])) {
        items.push({ path: '/reports', label: 'Project Reports', roles: ['sdd', 'director', 'gm', 'ops_manager', 'ceo', 'cfo'] });
    }

    // Department Reports (Source B) - NOT for sdd
    // Dept Managers, GM, Ops, CEO, CFO can see
    if (hasRole(['dept_manager', 'gm', 'ops_manager', 'ceo', 'cfo'])) {
        items.push({ path: '/department-reports', label: 'Dept Reports', roles: ['dept_manager', 'gm', 'ops_manager', 'ceo', 'cfo'] });
    }

    // Conflict Alerts - only executives
    if (hasRole(['gm', 'ops_manager', 'ceo', 'cfo'])) {
        items.push({ path: '/conflicts', label: 'Conflict Alerts', roles: ['gm', 'ops_manager', 'ceo', 'cfo'] });
    }

    return items;
};

export const DashboardLayout = ({ children }: { children: React.ReactNode }) => {
    const user = useAuthStore((state) => state.user);
    const { logout } = useAuth();
    const location = useLocation();

    const navItems = getNavigationItems(user?.roles || []);

    const isActiveRoute = (path: string) => {
        if (path === '/') return location.pathname === '/';
        return location.pathname.startsWith(path);
    };

    // Get role display name
    const getRoleDisplay = (role: string) => {
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

    return (
        <div className="min-h-screen bg-gray-50 flex flex-col">
            {/* Header */}
            <header className="bg-white shadow-sm">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex justify-between h-16">
                        <div className="flex items-center gap-6">
                            <h1 className="text-xl font-bold text-gray-900">
                                <Link to="/">Team Platform</Link>
                            </h1>
                            <nav className="flex gap-4">
                                {navItems.map((item) => (
                                    <Link
                                        key={item.path}
                                        to={item.path}
                                        className={`text-sm font-medium ${
                                            isActiveRoute(item.path)
                                                ? 'text-blue-600 border-b-2 border-blue-600 pb-1'
                                                : 'text-gray-700 hover:text-blue-600'
                                        }`}
                                    >
                                        {item.label}
                                        {item.path === '/conflicts' && (
                                            <span className="ml-1 text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded-full">
                                                !
                                            </span>
                                        )}
                                    </Link>
                                ))}
                            </nav>
                        </div>
                        <div className="flex items-center gap-4">
                            <div className="text-right">
                                <span className="text-sm font-medium text-gray-900 block">
                                    {user?.name}
                                </span>
                                <span className="text-xs text-gray-500">
                                    {user?.roles[0] ? getRoleDisplay(user.roles[0]) : 'Unknown Role'}
                                </span>
                            </div>
                            {user?.is_god_mode && (
                                <span className="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full font-medium">
                                    God Mode
                                </span>
                            )}
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

            {/* Role Warning Banner (for unauthorized access attempts) */}
            {user?.roles.includes('sdd') && location.pathname.startsWith('/department-reports') && (
                <div className="bg-red-600 text-white text-sm text-center py-2">
                    Access Denied: SDDs cannot view Department Reports (Source B)
                </div>
            )}
            {user?.roles.includes('dept_manager') && location.pathname.startsWith('/reports') && !location.pathname.startsWith('/reports') && (
                <div className="bg-red-600 text-white text-sm text-center py-2">
                    Access Denied: Department Managers cannot view Project Reports (Source A)
                </div>
            )}

            {/* Main Content */}
            <main className="flex-1 py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {children}
                </div>
            </main>

            {/* Footer */}
            <footer className="bg-white border-t py-4">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <p className="text-xs text-gray-500 text-center">
                        Team Management Platform • Dual Independent Reporting System
                    </p>
                </div>
            </footer>
        </div>
    );
};
