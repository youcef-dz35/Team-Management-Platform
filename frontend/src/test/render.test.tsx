import { describe, it, expect } from 'vitest'
import { screen } from '@testing-library/react'
import { renderWithProviders } from './utils'
import React from 'react'

const TestComponent = () => {
    return <div>Hello Redux</div>
}

describe('Render Sanity', () => {
    it('renders with providers', () => {
        renderWithProviders(<TestComponent />)
        expect(screen.getByText('Hello Redux')).toBeInTheDocument()
    })
})
