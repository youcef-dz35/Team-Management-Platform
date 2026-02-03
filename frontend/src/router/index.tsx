import { createBrowserRouter, Navigate, Outlet } from 'react-router-dom';
import { AuthLayout } from '../components/layouts/AuthLayout';
import { DashboardLayout } from '../components/layouts/DashboardLayout';
import Login from '../pages/Login';
import Dashboard from '../pages/Dashboard';
import ReportList from '../pages/Reports/ReportList';
import ReportForm from '../pages/Reports/ReportForm';
import ReportView from '../pages/Reports/ReportView';
import ReportAmend from '../pages/Reports/ReportAmend';
import DeptReportList from '../pages/DepartmentReports/ReportList';
import DeptReportForm from '../pages/DepartmentReports/ReportForm';
import DeptReportView from '../pages/DepartmentReports/ReportView';
import DeptReportAmend from '../pages/DepartmentReports/ReportAmend';
import ConflictList from '../pages/Conflicts/ConflictList';
import ConflictDetail from '../pages/Conflicts/ConflictDetail';
import { useAuthStore } from '../store/authStore';
import { RoleGuard } from '../guards/RoleGuard';

const ProtectedRoute = () => {
    const isAuthenticated = useAuthStore((state) => state.isAuthenticated);
    return isAuthenticated ? <Outlet /> : <Navigate to="/login" replace />;
};

const PublicRoute = () => {
    const isAuthenticated = useAuthStore((state) => state.isAuthenticated);
    return isAuthenticated ? <Navigate to="/" replace /> : <Outlet />;
};

export const router = createBrowserRouter([
    {
        element: <PublicRoute />,
        children: [
            {
                path: '/login',
                element: (
                    <AuthLayout>
                        <Login />
                    </AuthLayout>
                ),
            },
        ],
    },
    {
        element: <ProtectedRoute />,
        children: [
            {
                path: '/',
                element: (
                    <DashboardLayout>
                        <Dashboard />
                    </DashboardLayout>
                ),
            },
            {
                element: <RoleGuard allowedRoles={['sdd', 'director', 'gm', 'ops_manager', 'ceo', 'cfo']} />,
                children: [
                    {
                        path: '/reports',
                        element: (
                            <DashboardLayout>
                                <ReportList />
                            </DashboardLayout>
                        ),
                    },
                    {
                        path: '/reports/new',
                        element: (
                            <DashboardLayout>
                                <ReportForm />
                            </DashboardLayout>
                        ),
                    },
                    {
                        path: '/reports/:id',
                        element: (
                            <DashboardLayout>
                                <ReportView />
                            </DashboardLayout>
                        ),
                    },
                    {
                        path: '/reports/:id/edit',
                        element: (
                            <DashboardLayout>
                                <ReportForm />
                            </DashboardLayout>
                        ),
                    },
                    {
                        path: '/reports/:id/amend',
                        element: (
                            <DashboardLayout>
                                <ReportAmend />
                            </DashboardLayout>
                        ),
                    },
                ]
            },
            {
                element: <RoleGuard allowedRoles={['dept_manager', 'gm', 'ops_manager', 'ceo', 'cfo']} />,
                children: [
                    {
                        path: '/department-reports',
                        element: (
                            <DashboardLayout>
                                <DeptReportList />
                            </DashboardLayout>
                        ),
                    },
                    {
                        path: '/department-reports/new',
                        element: (
                            <DashboardLayout>
                                <DeptReportForm />
                            </DashboardLayout>
                        ),
                    },
                    {
                        path: '/department-reports/:id',
                        element: (
                            <DashboardLayout>
                                <DeptReportView />
                            </DashboardLayout>
                        ),
                    },
                    {
                        path: '/department-reports/:id/edit',
                        element: (
                            <DashboardLayout>
                                <DeptReportForm />
                            </DashboardLayout>
                        ),
                    },
                    {
                        path: '/department-reports/:id/amend',
                        element: (
                            <DashboardLayout>
                                <DeptReportAmend />
                            </DashboardLayout>
                        ),
                    },
                ]
            },
            // Conflict Alerts (GM, CEO, CFO, Ops Manager only)
            {
                element: <RoleGuard allowedRoles={['gm', 'ceo', 'cfo', 'ops_manager']} />,
                children: [
                    {
                        path: '/conflicts',
                        element: (
                            <DashboardLayout>
                                <ConflictList />
                            </DashboardLayout>
                        ),
                    },
                    {
                        path: '/conflicts/:id',
                        element: (
                            <DashboardLayout>
                                <ConflictDetail />
                            </DashboardLayout>
                        ),
                    },
                ],
            },
        ],
    },
    {
        path: '*',
        element: <div>404 Not Found</div>,
    },
]);
