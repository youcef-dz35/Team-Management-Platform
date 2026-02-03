import { describe, it, expect, vi, beforeEach } from 'vitest'
import { screen } from '@testing-library/react'
import { renderWithProviders } from '../utils'
import DeptReportForm from '../../pages/DepartmentReports/ReportForm'
import { useAuthStore } from '../../store/authStore'

// Mock API
vi.mock('../../lib/api/departmentReports', () => ({
    deptReportsApi: {
        createReport: vi.fn(),
        getReport: vi.fn(),
        updateReport: vi.fn()
    }
}))

describe('Dept Manager Reporting Flow (User Story 2)', () => {

    beforeEach(() => {
        useAuthStore.setState({ user: null, isAuthenticated: false })
    })

    it('renders the form correctly for Dept Manager', () => {
        useAuthStore.setState({
            user: {
                id: 2,
                name: 'Dept Mgr',
                roles: ['dept_manager'],
                department: { id: 10, name: 'Frontend' },
                email: 'dm@test.com', permissions: [], is_god_mode: false
            },
            isAuthenticated: true
        })

        renderWithProviders(<DeptReportForm />)

        expect(screen.getByText(/New Weekly Allocation/i)).toBeInTheDocument()
        expect(screen.getByText(/Resource Allocations/i)).toBeInTheDocument()
    })

    it('allows filling the form', async () => {
        useAuthStore.setState({
            user: {
                id: 2,
                name: 'Dept Mgr',
                roles: ['dept_manager'],
                department: { id: 10, name: 'Frontend' },
                email: 'dm@test.com', permissions: [], is_god_mode: false
            },
            isAuthenticated: true
        })

        renderWithProviders(<DeptReportForm />)

        expect(screen.getByText(/Start Date/i)).toBeInTheDocument()
    })
})
