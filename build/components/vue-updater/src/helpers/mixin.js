import {mapGetters} from 'vuex';
import store from './store';
import Continue from '../components/Continue';

export default {
    data() {
        return {
            steps: [
                'Anmelden',
                'Ausgewähltes Update installieren',
                'Zum Shop Backend'
            ]
        };
    },
    components: {
        Continue,
    },
    methods: {
        setStep(step) {
            store.commit('setStep', step);
            store.commit('setProgress', step / (this.steps.length - 1) * 100);
        },
        setApplicationVersion(appVersion) {
            store.commit('setApplicationVersion', appVersion);
        },
        setAvailableUpdates(availableUpdates) {
            store.commit('setAvailableUpdates', availableUpdates);
        },
        setLoginBtnDisabled(disabled) {
            store.commit('setLoginBtnDisabled', disabled);
        },
        setLoginErrors(error) {
            store.commit('setLoginError', error);
        }
    },
    computed: mapGetters({
        step:             'getStep',
        appVersion:       'getApplicationVersion',
        availableUpdates: 'getAvailableUpdates',
        loginBtnDisabled: 'getLoginBtnDisabled',
        loginErrors:      'getLoginErrors',
    })
};
