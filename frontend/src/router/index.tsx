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
import { useAuthStore } from '../store/authStore';

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
            // Department Reports
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
                path: '/department-reports/:id/edit',
                element: (
                    <DashboardLayout>
                        <DeptReportForm />
                    </DashboardLayout>
                ),
            },
        ],
    },
    {
        path: '*',
        element: <div>404 Not Found</div>,
    },
]);
