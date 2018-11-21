import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);
export default new Vuex.Store({
    state: {
        step:             0,
        appVersion:       null,
        availableUpdates: null,
        loginBtnDisabled: false,
        loginErrors:      null,
    },
    mutations: {
        setStep(state, step) {
            if (step > -1) {
                state.step = step;
            }
        },
        nextStep(state) {
            ++state.step;
        },
        prevStep(state) {
            if (state.step > 0) {
                --state.step;
            }
        },
        setApplicationVersion(state, appVersion) {
            state.appVersion = appVersion;
        },
        setAvailableUpdates(state, availableUpdates) {
            state.availableUpdates = availableUpdates;
        },
        setLoginBtnDisabled(state, disabled) {
            state.loginBtnDisabled = disabled;
        },
        setLoginErrors(state, error) {
            state.loginErrors = error;
        },
    },
    getters:   {
        getStep:               state => state.step,
        getApplicationVersion: state => state.appVersion,
        getAvailableUpdates:   state => state.availableUpdates,
        getLoginBtnDisabled:   state => state.loginBtnDisabled,
        getLoginErrors:        state => state.loginErrors,
    }
});
