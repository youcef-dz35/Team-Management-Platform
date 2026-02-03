import { createSlice } from '@reduxjs/toolkit'
import { describe, it, expect } from 'vitest'

describe('Toolkit Sanity', () => {
    it('imports', () => {
        expect(createSlice).toBeDefined()
    })
})
