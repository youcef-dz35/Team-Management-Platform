import api from '../lib/axios'
import { describe, it, expect } from 'vitest'

describe('Axios Sanity', () => {
    it('instantiates', () => {
        expect(api).toBeDefined()
    })
})
