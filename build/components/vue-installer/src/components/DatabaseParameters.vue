<template>
    <div>
        <jumbotron :header="$t('headerMsg')"
                   :lead="$t('leadMsg')"
                   :content="$t('contentMsg')">
        </jumbotron>
        <form>
            <div class="row">
                <div class="col">
                    <div class="form-group">
                        <b-input-group size="md" :prepend="$t('hostPrep')">
                            <b-form-input size="35" required v-model="db.host" type="text" :placeholder="$t('dbhost')"
                                          :state="db.host.length ? true : false"></b-form-input>
                            <b-input-group-append is-text>
                                <icon name="home"></icon>
                            </b-input-group-append>
                        </b-input-group>
                    </div>
                    <div class="form-group">
                        <b-input-group size="md" :prepend="$t('socketPrep')">
                            <b-form-input size="35" v-model="db.socket" type="text"
                                          autocomplete="off"
                                          :placeholder="$t('socketPlaceholder')"></b-form-input>
                            <b-input-group-append is-text>
                                <icon name="exchange-alt"></icon>
                            </b-input-group-append>
                        </b-input-group>
                    </div>
                    <div class="form-group">
                        <b-input-group size="md" :prepend="$t('userNamePrep')">
                            <b-form-input size="35" required v-model="db.user" type="text"
                                          :placeholder="$t('userNamePlaceholder')"
                                          autocomplete="off"
                                          :state="db.user.length > 0"></b-form-input>
                            <b-input-group-append is-text>
                                <icon name="user"></icon>
                            </b-input-group-append>
                        </b-input-group>
                    </div>
                    <div class="form-group">
                        <b-input-group size="md" :prepend="$t('passwordPrep')">
                            <b-form-input size="35" required v-model="db.pass" type="password"
                                          :placeholder="$t('passwordPlaceholder')"
                                          autocomplete="new-password"
                                          :state="db.pass.length > 0"></b-form-input>
                            <b-input-group-append is-text>
                                <icon name="lock"></icon>
                            </b-input-group-append>
                        </b-input-group>
                    </div>
                    <div class="form-group">
                        <b-input-group size="md" :prepend="$t('dbNamePrep')">
                            <b-form-input size="35" required v-model="db.name" type="text"
                                          :placeholder="$t('dbNamePlaceholder')"
                                          autocomplete="off"
                                          :state="db.name.length > 0"></b-form-input>
                            <b-input-group-append is-text>
                                <icon name="database"></icon>
                            </b-input-group-append>
                        </b-input-group>
                    </div>
                    <div class="form-group mb-0">
                        <b-input-group size="md">
                            <b-form-checkbox v-model="installDemoData">{{ $t('installDemoData') }}</b-form-checkbox>
                        </b-input-group>
                    </div>
                    <hr>
                    <b-btn
                        :class="{'pulse-button': db.name.length && db.pass.length && db.user.length && db.host.length && error !== false}"
                        size="sm" variant="primary" @click="checkCredentials(db)">
                        <icon name="sync"></icon>
                        {{ $t('verify')}}
                    </b-btn>
                </div>
            </div>
        </form>
        <div class="result mt-3" v-if="error !== null">
            <b-alert :variant="error ? 'danger' : 'success'" show>
                <icon :name="error ? 'exclamation-triangle' : 'check'"></icon>
                {{ $t(msg) }}
            </b-alert>
        </div>
        <b-alert class="result mt-3" variant="danger" show v-if="networkError !== false">
            <icon name="exclamation-triangle"></icon> {{ $t('networkError') }} <div v-html="networkError"></div>
        </b-alert>
        <continue :disableBack="false" :disable="error !== false"></continue>
    </div>
</template>

<script>
import axios from 'axios';
import qs from 'qs';

