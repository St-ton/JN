import Vue from 'vue';
import Vuex from 'vuex';
import vuexI18n from 'vuex-i18n';
import App from './App.vue';
import { BootstrapVue, BootstrapVueIcons} from 'bootstrap-vue';
import 'bootstrap/dist/css/bootstrap.css';
import 'bootstrap-vue/dist/bootstrap-vue.css';
import router from './helpers/router';
import mixin from './helpers/mixin';
import store from './helpers/store';
import plugin from './helpers/plugin';

Vue.use(BootstrapVue);
Vue.use(BootstrapVueIcons);
Vue.use(Vuex);
Vue.use(vuexI18n.plugin, store);
Vue.use({ install: plugin });
Vue.mixin(mixin);
Vue.i18n.set('de');
Vue.config.productionTip = false;

new Vue({
  router,
  store,
  render: (h) => h(App)
}).$mount('#app')
