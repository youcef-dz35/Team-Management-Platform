import { describe, it, expect, vi, beforeEach } from 'vitest'
import { screen } from '@testing-library/react'
import { renderWithProviders } from '../utils'
import { RoleGuard } from '../../guards/RoleGuard'
import { useAuthStore } from '../../store/authStore'
import { Route, Routes } from 'react-router-dom'
import React from 'react'

describe('RBAC Isolation Integration', () => {

    beforeEach(() => {
        useAuthStore.setState({ user: null, token: null, isAuthenticated: false })
    })

    it('RoleGuard blocks SDD from accessing Source B routes', () => {
        // Setup SDD user (Source A access only)
        useAuthStore.setState({
            user: { id: 1, name: 'SDD User', role: 'sdd', roles: ['sdd'], permissions: [], email: 'sdd@test.com', is_god_mode: false },
            isAuthenticated: true
        })

        renderWithProviders(
            <Routes>
                <Route element={<RoleGuard allowedRoles={['dept_manager', 'ceo']} />}>
                    <Route path="/" element={<div>Sensitive Department Data</div>} />
                </Route>
            </Routes>
        )

        expect(screen.queryByText('Sensitive Department Data')).not.toBeInTheDocument()
    })

    it('RoleGuard blocks Dept Manager from accessing Source A routes', () => {
        // Setup Dept Manager (Source B access only)
        useAuthStore.setState({
            user: { id: 2, name: 'Dept Mgr', role: 'dept_manager', roles: ['dept_manager'], permissions: [], email: 'dm@test.com', is_god_mode: false },
            isAuthenticated: true
        })

        renderWithProviders(
            <Routes>
                <Route element={<RoleGuard allowedRoles={['sdd', 'ceo']} />}>
                    <Route path="/" element={<div>Sensitive Project Data</div>} />
                </Route>
            </Routes>
        )

        expect(screen.queryByText('Sensitive Project Data')).not.toBeInTheDocument()
    })

    it('CEO has God-mode access', () => {
        // Setup CEO
        useAuthStore.setState({
            user: { id: 99, name: 'CEO', role: 'ceo', roles: ['ceo'], permissions: [], email: 'ceo@test.com', is_god_mode: true },
            isAuthenticated: true
        })

        renderWithProviders(
            <Routes>
                <Route element={<RoleGuard allowedRoles={['sdd', 'dept_manager', 'ceo']} />}>
                    <Route path="/" element={<div>Any Data</div>} />
                </Route>
            </Routes>
        )

        expect(screen.getByText('Any Data')).toBeInTheDocument()
    })
})
