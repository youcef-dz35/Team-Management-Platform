import { describe, it, expect, vi, beforeEach } from 'vitest'
import { screen, fireEvent, waitFor } from '@testing-library/react'
import { renderWithProviders } from '../utils'
import ConflictDetail from '../../pages/Conflicts/ConflictDetail'
import { useConflicts } from '../../hooks/useConflicts'
import { useAuthStore } from '../../store/authStore'
import { Route, Routes } from 'react-router-dom'
import React from 'react'

// Mock the hook directly
vi.mock('../../hooks/useConflicts', () => ({
    useConflicts: vi.fn()
}))

describe('Conflict Resolution (User Story 3)', () => {

    const mockResolveMutate = vi.fn()

    beforeEach(() => {
        useAuthStore.setState({ user: null, isAuthenticated: false })

        vi.mocked(useConflicts).mockReturnValue({
            useGetConflict: () => ({
                data: {
                    id: 999,
                    employee_id: 101,
                    employee: { name: 'John Doe', department: { name: 'Backend' } },
                    reporting_period_start: '2026-02-01',
                    reporting_period_end: '2026-02-07',
                    created_at: '2026-02-08T10:00:00',
                    source_a_hours: 40,
                    source_b_hours: 20,
                    discrepancy: 20,
                    status: 'open',
                    resolution_notes: null
                },
                isLoading: false,
                isError: false
            }),
            resolveConflict: {
                mutateAsync: mockResolveMutate
            }
        } as any)
    })

    it('displays conflict details correctly', () => {
        // Setup GM user
        useAuthStore.setState({
            user: { id: 3, name: 'GM', roles: ['gm'], email: 'gm@test.com', permissions: [], is_god_mode: false },
            isAuthenticated: true
        })

        // Need Router for useParams? no, useParams returns empty usually if not nested in route.
        // But the component call useParams().
        // If we don't wrap in Routes with path, id might be undefined.
        // Component does: const { id } = useParams<{ id: string }>();
        // useGetConflict(id || '')

        // renderWithProviders wraps in BrowserRouter.
        // To provide ID, we should render with specific initial entry or Route.
        // But renderWithProviders defaults to BrowserRouter which doesn't support initialEntries easily?
        // Wait, utils defines BrowserRouter. We can't push history.
        // But useParams matches URL.

        // Actually, renderWithProviders hardcodes BrowserRouter.
        // This is a limitation for testing specific routes with IDs.
        // We should use MemoryRouter for tests, but utils uses BrowserRouter.
        // However, for this test, we can Mock `useParams`!

        renderWithProviders(<ConflictDetail />)

        // We just check if content from the MOCK data appears.
        expect(screen.getByText((content) => content.includes('40') && content.includes('hrs'))).toBeInTheDocument()
    })

    it('submits resolution', async () => {
        useAuthStore.setState({
            user: { id: 3, name: 'GM', roles: ['gm'], email: 'gm@test.com', permissions: [], is_god_mode: false },
            isAuthenticated: true
        })

        renderWithProviders(<ConflictDetail />)

        const noteInput = screen.getByPlaceholderText(/Explain how this conflict was resolved/i)
        fireEvent.change(noteInput, { target: { value: 'Met with SDD and confirmed 40h is correct' } })

        const resolveBtn = screen.getByText('Mark as Resolved')
        fireEvent.click(resolveBtn)

        await waitFor(() => {
            expect(mockResolveMutate).toHaveBeenCalled()
        })
    })
})
