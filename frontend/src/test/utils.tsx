import React, { PropsWithChildren } from 'react'
import { render } from '@testing-library/react'
import type { RenderOptions } from '@testing-library/react'
import { Provider } from 'react-redux'
import { configureStore } from '@reduxjs/toolkit'
import { BrowserRouter } from 'react-router-dom'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import uiReducer from '../store/slices/uiSlice'
import projectReportsReducer from '../store/slices/projectReportsSlice'
import departmentReportsReducer from '../store/slices/departmentReportsSlice'
import conflictsReducer from '../store/slices/conflictsSlice'

// Create a custom render function that includes providers
interface ExtendedRenderOptions extends Omit<RenderOptions, 'queries'> {
    preloadedState?: Record<string, unknown>
    store?: any
}

export function renderWithProviders(
    ui: React.ReactElement,
    {
        preloadedState = {},
        // Automatically create a store instance if no store was passed in
        store = configureStore({
            reducer: {
                ui: uiReducer,
                projectReports: projectReportsReducer,
                departmentReports: departmentReportsReducer,
                conflicts: conflictsReducer,
            },
            preloadedState,
        }),
        ...renderOptions
    }: ExtendedRenderOptions = {}
) {
    // Create a new QueryClient for each test
    const queryClient = new QueryClient({
        defaultOptions: {
            queries: {
                retry: false,
            },
        },
    })

    function Wrapper({ children }: PropsWithChildren<{}>): JSX.Element {
        return (
            <Provider store={store}>
                <QueryClientProvider client={queryClient}>
                    <BrowserRouter>{children}</BrowserRouter>
                </QueryClientProvider>
            </Provider>
        )
    }

    // Return an object with the store and all of RTL's query functions
    return { store, ...render(ui, { wrapper: Wrapper, ...renderOptions }) }
}

// Re-export everything
export * from '@testing-library/react'
