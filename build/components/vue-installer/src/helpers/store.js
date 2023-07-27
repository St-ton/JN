import Vue from 'vue';
import Vuex from 'vuex';

Vue.use(Vuex);
export default new Vuex.Store({
    state: {
        step:              0,
        wizardStep:        1,
        progress:          0,
        adminUser:         null,
        wawiUser:          null,
        database:          null,
        secretKey:         null,
        shopURL:           null,
        installDemoData:   false,
        demoProducts:      null,
        demoManufacturers: null,
        demoCategories:    null
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
        setProgress(state, progress) {
            state.progress = progress;
        },
        setShopURL(state, url) {
            state.shopURL = url;
        },
        setAdminUser(state, user) {
            state.adminUser = user;
        },
        setWawiUser(state, user) {
            state.wawiUser = user;
        },
        setSecretKey(state, key) {
            state.secretKey = key;
        },
        setDBCredentials(state, db) {
            state.database = db;
        },
        setDoInstallDemoData(state, value) {
            state.installDemoData = value;
        },
        setDemoDataProductCount(state, value) {
            state.demoProducts = value;
        },
        setDemoDataManufacturerCount(state, value) {
            state.demoManufacturers = value;
        },
        setDemoDataCategoryCount(state, value) {
            state.demoCategories = value;
        }
    },
    getters:   {
        getStep:                      state => state.step,
        getProgress:                  state => state.progress,
        getAdminUser:                 state => state.adminUser,
        getWawiUser:                  state => state.wawiUser,
        getSecretKey:                 state => state.secretKey,
        getShopURL:                   state => state.shopURL,
        getDemoDataProductCount:      state => state.demoProducts,
        getDemoDataCategoryCount:     state => state.demoCategories,
        getDemoDataManufacturerCount: state => state.demoManufacturers,
        installDemoData:              state => state.installDemoData
    }
});
