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
                <b-alert variant="danger" show v-if="networkError !== false">
                    <icon name="exclamation-triangle"></icon> {{ $t('networkError') }} {{ networkError }}
                </b-alert>
            </div>
        </div>
        <continue :disableBack="false" :disable="isInstalled || networkError !== false"></continue>
    </div>
</template>

<script>
import axios from 'axios';
export default {
    name: 'installedcheck',
    data() {
        let isInstalled  = false,
            networkError = false;
        const messages = {
            de: {
                msgInstalled: 'Installation kann nicht fortgesetzt werden, da der Shop bereits installiert wurde.',
                msgNoConfig:  'Keine config.JTL-Shop.ini.php gefunden.',
                networkError: 'Netzwerkfehler:',
                headerMsg:    'Bestehende Installation',
                unreachable:  'URL {url} nicht erreichbar.',
                leadMsg:      'Prüft, ob der Shop bereits installiert ist'
            },
            en: {
                msgInstalled: 'Cannot continue installation - Shop already installed.',
                msgNoConfig:  'No config.JTL-Shop.ini.php found.',
                networkError: 'Network error:',
                headerMsg:    'Existing installation',
                unreachable:  'URL {url} unreachable.',
                leadMsg:      'Checks if the shop was installed before'
            }
        };
        this.$i18n.add('en', messages.en);
        this.$i18n.add('de', messages.de);
        axios.get(this.$getApiUrl('installedcheck'))
            .then(response => {
                this.isInstalled = response.data.installed;
                this.$store.commit('setShopURL', response.data.shopURL);
            })
            .catch(error => {
                this.networkError = error.response
                    ? error.response
                    : this.$i18n.translate('unreachable', { url: this.$getApiUrl('installedcheck') });
            });
        return {
            isInstalled,
            networkError
        };
    }
};
</script>
