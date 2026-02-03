import { createSlice, createAsyncThunk, PayloadAction } from '@reduxjs/toolkit';
import { deptReportsApi, DepartmentReport, StoreDeptReportPayload, AmendDeptReportPayload } from '../../lib/api/departmentReports';

interface DepartmentReportsState {
    reports: DepartmentReport[];
    loading: boolean;
    error: string | null;
    currentReport: DepartmentReport | null;
}

const initialState: DepartmentReportsState = {
    reports: [],
    loading: false,
    error: null,
    currentReport: null,
};

export const fetchDeptReports = createAsyncThunk('departmentReports/fetchReports', async (page?: number) => {
    const response = await deptReportsApi.getReports(page);
    return response.data;
});

export const fetchDeptReport = createAsyncThunk('departmentReports/fetchReport', async (id: number) => {
    const response = await deptReportsApi.getReport(id);
    return response;
});

export const createDeptReport = createAsyncThunk('departmentReports/createReport', async (data: StoreDeptReportPayload) => {
    const response = await deptReportsApi.createReport(data);
    return response;
});

export const updateDeptReport = createAsyncThunk('departmentReports/updateReport', async ({ id, data }: { id: number; data: Partial<StoreDeptReportPayload> }) => {
    const response = await deptReportsApi.updateReport(id, data);
    return response;
});

export const submitDeptReport = createAsyncThunk('departmentReports/submitReport', async (id: number) => {
    const response = await deptReportsApi.submitReport(id);
    return response.report;
});

export const amendDeptReport = createAsyncThunk('departmentReports/amendReport', async ({ id, data }: { id: number; data: AmendDeptReportPayload }) => {
    const response = await deptReportsApi.amendReport(id, data);
    return response;
});

const departmentReportsSlice = createSlice({
    name: 'departmentReports',
    initialState,
    reducers: {
        clearCurrentDeptReport(state) {
            state.currentReport = null;
        },
    },
    extraReducers: (builder) => {
        builder
            .addCase(fetchDeptReports.pending, (state) => {
                state.loading = true;
                state.error = null;
            })
            .addCase(fetchDeptReports.fulfilled, (state, action: PayloadAction<DepartmentReport[]>) => {
                state.loading = false;
                state.reports = action.payload;
            })
            .addCase(fetchDeptReports.rejected, (state, action) => {
                state.loading = false;
                state.error = action.error.message || 'Failed to fetch reports';
            })
            .addCase(fetchDeptReport.pending, (state) => {
                state.loading = true;
                state.error = null;
            })
            .addCase(fetchDeptReport.fulfilled, (state, action: PayloadAction<DepartmentReport>) => {
                state.loading = false;
                state.currentReport = action.payload;
            })
            .addCase(fetchDeptReport.rejected, (state, action) => {
                state.loading = false;
                state.error = action.error.message || 'Failed to fetch report';
            })
            .addCase(createDeptReport.fulfilled, (state, action: PayloadAction<DepartmentReport>) => {
                state.reports.unshift(action.payload);
            })
            .addCase(updateDeptReport.fulfilled, (state, action: PayloadAction<DepartmentReport>) => {
                const index = state.reports.findIndex(report => report.id === action.payload.id);
                if (index !== -1) {
                    state.reports[index] = action.payload;
                }
                if (state.currentReport?.id === action.payload.id) {
                    state.currentReport = action.payload;
                }
            })
            .addCase(submitDeptReport.fulfilled, (state, action: PayloadAction<DepartmentReport>) => {
                const index = state.reports.findIndex(report => report.id === action.payload.id);
                if (index !== -1) {
                    state.reports[index] = action.payload;
                }
                if (state.currentReport?.id === action.payload.id) {
                    state.currentReport = action.payload;
                }
            })
            .addCase(amendDeptReport.fulfilled, (state, action: PayloadAction<DepartmentReport>) => {
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

export const { clearCurrentDeptReport } = departmentReportsSlice.actions;

export default departmentReportsSlice.reducer;
