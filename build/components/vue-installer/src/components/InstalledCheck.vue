<template>
    <div>
        <jumbotron header="Bestehende Installation"
                     lead="Prüft, ob der Shop bereits installiert ist"
                     content="">
        </jumbotron>
        <div class="row">
            <div class="col">
                <b-alert variant="danger" show v-if="isInstalled">
                    <icon name="exclamation-triangle"></icon> Installation kann nicht fortgesetzt werden, da der Shop bereits installiert wurde.
                </b-alert>
                <b-alert variant="success" show v-else>
                    <icon name="check"></icon> Keine config.JTL-Shop.ini.php gefunden.
                </b-alert>
                <b-alert variant="danger" show v-if="networkError !== false">
                    <icon name="exclamation-triangle"></icon> Netzwerkfehler: {{ networkError }}
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
            axios.get(this.$getApiUrl('installedcheck'))
                .then(response => {
                    this.isInstalled = response.data.installed;
                    this.$store.commit('setShopURL', response.data.shopURL);
                })
                .catch(error => {
                    this.networkError = error.response
                        ? error.response
                        : `URL ${this.$getApiUrl('installedcheck')} nicht erreichbar.`;
                });
            return {
                isInstalled,
                networkError
            };
        }
    };
</script>
