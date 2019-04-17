<template>
    <div>
        <jumbotron header="Wawi-Abgleich"
                   lead="Firmendaten"
                   content="">
        </jumbotron>

        <div class="row">
            <div class="col">
                <b-alert variant="info" show v-if="!syncOK">
                    <icon name="exclamation-triangle"></icon> Bitte Wawi-Abgleich starten
                </b-alert>
                <table class="table b-table table-striped table-hover" v-if="syncOK">
                    <thead>
                    <tr>
                        <th colspan="2">Firmendaten</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Name</td>
                        <td>{{ syncData.step.company.cName}}</td>
                    </tr>
                    <tr>
                        <td>Unternehmer</td>
                        <td>{{ syncData.step.company.cUnternehmer}}</td>
                    </tr>
                    <tr>
                        <td>Straße</td>
                        <td>{{ syncData.step.company.cStrasse}}</td>
                    </tr>
                    <tr>
                        <td>PLZ</td>
                        <td>{{ syncData.step.company.cPLZ}}</td>
                    </tr>
                    <tr>
                        <td>Ort</td>
                        <td>{{ syncData.step.company.cOrt}}</td>
                    </tr>
                    <tr>
                        <td>Land</td>
                        <td>{{ syncData.step.company.cLand}}</td>
                    </tr>
                    <tr>
                        <td>Tel.</td>
                        <td>{{ syncData.step.company.cTel}}</td>
                    </tr>
                    <tr>
                        <td>Fax</td>
                        <td>{{ syncData.step.company.cFax}}</td>
                    </tr>
                    <tr>
                        <td>E-Mail</td>
                        <td>{{ syncData.step.company.cEMail}}</td>
                    </tr>
                    <tr>
                        <td>WWW</td>
                        <td>{{ syncData.step.company.cWWW}}</td>
                    </tr>
                    <tr>
                        <td>Kontoinhaber</td>
                        <td>{{ syncData.step.company.cKontoinhaber}}</td>
                    </tr>
                    <tr>
                        <td>BLZ</td>
                        <td>{{ syncData.step.company.cBLZ}}</td>
                    </tr>
                    <tr>
                        <td>KontoNr.</td>
                        <td>{{ syncData.step.company.cKontoNr}}</td>
                    </tr>
                    <tr>
                        <td>Bank</td>
                        <td>{{ syncData.step.company.cBank}}</td>
                    </tr>
                    <tr>
                        <td>IBAN</td>
                        <td>{{ syncData.step.company.cIBAN}}</td>
                    </tr>
                    <tr>
                        <td>BIC</td>
                        <td>{{ syncData.step.company.cBIC}}</td>
                    </tr>
                    <tr>
                        <td>USTIDNr.</td>
                        <td>{{ syncData.step.company.cUSTID}}</td>
                    </tr>
                    <tr>
                        <td>SteuerNr.</td>
                        <td>{{ syncData.step.company.cSteuerNr}}</td>
                    </tr>
                    </tbody>
                </table>

                <table class="table b-table table-striped table-hover" v-if="syncOK">
                    <thead>
                    <tr>
                        <th colspan="2">Kundengruppen</th>
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
                        <th colspan="2">Sprachen</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="language in syncData.step.languages" :key="language.cNameDeutsch">
                        <td v-if="language.cShopStandard === 'Y'"><strong>{{ language.cNameDeutsch }}</strong> (Standard)</td>
                        <td v-else>{{ language.cNameDeutsch }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="row">
            <div class="col">
                <b-btn :class="{'pulse-button': !syncOK}" size="sm" variant="primary" @click="checkWawi()">
                    <icon name="sync"></icon> erneut prüfen
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
