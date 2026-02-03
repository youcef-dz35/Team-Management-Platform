import React from 'react';
import { Link, useLocation } from 'react-router-dom';
import { useAuthStore } from '../../store/authStore';

interface NavItem {
    path: string;
    label: string;
    icon: React.ReactNode;
    roles: string[];
}

// SVG Icons
const DashboardIcon = () => (
    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
    </svg>
);

const ReportIcon = () => (
    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
    </svg>
);

const DepartmentIcon = () => (
    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
    </svg>
);

const ConflictIcon = () => (
    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
    </svg>
);

const AuditIcon = () => (
    <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
    </svg>
);

const getNavItems = (roles: string[]): NavItem[] => {
    const hasRole = (allowedRoles: string[]) =>
        roles.some(role => allowedRoles.includes(role));

    const items: NavItem[] = [
        {
            path: '/',
            label: 'Dashboard',
            icon: <DashboardIcon />,
            roles: ['all'],
        },
    ];

    // Project Reports (Source A) - NOT for dept_manager
    if (hasRole(['sdd', 'director', 'gm', 'ops_manager', 'ceo', 'cfo'])) {
        items.push({
            path: '/reports',
            label: 'Project Reports',
            icon: <ReportIcon />,
            roles: ['sdd', 'director', 'gm', 'ops_manager', 'ceo', 'cfo'],
        });
    }

    // Department Reports (Source B) - NOT for sdd
    if (hasRole(['dept_manager', 'gm', 'ops_manager', 'ceo', 'cfo'])) {
        items.push({
            path: '/department-reports',
            label: 'Dept Reports',
            icon: <DepartmentIcon />,
            roles: ['dept_manager', 'gm', 'ops_manager', 'ceo', 'cfo'],
        });
    }

    // Conflict Alerts - only executives
    if (hasRole(['gm', 'ops_manager', 'ceo', 'cfo'])) {
        items.push({
            path: '/conflicts',
            label: 'Conflict Alerts',
            icon: <ConflictIcon />,
            roles: ['gm', 'ops_manager', 'ceo', 'cfo'],
        });
    }

    // Audit Logs - CEO/CFO only
    if (hasRole(['ceo', 'cfo'])) {
        items.push({
            path: '/audit-logs',
            label: 'Audit Logs',
            icon: <AuditIcon />,
            roles: ['ceo', 'cfo'],
        });
    }

    return items;
};

interface SidebarProps {
    collapsed?: boolean;
    onToggle?: () => void;
}

export const Sidebar: React.FC<SidebarProps> = ({ collapsed = false, onToggle }) => {
    const user = useAuthStore((state) => state.user);
    const location = useLocation();
    const navItems = getNavItems(user?.roles || []);

    const isActiveRoute = (path: string) => {
        if (path === '/') return location.pathname === '/';
        return location.pathname.startsWith(path);
    };

    return (
        <aside className={`bg-gray-900 text-white transition-all duration-300 ${collapsed ? 'w-16' : 'w-64'}`}>
            <div className="flex flex-col h-full">
                {/* Logo */}
                <div className="flex items-center justify-between h-16 px-4 border-b border-gray-800">
                    {!collapsed && (
                        <span className="text-lg font-bold">Team Platform</span>
                    )}
                    {onToggle && (
                        <button onClick={onToggle} className="p-1 hover:bg-gray-800 rounded">
                            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h16" />
                            </svg>
                        </button>
                    )}
                </div>

                {/* Navigation */}
                <nav className="flex-1 px-2 py-4 space-y-1">
                    {navItems.map((item) => (
                        <Link
                            key={item.path}
                            to={item.path}
                            className={`flex items-center gap-3 px-3 py-2 rounded-md transition-colors ${
                                isActiveRoute(item.path)
                                    ? 'bg-blue-600 text-white'
                                    : 'text-gray-300 hover:bg-gray-800 hover:text-white'
                            }`}
                            title={collapsed ? item.label : undefined}
                        >
                            {item.icon}
                            {!collapsed && <span>{item.label}</span>}
                        </Link>
                    ))}
                </nav>

                {/* User Info */}
                {!collapsed && user && (
                    <div className="border-t border-gray-800 p-4">
                        <div className="text-sm text-gray-400">Logged in as</div>
                        <div className="text-sm font-medium truncate">{user.name}</div>
                        <div className="text-xs text-gray-500 capitalize">{user.roles[0]?.replace('_', ' ')}</div>
                    </div>
                )}
            </div>
        </aside>
    );
};

export default Sidebar;
