import Vue from 'vue';
import Router from 'vue-router';
import Installer from '@/components/Installer.vue';

Vue.use(Router);

export default new Router({
    routes: [
        {
            path:      '/',
            name:      'Installer',
            base:      '/install',
            component: Installer
        }
    ]
});
