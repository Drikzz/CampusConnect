import '../css/app.css';
import { createApp, h } from 'vue';
import { createInertiaApp, Head, Link, router } from '@inertiajs/vue3';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import Layout from './Layouts/Layout.vue';

// Add global registration data cleanup function with clear logging
const clearRegistrationData = () => {
    //console.log("Clearing registration data from sessionStorage");
    const formFields = [
        'user_type_id', 
        'grade_level_id', 
        'wmsu_dept_id', 
        'first_name', 
        'middle_name', 
        'last_name', 
        'gender', 
        'date_of_birth', 
        'phone',
        'username',
        'wmsu_email',
        'from_account_info',
        'registration_in_progress'
    ];
    
    formFields.forEach(field => {
        sessionStorage.removeItem(field);
    });
};

// Define specific registration routes with proper route structure
const registrationRoutes = [
    '/register',
    '/register/details',
    '/register/profile-picture'
];

// Add global navigation listener with improved route detection
router.on('navigate', (event) => {
    const currentPath = window.location.pathname;
    const targetPath = new URL(event.detail.page.url, window.location.origin).pathname;
    
    // console.log(`Navigation: ${currentPath} -> ${targetPath}`);
    
    // Check if navigating away from registration flow
    const isCurrentRegistrationRoute = registrationRoutes.some(route => currentPath.startsWith(route));
    const isTargetRegistrationRoute = registrationRoutes.some(route => targetPath.startsWith(route));
    
    // Only clear when navigating away from the entire registration flow
    if (isCurrentRegistrationRoute && !isTargetRegistrationRoute) {
        // console.log("Navigating away from registration - clearing data");
        clearRegistrationData();
    }
});

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
        // Check if we should clear registration data
        if (props.initialPage.props.clearStorage) {
            clearRegistrationData();
        }
        
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .component('Head', Head)
            .component('Link', Link)
            .mount(el);
            
        // Fix: Move this code after plugin initialization and remove the undefined check
        // Use the router instance that's already imported instead of accessing window.Inertia
        router.on('navigate', (event) => {
            // Check if the response has the no-scroll header
            if (event.detail.page.response?.headers?.['x-inertia-scroll-restoration'] === 'false') {
                window.scrollTo(0, 0);
                event.detail.preserveScroll = true;
            }
        });
    },
    progress: {    
        color: 'red',
        includeCSS: true,
        showSpinner: true,
        delay: 250,
    },
});