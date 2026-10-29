import { defineComponent, h, reactive, watchEffect } from 'vue';
import { RouterLink } from 'vue-router';
import { fetchErpPage, postErpAction } from '@/services/api';

export const pageState = reactive<{
    component: string;
    props: Record<string, any>;
    url: string;
    loading: boolean;
}>({
    component: '',
    props: {
        auth: { user: null },
        flash: { success: null, error: null },
        errors: {},
    },
    url: window.location.pathname || '/login',
    loading: false,
});

let navigateCallback: ((url: string) => Promise<void> | void) | null = null;

export function registerSpaNavigator(fn: (url: string) => Promise<void> | void) {
    navigateCallback = fn;
}

export function usePage() {
    return pageState;
}

export const Head = defineComponent({
    name: 'Head',
    props: {
        title: { type: String, default: '' },
    },
    setup(props) {
        watchEffect(() => {
            const appName = import.meta.env.VITE_APP_NAME || 'Gelawitto ERP';
            document.title = props.title ? `${props.title} - ${appName}` : appName;
        });
        return () => null;
    },
});

export const Link = defineComponent({
    name: 'Link',
    props: {
        href: { type: String, required: true },
    },
    setup(props, { slots, attrs }) {
        return () =>
            h(
                RouterLink,
                { to: props.href, ...attrs },
                slots
            );
    },
});

export interface VisitOptions {
    preserveScroll?: boolean;
    onStart?: () => void;
    onSuccess?: (page: any) => void;
    onError?: (errors: Record<string, string>) => void;
    onFinish?: () => void;
}

export const router = {
    async visit(url: string) {
        if (navigateCallback) {
            await navigateCallback(url);
        }
    },
    async get(url: string, options: VisitOptions = {}) {
        options.onStart?.();
        pageState.loading = true;
        try {
            const payload = await fetchErpPage(url);
            pageState.component = payload.component;
            pageState.props = payload.props;
            pageState.url = payload.url;
            if (navigateCallback && window.location.pathname !== payload.url) {
                await navigateCallback(payload.url);
            }
            options.onSuccess?.(pageState);
        } finally {
            pageState.loading = false;
            options.onFinish?.();
        }
    },
    async post(url: string, data: Record<string, any> = {}, options: VisitOptions = {}) {
        options.onStart?.();
        pageState.loading = true;
        try {
            const res = await postErpAction(url, data);

            if (res.status === 422 && res.data?.errors) {
                const flatErrors: Record<string, string> = {};
                for (const [k, v] of Object.entries(res.data.errors)) {
                    flatErrors[k] = Array.isArray(v) ? String(v[0]) : String(v);
                }
                pageState.props.errors = flatErrors;
                options.onError?.(flatErrors);
                return;
            }

            if (res.data && res.data.component && res.data.props) {
                const errs = res.data.props.errors || {};
                if (Object.keys(errs).length > 0) {
                    pageState.props = res.data.props;
                    options.onError?.(errs);
                    return;
                }

                pageState.component = res.data.component;
                pageState.props = res.data.props;
                pageState.url = res.data.url || pageState.url;

                if (navigateCallback && res.data.url && window.location.pathname !== res.data.url) {
                    await navigateCallback(res.data.url);
                }
                options.onSuccess?.(pageState);
            } else {
                const refreshed = await fetchErpPage(pageState.url);
                pageState.component = refreshed.component;
                pageState.props = refreshed.props;
                pageState.url = refreshed.url;
                options.onSuccess?.(pageState);
            }
        } finally {
            pageState.loading = false;
            options.onFinish?.();
        }
    },
};

export function useForm<T extends Record<string, any>>(initialValues: T) {
    const defaults = JSON.parse(JSON.stringify(initialValues)) as T;

    const form = reactive({
        ... JSON.parse(JSON.stringify(initialValues)),
        processing: false,
        errors: {} as Record<string, string>,
        reset(...fields: string[]) {
            if (fields.length === 0) {
                Object.assign(form, JSON.parse(JSON.stringify(defaults)));
            } else {
                for (const f of fields) {
                    (form as any)[f] = JSON.parse(JSON.stringify((defaults as any)[f]));
                }
            }
        },
        clearErrors() {
            form.errors = {};
        },
        async post(url: string, options: VisitOptions = {}) {
            form.processing = true;
            form.errors = {};

            const payload: Record<string, any> = {};
            for (const key of Object.keys(defaults)) {
                payload[key] = (form as any)[key];
            }

            await router.post(url, payload, {
                ...options,
                onError: (errs) => {
                    form.errors = errs;
                    options.onError?.(errs);
                },
                onFinish: () => {
                    form.processing = false;
                    options.onFinish?.();
                },
            });
        },
    });

    return form as T & {
        processing: boolean;
        errors: Record<string, string>;
        reset: (...fields: string[]) => void;
        clearErrors: () => void;
        post: (url: string, options?: VisitOptions) => Promise<void>;
    };
}
