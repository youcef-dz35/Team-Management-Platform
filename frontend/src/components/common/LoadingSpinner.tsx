import React from 'react';

interface LoadingSpinnerProps {
    size?: 'sm' | 'md' | 'lg' | 'xl';
    color?: 'blue' | 'gray' | 'white';
    text?: string;
    fullScreen?: boolean;
}

const sizeClasses = {
    sm: 'w-4 h-4',
    md: 'w-8 h-8',
    lg: 'w-12 h-12',
    xl: 'w-16 h-16',
};

const colorClasses = {
    blue: 'border-blue-600',
    gray: 'border-gray-600',
    white: 'border-white',
};

export const LoadingSpinner: React.FC<LoadingSpinnerProps> = ({
    size = 'md',
    color = 'blue',
    text,
    fullScreen = false,
}) => {
    const spinner = (
        <div className="flex flex-col items-center justify-center gap-3">
            <div
                className={`${sizeClasses[size]} border-4 border-t-transparent ${colorClasses[color]} rounded-full animate-spin`}
            />
            {text && (
                <span className={`text-sm ${color === 'white' ? 'text-white' : 'text-gray-600'}`}>
                    {text}
                </span>
            )}
        </div>
    );

    if (fullScreen) {
        return (
            <div className="fixed inset-0 bg-white/80 backdrop-blur-sm z-50 flex items-center justify-center">
                {spinner}
            </div>
        );
    }

    return spinner;
};

// Inline loading indicator for buttons
export const ButtonSpinner: React.FC<{ className?: string }> = ({ className = '' }) => (
    <svg
        className={`animate-spin h-4 w-4 ${className}`}
        xmlns="http://www.w3.org/2000/svg"
        fill="none"
        viewBox="0 0 24 24"
    >
        <circle
            className="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            strokeWidth="4"
        />
        <path
            className="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
        />
    </svg>
);

// Page loading skeleton
export const PageSkeleton: React.FC = () => (
    <div className="animate-pulse space-y-4">
        <div className="h-8 bg-gray-200 rounded w-1/4" />
        <div className="space-y-3">
            <div className="h-4 bg-gray-200 rounded w-full" />
            <div className="h-4 bg-gray-200 rounded w-5/6" />
            <div className="h-4 bg-gray-200 rounded w-4/6" />
        </div>
        <div className="grid grid-cols-3 gap-4 mt-6">
            <div className="h-24 bg-gray-200 rounded" />
            <div className="h-24 bg-gray-200 rounded" />
            <div className="h-24 bg-gray-200 rounded" />
        </div>
    </div>
);

// Table loading skeleton
export const TableSkeleton: React.FC<{ rows?: number; cols?: number }> = ({ rows = 5, cols = 4 }) => (
    <div className="animate-pulse">
        <div className="bg-gray-100 h-12 rounded-t-lg" />
        {Array.from({ length: rows }).map((_, i) => (
            <div key={i} className="flex gap-4 p-4 border-b border-gray-100">
                {Array.from({ length: cols }).map((_, j) => (
                    <div key={j} className="h-4 bg-gray-200 rounded flex-1" />
                ))}
            </div>
        ))}
    </div>
);

export default LoadingSpinner;
