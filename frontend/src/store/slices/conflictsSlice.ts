import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { conflictsApi, ConflictAlert, ConflictStats, ResolveConflictPayload } from '../../lib/api/conflicts';

interface ConflictsState {
    conflicts: ConflictAlert[];
    currentConflict: ConflictAlert | null;
    stats: ConflictStats | null;
    loading: boolean;
    error: string | null;
    pagination: {
        currentPage: number;
        lastPage: number;
        total: number;
    };
    filter: {
        status: string | null;
    };
}

const initialState: ConflictsState = {
    conflicts: [],
    currentConflict: null,
    stats: null,
    loading: false,
    error: null,
    pagination: {
        currentPage: 1,
        lastPage: 1,
        total: 0,
    },
    filter: {
        status: null,
    },
};

// Async thunks
export const fetchConflicts = createAsyncThunk(
    'conflicts/fetchConflicts',
    async ({ page, status }: { page?: number; status?: string } = {}, { rejectWithValue }) => {
        try {
            const response = await conflictsApi.getConflicts(page, status);
            return response;
        } catch (error: any) {
            return rejectWithValue(error.response?.data?.message || 'Failed to fetch conflicts');
        }
    }
);

export const fetchConflict = createAsyncThunk(
    'conflicts/fetchConflict',
    async (id: number | string, { rejectWithValue }) => {
        try {
            const response = await conflictsApi.getConflict(id);
            return response;
        } catch (error: any) {
            return rejectWithValue(error.response?.data?.message || 'Failed to fetch conflict');
        }
    }
);

export const fetchConflictStats = createAsyncThunk(
    'conflicts/fetchStats',
    async (_, { rejectWithValue }) => {
        try {
            const response = await conflictsApi.getStats();
            return response;
        } catch (error: any) {
            return rejectWithValue(error.response?.data?.message || 'Failed to fetch conflict stats');
        }
    }
);

export const resolveConflict = createAsyncThunk(
    'conflicts/resolveConflict',
    async ({ id, data }: { id: number | string; data: ResolveConflictPayload }, { rejectWithValue }) => {
        try {
            const response = await conflictsApi.resolveConflict(id, data);
            return response;
        } catch (error: any) {
            return rejectWithValue(error.response?.data?.message || 'Failed to resolve conflict');
        }
    }
);

export const runConflictDetection = createAsyncThunk(
    'conflicts/runDetection',
    async (data: { period_start: string; period_end: string }, { rejectWithValue }) => {
        try {
            const response = await conflictsApi.runDetection(data);
            return response;
        } catch (error: any) {
            return rejectWithValue(error.response?.data?.message || 'Failed to run conflict detection');
        }
    }
);

const conflictsSlice = createSlice({
    name: 'conflicts',
    initialState,
    reducers: {
        clearError: (state) => {
            state.error = null;
        },
        clearCurrentConflict: (state) => {
            state.currentConflict = null;
        },
        setStatusFilter: (state, action: PayloadAction<string | null>) => {
            state.filter.status = action.payload;
        },
    },
    extraReducers: (builder) => {
        // Fetch conflicts
        builder
            .addCase(fetchConflicts.pending, (state) => {
                state.loading = true;
                state.error = null;
            })
            .addCase(fetchConflicts.fulfilled, (state, action) => {
                state.loading = false;
                state.conflicts = action.payload.data;
                state.pagination = {
                    currentPage: action.payload.current_page,
                    lastPage: action.payload.last_page,
                    total: action.payload.total,
                };
            })
            .addCase(fetchConflicts.rejected, (state, action) => {
                state.loading = false;
                state.error = action.payload as string;
            });

        // Fetch single conflict
        builder
            .addCase(fetchConflict.pending, (state) => {
                state.loading = true;
                state.error = null;
            })
            .addCase(fetchConflict.fulfilled, (state, action) => {
                state.loading = false;
                state.currentConflict = action.payload;
            })
            .addCase(fetchConflict.rejected, (state, action) => {
                state.loading = false;
                state.error = action.payload as string;
            });

        // Fetch stats
        builder
            .addCase(fetchConflictStats.pending, (state) => {
                state.loading = true;
                state.error = null;
            })
            .addCase(fetchConflictStats.fulfilled, (state, action) => {
                state.loading = false;
                state.stats = action.payload;
            })
            .addCase(fetchConflictStats.rejected, (state, action) => {
                state.loading = false;
                state.error = action.payload as string;
            });

        // Resolve conflict
        builder
            .addCase(resolveConflict.pending, (state) => {
                state.loading = true;
                state.error = null;
            })
            .addCase(resolveConflict.fulfilled, (state, action) => {
                state.loading = false;
                state.currentConflict = action.payload.conflict;
                // Update the conflict in the list
                const index = state.conflicts.findIndex(c => c.id === action.payload.conflict.id);
                if (index !== -1) {
                    state.conflicts[index] = action.payload.conflict;
                }
            })
            .addCase(resolveConflict.rejected, (state, action) => {
                state.loading = false;
                state.error = action.payload as string;
            });

        // Run detection
        builder
            .addCase(runConflictDetection.pending, (state) => {
                state.loading = true;
                state.error = null;
            })
            .addCase(runConflictDetection.fulfilled, (state) => {
                state.loading = false;
            })
            .addCase(runConflictDetection.rejected, (state, action) => {
                state.loading = false;
                state.error = action.payload as string;
            });
    },
});

export const { clearError, clearCurrentConflict, setStatusFilter } = conflictsSlice.actions;
export default conflictsSlice.reducer;
