<template>
    <div class="d-print-none">
        <hr>
        <div class="row">
            <div class="col btn-group">
                <b-btn size="lg" variant="warning" @click="setStep(step - 1)" v-if="step > 0 && disableBack === false">
                    <icon name="arrow-left"></icon> Zurück
                </b-btn>
                <b-btn size="lg" variant="primary" @click="continueInstallation(step + 1)" :class="{'pulse-button': disable !== true, disabled: disable === true}" v-if="step + 1 < steps.length">
                    <icon name="share"></icon> Weiter zu Schritt {{ step + 1}} - {{ steps[step + 1] }}
                </b-btn>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name:    'continueinstallation',
    props:   ['disable', 'disableBack', 'cb'],
    methods: {
        continueInstallation(step) {
            if (typeof this.disable === 'undefined' || this.disable === false) {
                if (typeof this.cb === 'undefined' || (typeof this.cb === 'function' && this.cb() === true)) {
                    this.setStep(step);
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
