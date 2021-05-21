<template>
    <div>
        <jumbotron :header="$t('headerMsg')"
                   :lead="$t('leadMsg')"
                   content="">
        </jumbotron>
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <b-input-group size="md" :prepend="$t('adminUser')">
                        <b-form-input size="35" required v-model="admin.name" type="text" :state="admin.name.length > 0"></b-form-input>
                        <b-input-group-append is-text>
                            <icon name="user"></icon>
                        </b-input-group-append>
                    </b-input-group>
                </div>
                <div class="form-group">
                    <b-input-group size="md">
                        <b-input-group-prepend is-text>
                            {{ $t('adminPassword') }} &nbsp; <a @click="admin.pass = generatePassword()"><icon name="sync"></icon></a>
                        </b-input-group-prepend>
                        <b-form-input size="35" required v-model="admin.pass" type="text" :state="admin.pass.length > 0"></b-form-input>
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
                    <b-input-group size="md" :prepend="$t('syncUser')">
                        <b-form-input size="35" required v-model="wawi.name" type="text" :state="wawi.name.length > 0"></b-form-input>
                        <b-input-group-append is-text>
                            <icon name="user"></icon>
                        </b-input-group-append>
                    </b-input-group>
                </div>
                <div class="form-group">
                    <b-input-group size="md">
                        <b-input-group-prepend is-text>
                            {{ $t('syncPassword') }} &nbsp; <a @click="wawi.pass = generatePassword()"><icon name="sync"></icon></a>
                        </b-input-group-prepend>
                        <b-form-input size="35" required v-model="wawi.pass" type="text" :state="wawi.pass.length > 0 && checkPassword(wawi.pass)"></b-form-input>
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
        const messages = {
            de: {
                headerMsg:     'Admin- und Sync-Benutzer',
                leadMsg:       'Konfigurieren Sie die nötigen Zugangsdaten. Benutzername und Passwort dürfen Groß- und Kleinbuchstaben, Zahlen sowie folgende Sonderzeichen enthalten: !\\"#$%&\'()*+,-./:;=>?@[\\\\]^_`|}~',
                adminPassword: 'Admin-Passwort',
                adminUser:     'Admin-Benutzer',
                syncPassword:  'Sync-Passwort',
                syncUser:      'Sync-Benutzer'
            },
            en: {
                headerMsg:     'Admin and sync user',
                leadMsg:       'Configure the required credentials. User name and password may contain uppercase and lowercase letters, numbers, and the following special characters: !\\"#$%&\'()*+,-./:;=>?@[\\\\]^_`|}~',
                adminPassword: 'Admin password',
                adminUser:     'Admin user',
                syncPassword:  'Sync password',
                syncUser:      'Sync user'
            }
        };
        this.$i18n.add('en', messages.en);
        this.$i18n.add('de', messages.de);
        return {
            admin: {
                name:   'admin',
                pass:   this.generatePassword(),
                locale: this.$i18n.locale()
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
                && this.wawi.pass.length > 0
                && this.checkPassword(this.wawi.pass);
        },
        generatePassword() {
            const crypto = window.crypto || window.msCrypto,
                buf = new Uint8Array(9);
            return typeof crypto !== 'undefined'
                ? btoa(String.fromCharCode.apply(null, crypto.getRandomValues(buf)))
                : '';
        },
        checkPassword(pass) {
            const matches = pass.match(/[^A-Za-z0-9\!"\#\$%&\'\(\)\*\+,-\.\/:;\=\>\?@\[\\\\\]\^_`\|\}~]/);
            return !(matches !== null && matches.length > 0);
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
    .input-group-prepend {
        -ms-flex: 0 0 16.666667%;
        flex: 0 0 16.666667%;
        max-width: 16.666667%;
    }
    .input-group-prepend .input-group-text {
        width: 100%;
    }
</style>
