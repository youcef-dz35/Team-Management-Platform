import React from 'react';
import { useAuthStore } from '../store/authStore';

const Dashboard = () => {
    const user = useAuthStore((state) => state.user);

    return (
        <div className="space-y-6">
            <div className="bg-white shadow rounded-lg p-6">
                <h2 className="text-xl font-semibold text-gray-900 mb-4">
                    Welcome back, {user?.name}!
                </h2>
                <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div className="bg-blue-50 p-4 rounded-lg">
                        <h3 className="text-sm font-medium text-blue-800">Your Role</h3>
                        <p className="mt-1 text-2xl font-semibold text-blue-900">
                            {user?.roles[0]?.toUpperCase()}
                        </p>
                    </div>
                    <div className="bg-green-50 p-4 rounded-lg">
                        <h3 className="text-sm font-medium text-green-800">Department</h3>
                        <p className="mt-1 text-2xl font-semibold text-green-900">
                            {user?.department?.name || 'N/A'}
                        </p>
                    </div>
                    <div className="bg-purple-50 p-4 rounded-lg">
                        <h3 className="text-sm font-medium text-purple-800">Permissions</h3>
                        <p className="mt-1 text-2xl font-semibold text-purple-900">
                            {user?.permissions?.length || 0} active
                        </p>
                    </div>
                </div>
            </div>
        </div>
    );
};

export default Dashboard;
