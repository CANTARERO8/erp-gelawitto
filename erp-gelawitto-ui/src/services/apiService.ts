import axios from '@axios';

const request = async <T = any>(
    method: 'get' | 'post' | 'put' | 'delete',
    url: string,
    data: any = null,
    config: Record<string, any> = {}
): Promise<T> => {
    const response = await axios({
        method,
        url,
        data,
        ...config,
    });
    return response.data as T;
};

export const apiService = (recurso: string) => {
    const base = recurso.replace(/^\/+|\/+$/g, '');
    const buildUrl = (endpoint = '') => {
        const cleanEndpoint = endpoint.replace(/^\/+/, '');
        if (!base) {
            return `/${cleanEndpoint}`;
        }
        return cleanEndpoint ? `/${base}/${cleanEndpoint}` : `/${base}`;
    };

    return {
        get: <T = any>(endpoint = '', params?: Record<string, any>, config: Record<string, any> = {}) =>
            request<T>('get', buildUrl(endpoint), null, { params, ...config }),
        post: <T = any>(endpoint = '', data?: any, config: Record<string, any> = {}) =>
            request<T>('post', buildUrl(endpoint), data, config),
        put: <T = any>(endpoint = '', data?: any, config: Record<string, any> = {}) =>
            request<T>('put', buildUrl(endpoint), data, config),
        delete: <T = any>(endpoint = '', data?: any, config: Record<string, any> = {}) =>
            request<T>('delete', buildUrl(endpoint), null, { data, ...config }),
    };
};

export default apiService;
