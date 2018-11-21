<template>
    <div id="updater-wrapper">
        <AvailableUpdates class="steps" id="step-1" v-if="step === 1"></AvailableUpdates>
        <Login class="steps" id="step-0" v-if="step === 0"></Login>
    </div>
</template>

<script>
    import AvailableUpdates from "./AvailableUpdates";
    import PageHeader from './Header';
    import Login from "./Login";
    import axios from "axios";

    export default {
        name: 'Updater',
        components: {
            PageHeader,
            Login,
            AvailableUpdates,
        },
        methods: {
            setStepFromSession() {
                let that = this;

                axios.get(that.$getApiUrl('setStepFromSession'))
                    .then(response => {
                        that.setStep(response.data.step);
                    })
                    .catch(error => {
                        console.log('can\'t step from session', error);
                    });
            },
            getApplicationVersionAjaxCall() {
                let that = this;

                axios.get(that.$getApiUrl('getApplicationVersion'))
                    .then(response => {
                        that.setApplicationVersion(response.data.version);
                    })
                    .catch(error => {
                        console.log('can\'t resolve application version', error);
                    });
            },
            getAvailableUpdatesAjaxCall() {
                let that = this;

                axios.get(that.$getApiUrl('getAvailableUpdates'))
                    .then(response => {
                        that.setAvailableUpdates(response.data.updates);
                    })
                    .catch(error => {
                        console.log('can\'t resolve available updates', error);
                    });
            }
        },
        mounted() {
            this.setStepFromSession();
            this.getApplicationVersionAjaxCall();
            this.getAvailableUpdatesAjaxCall();
        }
    };
</script>

<style scoped>
    .list-group {
        width: 100%;
    }
</style>
