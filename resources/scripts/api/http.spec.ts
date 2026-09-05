import http from '@/api/http';
import type { AxiosRequestConfig, AxiosResponse } from 'axios';

const response = (config: AxiosRequestConfig, status: number, data: unknown = {}): AxiosResponse => ({
    config: config as AxiosResponse['config'],
    data,
    headers: {},
    status,
    statusText: status === 200 ? 'OK' : 'Error',
});

describe('CSRF recovery', () => {
    const originalAdapter = http.defaults.adapter;
    const originalFetch = global.fetch;

    afterEach(() => {
        http.defaults.adapter = originalAdapter;
        global.fetch = originalFetch;
        jest.restoreAllMocks();
    });

    it('refreshes the CSRF cookie and retries a mutation exactly once', async () => {
        let attempts = 0;
        http.defaults.adapter = async (config) => {
            attempts += 1;
            if (attempts === 1) {
                return Promise.reject({ config, response: response(config, 419) });
            }

            return response(config, 200, { updated: true });
        };
        global.fetch = jest.fn().mockResolvedValue({ ok: true });

        const result = await http.post('/api/client/servers/example/settings/rename', { name: 'Servidor' });

        expect(result.data).toEqual({ updated: true });
        expect(attempts).toBe(2);
        expect(global.fetch).toHaveBeenCalledTimes(1);
        expect(global.fetch).toHaveBeenCalledWith(
            '/sanctum/csrf-cookie',
            expect.objectContaining({ method: 'GET', credentials: 'same-origin', cache: 'no-store' })
        );
    });

    it('does not loop when the retried request still returns 419', async () => {
        let attempts = 0;
        http.defaults.adapter = async (config) => {
            attempts += 1;
            return Promise.reject({ config, response: response(config, 419) });
        };
        global.fetch = jest.fn().mockResolvedValue({ ok: true });

        await expect(
            http.post('/api/client/servers/example/settings/rename', { name: 'Servidor' })
        ).rejects.toMatchObject({
            response: { status: 419 },
        });

        expect(attempts).toBe(2);
        expect(global.fetch).toHaveBeenCalledTimes(1);
    });

    it('coalesces concurrent CSRF refreshes from a busy server screen', async () => {
        let attempts = 0;
        let finishRefresh!: () => void;
        const refreshPending = new Promise<void>((resolve) => {
            finishRefresh = resolve;
        });
        http.defaults.adapter = async (config) => {
            attempts += 1;
            if (attempts <= 2) {
                return Promise.reject({ config, response: response(config, 419) });
            }

            return response(config, 200, { updated: true });
        };
        global.fetch = jest.fn().mockImplementation(async () => {
            await refreshPending;
            return { ok: true };
        });

        const first = http.post('/api/client/servers/first/settings/rename', { name: 'Primeiro' });
        const second = http.post('/api/client/servers/second/settings/rename', { name: 'Segundo' });
        await Promise.resolve();
        finishRefresh();

        await expect(Promise.all([first, second])).resolves.toHaveLength(2);
        expect(attempts).toBe(4);
        expect(global.fetch).toHaveBeenCalledTimes(1);
    });
});
