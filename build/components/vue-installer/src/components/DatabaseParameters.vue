<template>
    <div>
        <jumbotron header="Datenbankparameter"
                     lead="Konfigurieren Sie die Datenbank"
                     content="<p>Für die Installation des JTL-Shops benötigen wir eine MySQL-Datenbank.</p>
<p>Meistens müssen der Benutzer und die Datenbank erst manuell erstellt werden. Bei Problemen wenden Sie sich bitte an Ihren Administrator bzw. Webhoster, da dieser Vorgang von Hoster zu Hoster unterschiedlich ist und von der eingesetzten Software abhängt.</p>
<p>Der Benutzer benötigt Lese-, Schreib- und Löschrechte (Create, Insert, Update, Delete) für diese Datenbank.</p>
<p>Als <strong>Host</strong> ist <i>localhost</i> zumeist die richtige Einstellung. Diese Information erhalten Sie ebenfalls von Ihrem Webhoster.</p>
        <p>Das Feld <strong>Socket</strong> füllen Sie bitte nur aus, wenn Sie ganz sicher sind, dass Ihre Datenbank über einen Socket erreichbar ist. In diesem Fall tragen Sie bitte den absoluten Pfad zum Socket ein.</p>">
        </jumbotron>
        <div class="row">
            <div class="col">
                <div class="form-group">
                    <b-input-group size="md" prepend="Host">
                        <b-form-input size="35" required v-model="db.host" type="text" placeholder="Datenbank-Host" :state="db.host.length ? 'success' : 'warning'"></b-form-input>
                        <b-input-group-append is-text><icon name="home"></icon></b-input-group-append>
                    </b-input-group>
                </div>
                <div class="form-group">
                    <b-input-group size="md" prepend="Socket (optional)">
                        <b-form-input size="35" v-model="db.socket" type="text" placeholder="Socket (z.B. /tmp/mysql5.sock)" :state="db.socket.length ? 'success' : 'warning'"></b-form-input>
                        <b-input-group-append is-text>
                            <icon name="exchange-alt"></icon>
                        </b-input-group-append>
                    </b-input-group>
                </div>
                <div class="form-group">
                    <b-input-group size="md" prepend="Benutzername">
                        <b-form-input size="35" required v-model="db.user" type="text" placeholder="Datenbank-Benutzername" :state="db.user.length ? 'success' : 'warning'"></b-form-input>
                        <b-input-group-append is-text>
                            <icon name="user"></icon>
                        </b-input-group-append>
                    </b-input-group>
                </div>
                <div class="form-group">
                    <b-input-group size="md" prepend="Passwort">
                        <b-form-input size="35" required v-model="db.pass" type="password" placeholder="Datenbank-Passwort" :state="db.pass.length ? 'success' : 'warning'"></b-form-input>
                        <b-input-group-append is-text>
                            <icon name="lock"></icon>
                        </b-input-group-append>
                    </b-input-group>
                </div>
                <div class="form-group">
                    <b-input-group size="md" prepend="Datenbank-Name">
                        <b-form-input size="35" required v-model="db.name" type="text" placeholder="Datenbank-Name" :state="db.name.length ? 'success' : 'warning'"></b-form-input>
                        <b-input-group-append is-text>
                            <icon name="database"></icon>
                        </b-input-group-append>
                    </b-input-group>
                </div>
                <div class="form-group mb-0">
                    <b-input-group size="md">
                        <b-form-checkbox v-model="installDemoData">Demodaten installieren?</b-form-checkbox>
                    </b-input-group>
                </div>
                <hr>
                <b-btn :class="{'pulse-button': db.name.length && db.pass.length && db.user.length && db.host.length && error !== false}" size="sm" variant="primary" @click="checkCredentials(db)">
                    <icon name="sync"></icon> Daten prüfen
                </b-btn>
            </div>
        </div>
        <div class="result mt-3" v-if="error !== null">
            <b-alert :variant="error ? 'danger' : 'success'" show>
                <icon :name="error ? 'exclamation-triangle' : 'check'"></icon> {{ msg }}
            </b-alert>
        </div>
        <continue :disableBack="false" :disable="error !== false"></continue>
    </div>
</template>

<script>
    import axios from 'axios';
    import qs from 'qs';
    export default {
        name: 'databaseparameters',
        data() {
            let msg   = null,
                error = null;
            return {
                db: {
                    host:   'localhost',
                    pass:   '',
                    socket: '',
                    user:   '',
                    name:   '',
                },
                installDemoData: false,
                error,
                msg
            };
        },
        methods: {
            checkCredentials(db) {
                axios.post(this.$getApiUrl('credentialscheck'), qs.stringify(db))
                    .then(response => {
                        this.msg = response.data.msg;
                        this.error = response.data.error;
                        this.$store.commit('setDBCredentials', db);
                        this.$store.commit('setDoInstallDemoData', this.installDemoData);
                    })
                    .catch(error => {
                        this.msg   = error.response
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
</style>