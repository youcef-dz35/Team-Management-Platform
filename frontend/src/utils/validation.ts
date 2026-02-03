/**
 * Shared validation utilities for forms
 */

// Email validation
export const isValidEmail = (email: string): boolean => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
};

// Required field validation
export const isRequired = (value: string | number | null | undefined): boolean => {
    if (value === null || value === undefined) return false;
    if (typeof value === 'string') return value.trim().length > 0;
    return true;
};

// Minimum length validation
export const minLength = (value: string, min: number): boolean => {
    return value.length >= min;
};

// Maximum length validation
export const maxLength = (value: string, max: number): boolean => {
    return value.length <= max;
};

// Numeric range validation
export const inRange = (value: number, min: number, max: number): boolean => {
    return value >= min && value <= max;
};

// Hours validation (for report entries)
export const isValidHours = (hours: number): boolean => {
    return hours >= 0 && hours <= 168; // Max hours in a week
};

// Date validation
export const isValidDate = (dateString: string): boolean => {
    const date = new Date(dateString);
    return !isNaN(date.getTime());
};

// Date range validation (end >= start)
export const isValidDateRange = (start: string, end: string): boolean => {
    const startDate = new Date(start);
    const endDate = new Date(end);
    return endDate >= startDate;
};

// Common validation rules
export const validationRules = {
    email: {
        required: 'Email is required',
        pattern: {
            value: /^[^\s@]+@[^\s@]+\.[^\s@]+$/,
            message: 'Invalid email address',
        },
    },
    password: {
        required: 'Password is required',
        minLength: {
            value: 8,
            message: 'Password must be at least 8 characters',
        },
    },
    name: {
        required: 'Name is required',
        minLength: {
            value: 2,
            message: 'Name must be at least 2 characters',
        },
        maxLength: {
            value: 255,
            message: 'Name cannot exceed 255 characters',
        },
    },
    hours: {
        required: 'Hours are required',
        min: {
            value: 0,
            message: 'Hours cannot be negative',
        },
        max: {
            value: 168,
            message: 'Hours cannot exceed 168 (hours in a week)',
        },
    },
    resolutionNotes: {
        required: 'Resolution notes are required',
        minLength: {
            value: 10,
            message: 'Resolution notes must be at least 10 characters',
        },
        maxLength: {
            value: 2000,
            message: 'Resolution notes cannot exceed 2000 characters',
        },
    },
};

// Form error helper - combines API errors with client-side errors
export const mergeErrors = (
    clientErrors: Record<string, string>,
    apiErrors: Record<string, string> = {}
): Record<string, string> => {
    return { ...clientErrors, ...apiErrors };
};

// Get first error message for a field
export const getFieldError = (
    field: string,
    errors: Record<string, string | string[]>
): string | undefined => {
    const error = errors[field];
    if (!error) return undefined;
    if (Array.isArray(error)) return error[0];
    return error;
};

// Check if form has any errors
export const hasErrors = (errors: Record<string, string | string[] | undefined>): boolean => {
    return Object.values(errors).some((error) => {
        if (!error) return false;
        if (Array.isArray(error)) return error.length > 0;
        return error.length > 0;
    });
};

// Validate a single field
export type ValidationRule = {
    required?: string;
    minLength?: { value: number; message: string };
    maxLength?: { value: number; message: string };
    min?: { value: number; message: string };
    max?: { value: number; message: string };
    pattern?: { value: RegExp; message: string };
    custom?: (value: any) => string | undefined;
};

export const validateField = (
    value: any,
    rules: ValidationRule
): string | undefined => {
    if (rules.required && !isRequired(value)) {
        return rules.required;
    }

    if (value && typeof value === 'string') {
        if (rules.minLength && value.length < rules.minLength.value) {
            return rules.minLength.message;
        }
        if (rules.maxLength && value.length > rules.maxLength.value) {
            return rules.maxLength.message;
        }
        if (rules.pattern && !rules.pattern.value.test(value)) {
            return rules.pattern.message;
        }
    }

    if (typeof value === 'number') {
        if (rules.min && value < rules.min.value) {
            return rules.min.message;
        }
        if (rules.max && value > rules.max.value) {
            return rules.max.message;
        }
    }

    if (rules.custom) {
        return rules.custom(value);
    }

    return undefined;
};
