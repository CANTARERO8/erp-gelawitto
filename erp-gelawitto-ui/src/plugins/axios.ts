import axios from 'axios';
import router from '@/router';

const axiosIns = axios.create({
    baseURL: import.meta.env.VITE_API_URL || '/api',
    withCredentials: true,
    withXSRFToken: true,
    headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

axiosIns.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error?.response?.status === 401 && router.currentRoute.value.path !== '/login') {
            router.push('/login');
        }
        return Promise.reject(error);
    }
);

export default axiosIns;
