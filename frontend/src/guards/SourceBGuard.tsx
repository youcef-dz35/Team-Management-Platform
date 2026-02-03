import React from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import { useAuthStore } from '../store/authStore';

/**
 * Source B Guard - Blocks sdd role from Source B (Department Reports) routes.
 *
 * Source B contains department reports submitted by Department Managers.
 * SDDs must NOT have access to prevent collusion.
 */
export const SourceBGuard: React.FC = () => {
    const user = useAuthStore((state) => state.user);

    if (!user) {
        return <Navigate to="/login" replace />;
    }

    // Block sdd from Source B routes
    const isSdd = user.roles.includes('sdd');

    if (isSdd) {
        // Redirect to dashboard with a message (could also use a dedicated forbidden page)
        return <Navigate to="/" replace />;
    }

    return <Outlet />;
};

export default SourceBGuard;
