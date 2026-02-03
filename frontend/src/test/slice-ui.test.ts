import uiReducer from '../store/slices/uiSlice'
import { describe, it, expect } from 'vitest'

describe('UI Slice Sanity', () => {
    it('instantiates', () => {
        expect(uiReducer).toBeDefined()
    })
})
