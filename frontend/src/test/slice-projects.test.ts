import projectReportsReducer from '../store/slices/projectReportsSlice'
import { describe, it, expect } from 'vitest'

describe('Project Reports Slice Sanity', () => {
    it('instantiates', () => {
        expect(projectReportsReducer).toBeDefined()
    })
})
