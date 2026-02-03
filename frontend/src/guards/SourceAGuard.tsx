import React from 'react';
import { Navigate, Outlet } from 'react-router-dom';
import { useAuthStore } from '../store/authStore';

/**
 * Source A Guard - Blocks dept_manager role from Source A (Project Reports) routes.
 *
 * Source A contains project reports submitted by SDDs.
 * Department Managers must NOT have access to prevent collusion.
 */
export const SourceAGuard: React.FC = () => {
    const user = useAuthStore((state) => state.user);

    if (!user) {
        return <Navigate to="/login" replace />;
    }

    // Block dept_manager from Source A routes
    const isDeptManager = user.roles.includes('dept_manager');

    if (isDeptManager) {
        // Redirect to dashboard with a message (could also use a dedicated forbidden page)
        return <Navigate to="/" replace />;
    }

    return <Outlet />;
};

export default SourceAGuard;
