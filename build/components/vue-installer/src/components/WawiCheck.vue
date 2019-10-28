<template>
    <div>
        <jumbotron :header="$t('headerMsg')"
                   :lead="$t('leadMsg')"
                   content="">
        </jumbotron>

        <div class="row">
            <div class="col">
                <b-alert variant="info" show v-if="!syncOK">
                    <icon name="exclamation-triangle"></icon> {{ $t('startSync') }}
                </b-alert>
                <table class="table b-table table-striped table-hover" v-if="syncOK">
                    <thead>
                    <tr>
                        <th colspan="2">{{ $t('companyData') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>{{ $t('name') }}</td>
                        <td>{{ syncData.step.company.cName}}</td>
                    </tr>
                    <tr>
                        <td>{{ $t('operator') }}</td>
                        <td>{{ syncData.step.company.cUnternehmer}}</td>
                    </tr>
                    <tr>
                        <td>{{ $t('street') }}</td>
                        <td>{{ syncData.step.company.cStrasse}}</td>
                    </tr>
                    <tr>
                        <td>{{ $t('zip') }}</td>
                        <td>{{ syncData.step.company.cPLZ}}</td>
                    </tr>
                    <tr>
                        <td>{{ $t('city') }}</td>
                        <td>{{ syncData.step.company.cOrt}}</td>
                    </tr>
                    <tr>
                        <td>{{ $t('country') }}</td>
                        <td>{{ syncData.step.company.cLand}}</td>
                    </tr>
                    <tr>
                        <td>{{ $t('tel') }}</td>
                        <td>{{ syncData.step.company.cTel}}</td>
                    </tr>
                    <tr>
                        <td>{{ $t('fax') }}</td>
                        <td>{{ syncData.step.company.cFax}}</td>
                    </tr>
                    <tr>
                        <td>{{ $t('mail') }}</td>
                        <td>{{ syncData.step.company.cEMail}}</td>
                    </tr>
                    <tr>
                        <td>{{ $t('www') }}</td>
                        <td>{{ syncData.step.company.cWWW}}</td>
                    </tr>
                    <tr>
                        <td>{{ $t('accountHolder') }}</td>
                        <td>{{ syncData.step.company.cKontoinhaber}}</td>
                    </tr>
                    <tr>
                        <td>{{ $t('blz') }}</td>
                        <td>{{ syncData.step.company.cBLZ}}</td>
                    </tr>
                    <tr>
                        <td>{{ $t('accountNo') }}</td>
                        <td>{{ syncData.step.company.cKontoNr}}</td>
                    </tr>
                    <tr>
                        <td>{{ $t('bank') }}</td>
                        <td>{{ syncData.step.company.cBank}}</td>
                    </tr>
                    <tr>
                        <td>{{ $t('iban') }}</td>
                        <td>{{ syncData.step.company.cIBAN}}</td>
                    </tr>
                    <tr>
                        <td>{{ $t('bic') }}</td>
                        <td>{{ syncData.step.company.cBIC}}</td>
                    </tr>
                    <tr>
                        <td>{{ $t('ustidnr') }}</td>
                        <td>{{ syncData.step.company.cUSTID}}</td>
                    </tr>
                    <tr>
                        <td>{{ $t('taxNo') }}</td>
                        <td>{{ syncData.step.company.cSteuerNr}}</td>
                    </tr>
                    </tbody>
                </table>

                <table class="table b-table table-striped table-hover" v-if="syncOK">
                    <thead>
                    <tr>
                        <th colspan="2">{{ $t('customerGroups') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="group in syncData.step.groups" :key="group.cName">
                        <td v-if="group.cStandard === 'Y'"><strong>{{ group.cName }}</strong></td>
                        <td v-else>{{ group.cName }}</td>
                    </tr>
                    </tbody>
                </table>

                <table class="table b-table table-striped table-hover" v-if="syncOK">
                    <thead>
                    <tr>
                        <th colspan="2">{{ $t('languages') }}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="language in syncData.step.languages" :key="language.cNameDeutsch">
                        <td v-if="language.cShopStandard === 'Y'"><strong>{{ language.cNameDeutsch }}</strong> {{ $t('default') }}</td>
                        <td v-else>{{ language.cNameDeutsch }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <b-btn :class="{'pulse-button': !syncOK}" size="sm" variant="primary" @click="checkWawi()">
                    <icon name="sync"></icon> {{ $t('checkAgain') }}
                </b-btn>
                <continue :disableBack="false" :disable="error !== false"></continue>
            </div>
        </div>
    </div>
</template>

<script>
/* eslint-disable */
import {mapGetters} from 'vuex';
import axios from 'axios';
import qs from 'qs';
export default {
    name:     'wawicheck',
    data() {
        const messages = {
            de: {
                headerMsg:      'Wawi-Abgleich',
                leadMsg:        'Firmendaten',
                startSync:      'Bitte Wawi-Abgleich starten',
                companyData:    'Firmendaten',
                name:           'Name',
                operator:       'Unternehmer',
                street:         'Straße',
                zip:            'PLZ',
                city:           'Ort',
                country:        'Land',
                tel:            'Tel.',
                fax:            'Fax',
                mail:           'E-Mail',
                www:            'WWW',
                accountHolder:  'Kontoinhaber',
                blz:            'BLZ',
                accountNo:      'KontoNr.',
                bank:           'Bank',
                iban:           'IBAN',
                bic:            'BIC',
                ustidnr:        'USTIDNr.',
                taxNo:          'SteuerNr.',
                customerGroups: 'Kundengruppen',
                languages:      'Sprachen',
                checkAgain:     'erneut prüfen',
                default:        '(Standard)'
            },
            en: {
                headerMsg:      'Wawi sync',
                leadMsg:        'Company data',
                startSync:      'Please start  Wawi sync',
                companyData:    'Company data',
                name:           'Name',
                operator:       'Business operator',
                street:         'Street',
                zip:            'ZIP',
                city:           'City',
                country:        'Coutnry',
                tel:            'Tel.',
                fax:            'Fax',
                mail:           'E-Mail',
                www:            'WWW',
                accountHolder:  'Account holder',
                blz:            'BLZ',
                accountNo:      'Account no.',
                bank:           'Bank',
                iban:           'IBAN',
                bic:            'BIC',
                ustidnr:        'USTIDNr.',
                taxNo:          'Tax no.',
                customerGroups: 'Customer groups',
                languages:      'Languages',
                checkAgain:     'check again',
                default:        '(default)'
            }
        };
        this.$i18n.add('en', messages.en);
        this.$i18n.add('de', messages.de);
        return {
            syncData: null,
            error:    false,
            syncOK:   false
        };
    },
    computed: mapGetters({
        wawi:      'getWawiUser',
        admin:     'getAdminUser',
        shopURL:   'getShopURL',
        secretKey: 'getSecretKey'
    }),
    mounted() {
        this.checkWawi();
    },
    methods: {
        checkWawi() {
            const postData = qs.stringify({
                db:     this.$store.state.database,
                stepId: 0
            });
            axios.post(this.$getApiUrl('wizard'), postData)
                .then(response => {
                    this.syncData = response.data.payload;
                    this.syncOK = response.data.payload.isSynced;
                })
                .catch(error => {
                    console.log('caught: ', error);
                });
        }
    }
};
</script>
