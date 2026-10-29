export interface ErpPagePayload {
    component: string;
    props: Record<string, any>;
    url: string;
    version?: string;
}

const API_BASE = import.meta.env.VITE_API_BASE_URL || '/erp-api';

function getXsrfToken(): string {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]*)/);
    return match ? decodeURIComponent(match[1]) : '';
}

function normalizeUrl(path: string): string {
    const cleanPath = path.startsWith('/') ? path : `/${path}`;
    return `${API_BASE}${cleanPath}`;
}

export async function fetchErpPage(path: string): Promise<ErpPagePayload> {
    const response = await fetch(normalizeUrl(path), {
        method: 'GET',
        credentials: 'include',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-Inertia': 'true',
            'X-Spa-Client': 'erp-gelawitto-ui',
        },
    });

    if (response.status === 409) {
        const location = response.headers.get('X-Inertia-Location') || '/login';
        return fetchErpPage(location);
    }

    const data = await response.json();
    return data as ErpPagePayload;
}

export async function postErpAction(
    path: string,
    payload: Record<string, any> = {}
): Promise<{ ok: boolean; status: number; data: any }> {
    const xsrf = getXsrfToken();
    const response = await fetch(normalizeUrl(path), {
        method: 'POST',
        credentials: 'include',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-Inertia': 'true',
            'X-Spa-Client': 'erp-gelawitto-ui',
            ...(xsrf ? { 'X-XSRF-TOKEN': xsrf } : {}),
        },
        body: JSON.stringify(payload),
    });

    let data: any = null;
    try {
        data = await response.json();
    } catch {
        data = null;
    }

    return {
        ok: response.ok,
        status: response.status,
        data,
    };
}