export default {
    name:    'databaseparameters',
    data() {
        const messages = {
            de: {
                hostPrep:            'Host',
                hostPlaceholder:     'Datenbank-Host',
                socketPrep:          'Socket (optional)',
                socketPlaceholder:   'Socket (z.B. /tmp/mysql5.sock)',
                userNamePrep:        'Benutzername',
                userNamePlaceholder: 'Datenbank-Benutzername',
                passwordPrep:        'Passwort',
                passwordPlaceholder: 'Datenbank-Passwort',
                dbNamePrep:          'Datenbank-Name',
                dbNamePlaceholder:   'Datenbank-Name',
                installDemoData:     'Demodaten installieren?',
                verify:              'Daten prüfen',
                headerMsg:           'Datenbankparameter',
                leadMsg:             'Konfigurieren der Datenbank',
                contentMsg:          '<p>Für die Installation von JTL-Shop benötigen Sie eine MySQL-Datenbank.</p>'
                                         + '<p>In der Regel müssen Datenbank und Benutzer erst manuell erstellt werden. '
                                         + 'Bei Problemen wenden Sie sich bitte an Ihren Administrator bzw. Webhoster, da dieser Vorgang '
                                         + 'von Hoster zu Hoster unterschiedlich ist und von der eingesetzten Software abhängt.</p>'
                                         + '<p>Der Benutzer benötigt Lese-, Schreib- und Löschrechte (Create, Insert, Update, Delete) '
                                         + 'für diese Datenbank.</p>'
                                         + '<p>Als <strong>Host</strong> ist <i>localhost</i> zumeist die richtige Einstellung. '
                                         + 'Diese Information erhalten Sie ebenfalls von Ihrem Webhoster.</p>'
                                         + '<p>Füllen Sie das Feld <strong>Socket</strong> nur aus, wenn Sie ganz sicher sind, '
                                         + 'dass Ihre Datenbank über einen Socket erreichbar ist. '
                                         + 'In diesem Fall tragen Sie bitte den absoluten Pfad zum Socket ein.</p>',
                connectionSuccess:   'Erfolgreich verbunden',
                cannotConnect:       'Keine Verbindung möglich',
                shopExists:          'Es existiert bereits eine Shopinstallation in dieser Datenbank',
                noCredentials:       'Keine Zugangsdaten übermittelt'
            },
            en: {
                hostPrep:            'Host',
                hostPlaceholder:     'Database host',
                socketPrep:          'Socket (optional)',
                socketPlaceholder:   'Socket (e.g.. /tmp/mysql5.sock)',
                userNamePrep:        'User name',
                userNamePlaceholder: 'database user name',
                passwordPrep:        'Password',
                passwordPlaceholder: 'database password',
                dbNamePrep:          'Database name',
                dbNamePlaceholder:   'Database name',
                installDemoData:     'Install demo data?',
                verify:              'verify',
                headerMsg:           'Database parameters',
                leadMsg:             'Configure the database',
                contentMsg:          '<p>You need a MySQL database to install JTL-Shop.</p>'
                                        + '<p>Usually the database and its users must be created manually. '
                                        + 'In case of any problems, please contact your server administrator or web hosting provider. '
                                        + 'The creation process varies from provider to provider and depends on the software used.</p>'
                                        + '<p>Users require read, write, and delete permissions (create, insert, update, delete) for this database.</p>'
                                        + '<p>In most cases, <i>localhost</i> is the right choice to enter for the <strong>Host</strong>. '
                                        + 'For further information on this topic, please refer to your web hosting provider.</p>'
                                        + '<p>Only complete the <strong>Socket</strong> field if you are sure that your '
                                        + 'database is accessible via a socket. '
                                        + 'If this is the case, enter the absolute path to the socket.</p>',
                connectionSuccess:   'Successfully connected',
                cannotConnect:       'Could not connect',
                shopExists:          'The selected database already contains a shop installation',
                noCredentials:       'No credentials given'
            }
        };
        this.$i18n.add('en', messages.en);
        this.$i18n.add('de', messages.de);
        return {
            db:              {
                host:   'localhost',
                pass:   '',
                socket: '',
                user:   '',
                name:   ''
            },
            installDemoData: false,
            error:           null,
            msg:             null,
            networkError:    false
        };
    },
    methods: {
        checkCredentials(db) {
            axios.post(this.$getApiUrl('credentialscheck'), qs.stringify(db))
                .then(response => {
                    if (!response.data.msg) {
                        this.networkError = response.data;
                        return;
                    }
                    this.networkError = false;
                    this.msg = response.data.msg;
                    this.error = response.data.error;
                    this.$store.commit('setDBCredentials', db);
                    this.$store.commit('setDoInstallDemoData', this.installDemoData);
                })
                .catch(error => {
                    this.msg = error.response
                        ? error.response
                        : `URL ${this.$getApiUrl('credentialscheck')} nicht erreichbar.`;
                    this.error = true;
                });
        }
    }
};
</script>
<style scoped>
    .input-group-addon.fixed-addon {
        width: 150px;
        text-align: right;
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
