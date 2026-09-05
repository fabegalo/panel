import http from '@/api/http';
import { AxiosError } from 'axios';
import { History } from 'history';

export const setupInterceptors = (history: History) => {
    http.interceptors.response.use(
        (resp) => resp,
        (error: AxiosError) => {
            if (
                (error.response?.status === 401 || error.response?.status === 419) &&
                !window.location.pathname.startsWith('/auth')
            ) {
                // A failed CSRF retry means the Laravel session itself no longer exists. A full
                // navigation clears the stale in-memory user and always gives the login form a new
                // CSRF cookie, including when Sign Out was the action that discovered the expiry.
                window.location.assign('/auth/login');
            }

            if (error.response?.status === 400) {
                if (
                    (error.response?.data as Record<string, any>).errors?.[0].code === 'TwoFactorAuthRequiredException'
                ) {
                    if (!window.location.pathname.startsWith('/account')) {
                        history.replace('/account', { twoFactorRedirect: true });
                    }
                }
            }
            throw error;
        }
    );
};
