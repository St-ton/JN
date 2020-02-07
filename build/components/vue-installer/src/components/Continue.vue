<template>
    <div class="d-print-none">
        <hr>
        <div class="row">
            <div class="col btn-group">
                <b-btn size="lg" variant="warning" @click="setStep(istep - 1)" v-if="istep > 0 && disableBack === false">
                    <icon name="arrow-left"></icon> {{ $t('back') }}
                </b-btn>
                <b-btn size="lg" variant="primary" @click="continueInstallation(istep + 1)" :class="{'pulse-button': disable !== true, disabled: disable === true}" v-if="istep + 1 < isteps.length">
                    <icon name="share"></icon> {{ $t('continueToStep') }} {{ istep + 1}} - {{ $t(isteps[istep + 1]) }}
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
        continueInstallation(istep) {
            if (typeof this.disable === 'undefined' || this.disable === false) {
                if (typeof this.cb === 'undefined' || (typeof this.cb === 'function' && this.cb() === true)) {
                    this.setStep(istep);
                }
            }
        }
    },
    data() {
        const messages = {
            de: {
                continueToStep: 'Weiter zu Schritt',
                back:           'Zurück',
                step1:          'Hallo',
                step2:          'Vorige Installation prüfen',
                step3:          'Dateirechte',
                step4:          'Systemcheck',
                step5:          'Datenbankdaten',
                step6:          'Adminnutzer',
                step7:          'Schema',
                step8:          'Abschluss'
            },
            en: {
                continueToStep: 'Continue to step',
                back:           'Back',
                step1:          'Hello',
                step2:          'Check for previous installation',
                step3:          'Permission check',
                step4:          'System check',
                step5:          'Database',
                step6:          'Admin user',
                step7:          'Scheme',
                step8:          'Summary'
            }
        };
        this.$i18n.add('en', messages.en);
        this.$i18n.add('de', messages.de);
        return { };
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
