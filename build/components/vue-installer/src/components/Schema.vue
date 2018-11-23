<template>
    <div>
        <jumbotron header="Schema importieren"
                     lead="Warten Sie bitte, bis das SQL-Schema importiert wurde"
                     content="">
        </jumbotron>
        <div class="row">
            <div class="col">
            </div>
        </div>

        <div class="result mt-3" v-if="!finished">
            <b-alert variant="info" show><icon name="sync" spin></icon> Installiere... bitte warten.</b-alert>
        </div>

        <div class="result mt-3" v-if="error !== null">
            <b-alert :variant="error ? 'danger' : 'success'" show>
                <icon :name="error ? 'exclamation-triangle' : 'check'"></icon>
                <span v-html="msg"></span>
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
            let finished = false,
                error    = null,
                msg      = null,
                postData = qs.stringify({
                    admin: this.$store.state.adminUser,
                    wawi:  this.$store.state.wawiUser,
                    db:    this.$store.state.database
                });
            axios.post(this.$getApiUrl('doinstall'), postData)
                .then(response => {
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
                                this.msg   = e2.response
                                    ? e2.response
                                    : `URL ${this.$getApiUrl('installdemodata')} nicht erreichbar.`;
                            });
                    } else {
                        this.error = !response.data.ok;
                        this.msg = response.data.msg;
                        this.finished = true;
                    }
                })
                .catch(err => {
                    this.error = true;
                    this.msg   = err.response
                        ? err.response
                        : `URL ${this.$getApiUrl('doinstall')} nicht erreichbar.`;
                });
            return {
                finished,
                error,
                msg
            };
        }
    };
</script>
<style scoped>
    .input-group-addon.fixed-addon {
        width: 150px;
        text-align: right;
    }
</style>