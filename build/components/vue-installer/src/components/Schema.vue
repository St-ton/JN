<template>
    <div>
        <jumbotron :header="$t('headerMsg')"
                   :lead="$t('leadMsg')"
                   content="">
        </jumbotron>
        <div class="row">
            <div class="col">
            </div>
        </div>

        <div class="result mt-3" v-if="!finished">
            <b-alert variant="info" show><icon name="sync" spin></icon> {{ $t('installing') }}.</b-alert>
        </div>
        <b-alert variant="danger" show v-if="networkError !== false">
            <icon name="exclamation-triangle"></icon> {{ $t('networkError') }} <div v-html="networkError"></div>
        </b-alert>

        <div class="result mt-3" v-if="error !== null">
            <b-alert :variant="error ? 'danger' : 'success'" show>
                <icon :name="error ? 'exclamation-triangle' : 'check'"></icon> <span v-html="$t(msg)"></span>
            </b-alert>
        </div>
        <continue :disableBack="false" :disable="error !== false"></continue>
    </div>
</template>

<script>
import axios from 'axios';
import qs from 'qs';
export default {
    name: 'schema',
    data() {
        const messages = {
            de: {
                unreachable:    'URL {url} nicht erreichbar.',
                noNiceDB:       'NiceDB nicht initialisiert.',
                installing:     'Installiere... bitte warten.',
                headerMsg:      'Schema importieren',
                leadMsg:        'Warten Sie bitte, bis das SQL-Schema importiert wurde',
                executeSuccess: 'Erfolgreich ausgeführt'
            },
            en: {
                unreachable:    'URL {url} unreachable.',
                noNiceDB:       'Cannot initialize NiceDB.',
                installing:     'Installing... please wait.',
                headerMsg:      'Import scheme',
                leadMsg:        'Please wait while the sql scheme is being imported',
                executeSuccess: 'Successfully executed'
            }
        };
        this.$i18n.add('en', messages.en);
        this.$i18n.add('de', messages.de);
        return {
            finished:     false,
            error:        null,
            msg:          null,
            networkError: false
        };
    },
    mounted() {
        const postData     = qs.stringify({
            admin: this.$store.state.adminUser,
            wawi:  this.$store.state.wawiUser,
            db:    this.$store.state.database
        });
        axios.post(this.$getApiUrl('doinstall'), postData)
            .then(response => {
                if (!response.data.payload) {
                    this.networkError = response.data;
                    return;
                }
                this.networkError = false;
                this.$store.commit('setSecretKey', response.data.payload.secretKey);
                if (this.$store.state.installDemoData === true) {
                    this.finished = false;
                    axios.post(this.$getApiUrl('installdemodata'), postData)
                        .then(r2 => {
                            this.error = !r2.data.ok;
                            this.msg = r2.data.msg;
                            this.finished = true;
                        })
                        .catch(e2 => {
                            this.error = true;
                            this.msg = e2.response
                                ? e2.response
                                : this.$i18n.translate('unreachable', { url: this.$getApiUrl('installdemodata') });
                        });
                } else {
                    this.error = !response.data.ok;
                    this.msg = response.data.msg;
                    this.finished = true;
                }
            })
            .catch(err => {
                this.error = true;
                this.msg = err.response
                    ? err.response
                    : this.$i18n.translate('unreachable', { url: this.$getApiUrl('doinstall') });
            });
    }
};
</script>
<style scoped>
    .input-group-addon.fixed-addon {
        width: 150px;
        text-align: right;
    }
</style>
