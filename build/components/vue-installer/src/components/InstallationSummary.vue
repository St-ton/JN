<template>
    <div>
        <jumbotron header="Herzlichen Glückwunsch"
                   lead="Installation abgeschlossen"
                   content="">
        </jumbotron>

        <div class="row">
            <div class="col">
                <h3>Webshop-Einstellungen in Wawi eintragen</h3>
                <p>Tragen Sie nun die nachfolgenden Daten im Menü <code>Onlineshop -&gt; Onlineshop-Anbindung</code>
                    der Wawi ein:</p>
                <table class="table table-striped">
                    <tbody>
                    <tr>
                        <th scope="row">Lizenzschlüssel</th>
                        <td>Den Lizenzschlüssel für den JTL-Shop finden Sie im <icon name="external-link-alt" class="d-print-none"></icon> <a href="https://kundencenter.jtl-software.de/" rel="noopener" target="_blank">JTL Kundencenter</a></td>
                    </tr>
                    <tr>
                        <th scope="row">Webshop-URL</th>
                        <td>{{ shopURL }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Sync-Benutzer</th>
                        <td>{{ wawi.name }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Sync-Passwort</th>
                        <td>{{ wawi.pass }}</td>
                    </tr>
                    </tbody>
                </table>

                <hr>

                <h3>Zugangsdaten zum Admin-Backend</h3>
                <table class="table table-striped">
                    <tbody>
                    <tr>
                        <th scope="row">URL</th>
                        <td>{{ shopURL }}admin</td>
                    </tr>
                    <tr>
                        <th scope="row">Benutzername</th>
                        <td>{{ admin.name }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Passwort</th>
                        <td>{{ admin.pass }}</td>
                    </tr>
                    <tr>
                        <th scope="row">Geheimer Schlüssel</th>
                        <td>{{ secretKey }}</td>
                    </tr>
                    </tbody>
                </table>
                <div class="d-print-none">
                    <b-alert variant="warning" show>
                        <icon name="exclamation-triangle"></icon> Bitte löschen Sie nun das Installationsverzeichnis des Shops (/install) und entziehen Sie die Schreibrechte von der Datei <code>includes/config.JTL-Shop.ini.php</code>.
                    </b-alert>

                    <b-alert variant="info" show>
                        <p>Drucken Sie diese Seite aus und verwahren Sie diese gut.</p>
                        <strong>Wir wünschen Ihnen viel Erfolg und Spaß mit Ihrem neuen JTL-Shop!</strong>
                    </b-alert>
                    <span class="btn-group">
                        <b-btn variant="secondary" @click="print">
                            <icon name="print"></icon> Diese Seite drucken
                        </b-btn>
                        <b-btn variant="primary" :href="shopURL + 'admin'">
                            <icon name="share"></icon> Zum Backend
                        </b-btn>
                        <b-btn variant="secondary" :href="shopURL">
                            <icon name="share"></icon> Zum Shop
                        </b-btn>
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { mapGetters } from 'vuex';
export default {
    name:     'installationsummary',
    data() {
        return { };
    },
    computed: mapGetters({
        wawi:      'getWawiUser',
        admin:     'getAdminUser',
        shopURL:   'getShopURL',
        secretKey: 'getSecretKey'
    }),
    methods: {
        print() {
            window.print();
            return false;
        }
    }
};
</script>
