import axios from 'axios';
import router from '@/router';

const axiosIns = axios.create({
    baseURL: import.meta.env.VITE_API_URL || '/erp-api',
    withCredentials: true,
    headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-Inertia': 'true',
        'X-Spa-Client': 'erp-gelawitto-ui',
    },
});

axiosIns.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('token');
        config.headers = config.headers || {};
        config.headers['X-Requested-With'] = 'XMLHttpRequest';

        const xsrfMatch = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);
        if (xsrfMatch) {
            config.headers['X-XSRF-TOKEN'] = decodeURIComponent(xsrfMatch[1]);
        }

        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }

        return config;
    },
    (error) => Promise.reject(error)
);

axiosIns.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error?.response?.status === 401) {
            localStorage.removeItem('isAuthenticated');
            localStorage.removeItem('usuario');
            localStorage.removeItem('token');
            router.push('/login');
        }
        return Promise.reject(error);
    }
);

export default axiosIns;
