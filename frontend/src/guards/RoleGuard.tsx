import React from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import { useAuthStore } from '../store/authStore';

interface RoleGuardProps {
    allowedRoles: string[];
}

export const RoleGuard: React.FC<RoleGuardProps> = ({ allowedRoles }) => {
    const user = useAuthStore((state) => state.user);

    if (!user) {
        return <Navigate to="/login" replace />;
    }

    const hasRequiredRole = user.roles.some(role => allowedRoles.includes(role));

    if (!hasRequiredRole) {
        return <Navigate to="/" replace />; // Or to a dedicated "Unauthorized" page
    }

    return <Outlet />;
};
