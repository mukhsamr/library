import './bootstrap';
import './bootstrap-modal';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/inertia-vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { InertiaProgress } from '@inertiajs/progress'
import mitt from 'mitt'

const ZiggyVue = {
    install(app) {
        app.config.globalProperties.route = (...args) => globalThis.route(...args);
    },
};

createInertiaApp({
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)

        app.provide('mitt', mitt())
        app.mount(el)
    },
});

InertiaProgress.init();