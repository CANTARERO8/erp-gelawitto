import axios from '@/plugins/axios';

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

export const apiService = (recurso: string) => ({
    get: <T = any>(endpoint = '', params?: Record<string, any>, config: Record<string, any> = {}) =>
        request<T>('get', endpoint ? `/${recurso}/${endpoint}` : `/${recurso}`, null, { params, ...config }),
    post: <T = any>(endpoint = '', data?: any, config: Record<string, any> = {}) =>
        request<T>('post', endpoint ? `/${recurso}/${endpoint}` : `/${recurso}`, data, config),
    put: <T = any>(endpoint = '', data?: any, config: Record<string, any> = {}) =>
        request<T>('put', endpoint ? `/${recurso}/${endpoint}` : `/${recurso}`, data, config),
    delete: <T = any>(endpoint = '', data?: any, config: Record<string, any> = {}) =>
        request<T>('delete', endpoint ? `/${recurso}/${endpoint}` : `/${recurso}`, null, { data, ...config }),
});

export default apiService;
