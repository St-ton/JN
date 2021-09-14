<template>
    <div>
        <jumbotron :header="$t('headerMsg')"
                   :lead="$t('leadMsg')"
                   content="">
        </jumbotron>
        <div class="row">
            <div class="col">
                <h3>{{ $t('h3wawi') }}</h3>
                <p v-html="$t('textwawi')"></p>
                <table class="table table-striped">
                    <tbody>
                    <tr>
                        <th scope="row">{{ $t('licenseKey') }}</th>
                        <td>{{ $t('licenseKeyDescr') }} <icon name="external-link-alt" class="d-print-none"></icon> <a href="https://kundencenter.jtl-software.de/" rel="noopener" target="_blank">{{ $t('customerCenter') }}</a></td>
                    </tr>
                    <tr>
                        <th scope="row">{{ $t('webshopURL') }}</th>
                        <td>{{ shopURL }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ $t('syncUser') }}</th>
                        <td>{{ wawi.name }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ $t('syncPass') }}</th>
                        <td>{{ wawi.pass }}</td>
                    </tr>
                    </tbody>
                </table>

                <hr>

                <h3>{{ $t('h3backend') }}</h3>
                <table class="table table-striped">
                    <tbody>
                    <tr>
                        <th scope="row">URL</th>
                        <td>{{ shopURL }}admin</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ $t('userName') }}</th>
                        <td>{{ admin.name }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ $t('pass') }}</th>
                        <td>{{ admin.pass }}</td>
                    </tr>
                    <tr>
                        <th scope="row">{{ $t('secretKey') }}</th>
                        <td>{{ secretKey }}</td>
                    </tr>
                    </tbody>
                </table>
                <div class="d-print-none">
                    <b-alert variant="warning" show>
                        <icon name="exclamation-triangle"></icon> {{$t('msgDeleteInstallDir') }} <code>includes/config.JTL-Shop.ini.php</code>.
                    </b-alert>

                    <b-alert variant="info" show>
                        <p>{{$t('msgPrint') }}</p>
                        <strong>{{$t('msgHaveFun') }}</strong>
                    </b-alert>
                    <span class="btn-group">
                        <b-btn variant="secondary" @click="print">
                            <icon name="print"></icon> {{$t('printPage') }}
                        </b-btn>
                        <b-btn variant="primary" :href="shopURL + 'admin'">
                            <icon name="share"></icon> {{$t('gotoBackend') }}
                        </b-btn>
                        <b-btn variant="secondary" :href="shopURL">
                            <icon name="share"></icon> {{$t('gotoFrontend') }}
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
        const messages = {
            de: {
                h3wawi:              'Webshop-Einstellungen in Wawi eintragen',
                textwawi:            'Tragen Sie nun die nachfolgenden Daten im Menü <code>Onlineshop -&gt; '
                                         + 'Onlineshop-Anbindung</code> der Wawi ein:',
                licenseKey:          'Lizenzschlüssel',
                webshopURL:          'Webshop-URL',
                syncUser:            'Sync-Benutzer',
                syncPass:            'Sync-Passwort',
                licenseKeyDescr:     'Den Lizenzschlüssel für den JTL-Shop finden Sie im',
                customerCenter:      'JTL Kundencenter',
                h3backend:           'Zugangsdaten zum Admin-Backend',
                userName:            'Benutzername',
                pass:                'Passwort',
                secretKey:           'Geheimer Schlüssel',
                msgDeleteInstallDir: 'Bitte löschen Sie nun das Installationsverzeichnis des Shops (/install) '
                                         + 'und entziehen Sie die Schreibrechte von der Datei',
                msgPrint:            'Drucken Sie diese Seite aus und verwahren Sie diese gut.',
                msgHaveFun:          'Wir wünschen Ihnen viel Erfolg und Spaß mit Ihrem neuen JTL-Shop!',
                printPage:           'Diese Seite drucken',
                gotoBackend:         'Zum Backend',
                gotoFrontend:        'Zum Shop',
                headerMsg:           'Herzlichen Glückwunsch',
                leadMsg:             'Installation abgeschlossen'
            },
            en: {
                h3wawi:              'Add webshop configuration to Wawi',
                textwawi:            'Add the following data at <code>Onlineshop -&gt; '
                                         + 'Onlineshop-Anbindung</code> to wawi:',
                licenseKey:          'License key',
                webshopURL:          'Webshop URL',
                syncUser:            'Sync user',
                syncPass:            'Sync password',
                licenseKeyDescr:     'You can find your license key at the ',
                customerCenter:      'JTL customer center',
                h3backend:           'Credentials for your admin backend',
                userName:            'User name',
                pass:                'Password',
                secretKey:           'Secret key',
                msgDeleteInstallDir: 'Now please delete the installation directory (/install) '
                                         + 'and remove any write permissions for the file',
                msgPrint:            'Please print this page and keep it safe.',
                msgHaveFun:          'Have a good time with your new JTL-Shop!',
                printPage:           'Print page',
                gotoBackend:         'Go to backend',
                gotoFrontend:        'Go to frontend',
                headerMsg:           'Congratulations',
                leadMsg:             'Installation finished'
            }
        };
        this.$i18n.add('en', messages.en);
        this.$i18n.add('de', messages.de);
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
