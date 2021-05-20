<template>
    <div>
        <jumbotron :header="$t('headerMsg')"
                   :lead="$t('leadMsg')"
                   content="">
        </jumbotron>
        <div class="row">
            <div class="col">
                <b-alert variant="danger" show v-if="isInstalled">
                    <icon name="exclamation-triangle"></icon> {{ $t('msgInstalled') }}
                </b-alert>
                <b-alert variant="success" show v-else>
                    <icon name="check"></icon> {{ $t('msgNoConfig') }}
                </b-alert>
                <b-alert variant="warning" show v-if="protoWarning && !isInstalled">
                    <icon name="exclamation-triangle"></icon> {{ $t('titleProtoWarning') }} {{ shopURL }}<br>
                    {{ $t('msgProtoWarning') }}
                </b-alert>
                <b-alert variant="danger" show v-if="networkError !== false">
                    <icon name="exclamation-triangle"></icon> {{ $t('networkError') }} <div v-html="networkError"></div>
                </b-alert>
                <b-alert variant="danger" show v-if="phpError !== false">
                    <icon name="exclamation-triangle"></icon> {{ phpError }}
                </b-alert>
                <b-form-checkbox v-model="anyway" value="true" unchecked-value="false" v-if="protoWarning && !isInstalled">
                    {{ $t('continueAnyway') }}
                </b-form-checkbox>
            </div>
        </div>
        <continue :disableBack="false" :disable="isInstalled || networkError !== false || phpError !== false || (protoWarning === true && anyway !== 'true')"></continue>
    </div>
</template>

<script>
import axios from 'axios';
import { mapGetters } from 'vuex';
export default {
    name: 'installedcheck',
    data() {
        const messages = {
            de: {
                msgInstalled:      'Installation kann nicht fortgesetzt werden, da der Shop bereits installiert wurde.',
                msgNoConfig:       'Keine config.JTL-Shop.ini.php gefunden.',
                networkError:      'Netzwerkfehler:',
                headerMsg:         'Bestehende Installation',
                unreachable:       'URL {url} nicht erreichbar.',
                leadMsg:           'Prüft, ob der Shop bereits installiert ist',
                titleProtoWarning: 'Sie scheinen kein SSL zu nutzen! Erkannte URL: ',
                msgProtoWarning:   'Falls Ihr Webserver SSL unterstützt, laden Sie die Installation bitte über eine'
                    + ' https-URL neu, andernfalls bestätigen Sie die Checkbox weiter unten.',
                continueAnyway:    'Trotzdem fortfahren'
            },
            en: {
                msgInstalled:      'Cannot continue installation - Shop already installed.',
                msgNoConfig:       'No config.JTL-Shop.ini.php found.',
                networkError:      'Network error:',
                headerMsg:         'Existing installation',
                unreachable:       'URL {url} unreachable.',
                leadMsg:           'Checks if the shop was installed before',
                titleProtoWarning: 'You seem to use an unsecured URL! We got the URL: ',
                msgProtoWarning:   'Please restart the installation via a secured URL if your webserver supports SSL,'
                    + ' otherwise check the checkbox below.',
                continueAnyway:    'Continue anyway'
            }
        };
        this.$i18n.add('en', messages.en);
        this.$i18n.add('de', messages.de);
        return {
            isInstalled:  false,
            phpError:     false,
            networkError: false,
            protoWarning: false,
            anyway:       false
        };
    },
    mounted() {
        axios.get(this.$getApiUrl('installedcheck'))
            .then(response => {
                if (response.data.error) {
                    this.phpError = response.data.error;
                    return;
                }
                if (!response.data.installed && !response.data.shopURL) {
                    this.networkError = response.data;
                    return;
                }
                this.phpError = false;
                this.networkError = false;
                this.isInstalled = response.data.installed;
                if (response.data.shopURL.indexOf('https:') === -1
                    && response.data.shopURL.indexOf('localhost') === -1
                ) {
                    this.protoWarning = true;
                }
                this.$store.commit('setShopURL', response.data.shopURL);
            })
            .catch(error => {
                this.networkError = error.response
                    ? error.response
                    : this.$i18n.translate('unreachable', { url: this.$getApiUrl('installedcheck') });
            });
    },
    computed: mapGetters({
        shopURL: 'getShopURL'
    })
};
</script>
