import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import Layout from './Layouts/Layout.vue';
import BecomeSeller from './Pages/Dashboard/BecomeSeller.vue';
import ToastWrapper from '@/Layouts/ToastWrapper.vue'

createInertiaApp({
    title: (title) => `Campus Connect ${title}`,
    resolve: name => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true })
        let page = pages[`./Pages/${name}.vue`]
        if (!page) {
            throw new Error(`Page ${name} not found`);
        }
        page.default.layout = page.default.layout || Layout
        return page
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .component('Head', Head)
            .component('Link', Link)
            .component('ToastWrapper', ToastWrapper)
            .mount(el);
    },
    progress: {    
        color: 'red',
        includeCSS: true,
        showSpinner: true,
    },
});