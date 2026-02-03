import React from 'react'
import ReactDOM from 'react-dom/client'
import { Provider } from 'react-redux'
import { QueryClient, QueryClientProvider } from '@tanstack/react-query'
import { RouterProvider } from 'react-router-dom'
import { store } from './store'
import { router } from './router'
import './index.css'

const queryClient = new QueryClient({
  defaultOptions: {
    queries: {
      retry: false,
      refetchOnWindowFocus: false,
      staleTime: 1000 * 60 * 10, // 10 minutes - data stays fresh longer
      gcTime: 1000 * 60 * 15,    // 15 minutes - cache persists longer
      refetchOnMount: false,      // Don't refetch on component mount if data is fresh
      refetchOnReconnect: false,  // Don't refetch on network reconnection
    },
  },
})

ReactDOM.createRoot(document.getElementById('root')!).render(
  // Disabled StrictMode in development to prevent duplicate API requests
  // which cause unnecessary queueing. Re-enable for production builds.
  // <React.StrictMode>
  <Provider store={store}>
    <QueryClientProvider client={queryClient}>
      <RouterProvider router={router} />
    </QueryClientProvider>
  </Provider>
  // </React.StrictMode>
)
