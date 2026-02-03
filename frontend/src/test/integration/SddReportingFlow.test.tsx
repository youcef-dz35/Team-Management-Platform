import { describe, it, expect, vi, beforeEach } from 'vitest'
import { screen, fireEvent, waitFor } from '@testing-library/react'
import { renderWithProviders } from '../utils'
import ReportForm from '../../pages/Reports/ReportForm'
import { reportsApi } from '../../lib/api/reports'
import { useAuthStore } from '../../store/authStore'

// Mock the API interactions
vi.mock('../../lib/api/reports', () => ({
    reportsApi: {
        createReport: vi.fn(),
        getReport: vi.fn(),
        updateReport: vi.fn()
    }
}))

// Mock Date
vi.setSystemTime(new Date('2026-02-02'))

describe('SDD Reporting Flow (User Story 1)', () => {

    beforeEach(() => {
        useAuthStore.setState({ user: null, isAuthenticated: false })
        vi.clearAllMocks()
    })

    it('renders the report form correctly for SDD', async () => {
        useAuthStore.setState({
            user: { id: 1, name: 'SDD User', roles: ['sdd'], email: 'sdd@test.com', permissions: [], is_god_mode: false },
            isAuthenticated: true
        })

        renderWithProviders(<ReportForm />)

        expect(screen.getByText(/New Weekly Report/i)).toBeInTheDocument()
    })

    it('submits a valid report successfully', async () => {
        const mockCreate = vi.mocked(reportsApi.createReport).mockResolvedValue({ id: 101, status: 'submitted' })

        useAuthStore.setState({
            user: { id: 1, name: 'SDD User', roles: ['sdd'], email: 'sdd@test.com', permissions: [], is_god_mode: false },
            isAuthenticated: true
        })

        renderWithProviders(<ReportForm />)

        // Fill Project ID
        const projectInput = screen.getByPlaceholderText(/Enter Project ID/i)
        fireEvent.change(projectInput, { target: { value: '1' } })

        // Fill Entry User ID
        const userIdInputs = screen.getAllByPlaceholderText(/User ID/i)
        fireEvent.change(userIdInputs[0], { target: { value: '301' } })

        // Fill Hours (spinbutton) - targeted precisely
        const inputs = screen.getAllByRole('spinbutton')
        // Assuming order based on previous manual check failure
        // Index 0: Project ID
        // Index 1: User ID
        // Index 2: Hours
        if (inputs[2]) {
            fireEvent.change(inputs[2], { target: { value: '40' } })
        } else {
            // Fallback if role query fails or structure different
            // Debug by printing? No console access.
        }

        // Submit
        const saveBtn = screen.getByText('Save')

        // Ensure not disabled
        expect(saveBtn).not.toBeDisabled()

        fireEvent.click(saveBtn)

        await waitFor(() => {
            // Check for potential validation errors if it fails
            const errors = screen.queryAllByText(/required/i)
            if (errors.length > 0) {
                // console.log(errors.map(e => e.textContent))
            }

            expect(mockCreate).toHaveBeenCalled()
        })
    })
})
