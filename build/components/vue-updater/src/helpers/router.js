import Vue from 'vue';
import Router from 'vue-router';
import Updater from '@/components/Updater';

Vue.use(Router);

export default new Router({
    routes: [
        {
            path:      '/',
            name:      'Updater',
            component: Updater
        }
    ]
});
