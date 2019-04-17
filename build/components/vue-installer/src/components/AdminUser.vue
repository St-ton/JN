<template>
    <div>
        <jumbotron header="Admin- und Sync-Benutzer"
                     lead="Konfigurieren Sie die nötigen Zugangsdaten"
                     content="">
        </jumbotron>
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <b-input-group size="md" prepend="Admin-Benutzer">
                        <b-form-input size="35" required v-model="admin.name" type="text" :state="admin.name.length ? 'success' : 'warning'"></b-form-input>
                        <b-input-group-append is-text>
                            <icon name="user"></icon>
                        </b-input-group-append>
                    </b-input-group>
                </div>
                <div class="form-group">
                    <b-input-group size="md">
                        <b-input-group-prepend is-text>
                            Admin-Passwort &nbsp; <a @click="admin.pass = generatePassword()"><icon name="sync"></icon></a>
                        </b-input-group-prepend>
                        <b-form-input size="35" required v-model="admin.pass" type="text" :state="admin.pass.length ? 'success' : 'warning'"></b-form-input>
                        <b-input-group-append is-text>
                            <icon name="lock"></icon>
                        </b-input-group-append>
                    </b-input-group>
                </div>
                <hr>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <b-input-group size="md" prepend="Sync-Benutzer">
                        <b-form-input size="35" required v-model="wawi.name" type="text" :state="wawi.name.length ? 'success' : 'warning'"></b-form-input>
                        <b-input-group-append is-text>
                            <icon name="user"></icon>
                        </b-input-group-append>
                    </b-input-group>
                </div>
                <div class="form-group">
                    <b-input-group size="md">
                        <b-input-group-prepend is-text>
                            Sync-Passwort &nbsp; <a @click="wawi.pass = generatePassword()"><icon name="sync"></icon></a>
                        </b-input-group-prepend>
                        <b-form-input size="35" required v-model="wawi.pass" type="text" :state="wawi.pass.length ? 'success' : 'warning'"></b-form-input>
                        <b-input-group-append is-text>
                            <icon name="lock"></icon>
                        </b-input-group-append>
                    </b-input-group>
                </div>
            </div>
        </div>
        <continue :cb="saveUsers" :disableBack="false"></continue>
    </div>
</template>

<script>
export default {
    name: 'adminuser',
    data() {
        return {
            admin: {
                name: 'admin',
                pass: this.generatePassword()
            },
            wawi:  {
                name: 'sync',
                pass: this.generatePassword()
            }
        };
    },
    methods: {
        saveUsers() {
            this.$store.commit('setAdminUser', this.admin);
            this.$store.commit('setWawiUser', this.wawi);
            return this.admin.name.length > 0
                && this.admin.pass.length > 0
                && this.wawi.name.length > 0
                && this.wawi.pass.length > 0;
        },
        generatePassword() {
            let crypto = window.crypto || window.msCrypto,
                buf    = new Uint8Array(9);
            return typeof crypto !== 'undefined'
                ? btoa(String.fromCharCode.apply(null, crypto.getRandomValues(buf)))
                : '';
        }
    }
};
</script>
<style scoped>
    .input-group-addon.fixed-addon {
        width: 170px;
        text-align: right;
    }
    a {
        cursor: pointer;
    }
</style>
