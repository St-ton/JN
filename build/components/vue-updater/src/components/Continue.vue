<template>
    <div class="form-group align-items-center">
        <button type="submit" @click.prevent="continueInstallation(step + 1)" v-if="step + 1 < steps.length"
            :class="{'btn btn-primary btn-block btn-md': true, 'disabled': loginBtnDisabled}">
            {{ steps[step] }}
        </button>
    </div>
</template>

<script>
    export default {
        name: 'continueinstallation',
        props: ['disable', 'disableBack', 'cb', 'form', 'url'],
        methods: {
            continueInstallation(step) {
                let that = this;

                if(!that.loginBtnDisabled) {
                    that.setLoginBtnDisabled(true);

                    if (typeof that.disable === 'undefined' || that.disable === false) {
                        if (typeof that.cb === 'undefined' || (typeof that.cb === 'function' && that.cb() === true)) {
                            that.form.axiosPost(that.url)
                                .then( data => {
                                    if (data.success) {
                                        that.setStep(step);
                                    }
                                    that.setLoginBtnDisabled(false);
                                })
                                .catch( errors => {
                                    that.setLoginErrors(errors);
                                    that.setLoginBtnDisabled(false);
                                });
                        }
                    }
                }
            }
        }
    };
</script>
<style>
    .btn.pulse-button {
        position: relative;
        box-shadow: 0 0 0 0 rgba(2, 117, 216, 0.7);
        -webkit-animation: pulse 1.25s infinite cubic-bezier(0.66, 0, 0, 1);
        -moz-animation: pulse 1.25s infinite cubic-bezier(0.66, 0, 0, 1);
        -ms-animation: pulse 1.25s infinite cubic-bezier(0.66, 0, 0, 1);
        animation: pulse 1.25s infinite cubic-bezier(0.66, 0, 0, 1);
    }
    .btn.pulse-button:hover
    {
        -webkit-animation: none;-moz-animation: none;-ms-animation: none;animation: none;
    }

    @-webkit-keyframes pulse {to {box-shadow: 0 0 0 15px rgba(2, 117, 216, 0);}}
    @-moz-keyframes pulse {to {box-shadow: 0 0 0 15px rgba(2, 117, 216, 0);}}
    @-ms-keyframes pulse {to {box-shadow: 0 0 0 15px rgba(2, 117, 216, 0);}}
    @keyframes pulse {to {box-shadow: 0 0 0 15px rgba(2, 117, 216, 0);}}
</style>