import Vue from 'vue';
import Vuex from 'vuex';
import App from './App';
import BootstrapVue from 'bootstrap-vue';
import 'vue-awesome/icons/sync';
import 'vue-awesome/icons/home';
import 'vue-awesome/icons/user';
import 'vue-awesome/icons/arrow-left';
import 'vue-awesome/icons/share';
import 'vue-awesome/icons/exchange-alt';
import 'vue-awesome/icons/lock';
import 'vue-awesome/icons/database';
import 'vue-awesome/icons/check';
import 'vue-awesome/icons/exclamation-triangle';
import 'vue-awesome/icons/print';
import 'vue-awesome/icons/save';
import 'vue-awesome/icons/external-link-alt';
import Icon from 'vue-awesome/components/Icon';
import 'jquery/dist/jquery.slim.min'
import 'popper.js/dist/umd/popper.min'
import 'bootstrap/dist/js/bootstrap.min'
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import router from './helpers/router';
import mixin from './helpers/mixin';
import store from './helpers/store';
import plugin from './helpers/plugin';

Vue.use(BootstrapVue);
Vue.use(Vuex);
Vue.use({install: plugin});
Vue.component('icon', Icon);
Vue.mixin(mixin);
Vue.config.productionTip = false;

/* eslint-disable no-new */
new Vue({
    el:     '#app',
    router,
    store,
    render: h => h(App)
});
