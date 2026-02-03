import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { reportsApi, ProjectReport, StoreReportPayload, UpdateReportPayload, AmendReportPayload } from '../../lib/api/reports';

interface ProjectReportsState {
    reports: ProjectReport[];
    loading: boolean;
    error: string | null;
    currentReport: ProjectReport | null;
}

const initialState: ProjectReportsState = {
    reports: [],
    loading: false,
    error: null,
    currentReport: null,
};

export const fetchReports = createAsyncThunk('projectReports/fetchReports', async (page?: number) => {
    const response = await reportsApi.getReports(page);
    return response.data;
});

export const fetchReport = createAsyncThunk('projectReports/fetchReport', async (id: number) => {
    const response = await reportsApi.getReport(id);
    return response;
});

export const createReport = createAsyncThunk('projectReports/createReport', async (data: StoreReportPayload) => {
    const response = await reportsApi.createReport(data);
    return response;
});

export const updateReport = createAsyncThunk('projectReports/updateReport', async ({ id, data }: { id: number; data: UpdateReportPayload }) => {
    const response = await reportsApi.updateReport(id, data);
    return response;
});

export const submitReport = createAsyncThunk('projectReports/submitReport', async (id: number) => {
    const response = await reportsApi.submitReport(id);
    return response.report;
});

export const amendReport = createAsyncThunk('projectReports/amendReport', async ({ id, data }: { id: number; data: AmendReportPayload }) => {
    const response = await reportsApi.amendReport(id, data);
    return response;
});

const projectReportsSlice = createSlice({
    name: 'projectReports',
    initialState,
    reducers: {
        clearCurrentReport(state) {
            state.currentReport = null;
        },
    },
    extraReducers: (builder) => {
        builder
            .addCase(fetchReports.pending, (state) => {
                state.loading = true;
                state.error = null;
            })
            .addCase(fetchReports.fulfilled, (state, action: PayloadAction<ProjectReport[]>) => {
                state.loading = false;
                state.reports = action.payload;
            })
            .addCase(fetchReports.rejected, (state, action) => {
                state.loading = false;
                state.error = action.error.message || 'Failed to fetch reports';
            })
            .addCase(fetchReport.pending, (state) => {
                state.loading = true;
                state.error = null;
            })
            .addCase(fetchReport.fulfilled, (state, action: PayloadAction<ProjectReport>) => {
                state.loading = false;
                state.currentReport = action.payload;
            })
            .addCase(fetchReport.rejected, (state, action) => {
                state.loading = false;
                state.error = action.error.message || 'Failed to fetch report';
            })
            .addCase(createReport.fulfilled, (state, action: PayloadAction<ProjectReport>) => {
                state.reports.unshift(action.payload);
            })
            .addCase(updateReport.fulfilled, (state, action: PayloadAction<ProjectReport>) => {
                const index = state.reports.findIndex(report => report.id === action.payload.id);
                if (index !== -1) {
                    state.reports[index] = action.payload;
                }
                if (state.currentReport?.id === action.payload.id) {
                    state.currentReport = action.payload;
                }
            })
            .addCase(submitReport.fulfilled, (state, action: PayloadAction<ProjectReport>) => {
                const index = state.reports.findIndex(report => report.id === action.payload.id);
                if (index !== -1) {
                    state.reports[index] = action.payload;
                }
                if (state.currentReport?.id === action.payload.id) {
                    state.currentReport = action.payload;
                }
            })
            .addCase(amendReport.fulfilled, (state, action: PayloadAction<ProjectReport>) => {
                const index = state.reports.findIndex(report => report.id === action.payload.id);
                if (index !== -1) {
                    state.reports[index] = action.payload;
                }
                if (state.currentReport?.id === action.payload.id) {
                    state.currentReport = action.payload;
                }
            });
    },
});

export const { clearCurrentReport } = projectReportsSlice.actions;

export default projectReportsSlice.reducer;
