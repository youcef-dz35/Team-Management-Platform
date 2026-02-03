import uiReducer from '../store/slices/uiSlice'
import projectReportsReducer from '../store/slices/projectReportsSlice'
import departmentReportsReducer from '../store/slices/departmentReportsSlice'
import conflictsReducer from '../store/slices/conflictsSlice'
import { describe, it, expect } from 'vitest'

describe('Slices Sanity', () => {
    it('instantiates ui', () => {
        expect(uiReducer).toBeDefined()
    })
    it('instantiates projectReports', () => {
        expect(projectReportsReducer).toBeDefined()
    })
    it('instantiates departmentReports', () => {
        expect(departmentReportsReducer).toBeDefined()
    })
    it('instantiates conflicts', () => {
        expect(conflictsReducer).toBeDefined()
    })
})
