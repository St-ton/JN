<template>
    <div>
        <jumbotron :header="$t('headerMsg')"
                   :lead="$t('leadMsg')"
                   content="">
        </jumbotron>
        <div class="row">
            <div class="col" v-if="checkedDirectories">
                <b-card show-header no-block>
                    <h3 slot="header">
                        <span class="badge" :class="{'badge-danger': directoriesStatus === 0, 'badge-success': directoriesStatus === 1}">
                            <icon name="check" v-if="directoriesStatus === 1"></icon>
                            <icon name="exclamation-triangle" v-else></icon>
                        </span> {{ $t('writePermissions') }} <b-btn v-b-toggle.collapse-directories size="sm">
                        <span class="when-opened">{{ $t('hide') }}</span>
                        <span class="when-closed">{{ $t('show') }}</span>
                    </b-btn>
                    </h3>
                    <span id="dir-status-msg" class="alert alert-success" v-if="directoriesStatus === 1 && !collapseIsVisible">{{ $t('ok') }}</span>
                    <b-collapse id="collapse-directories" :visible="directoriesStatus === 0" @hidden="collapseHide()" @show="collapseShow()">
                        <b-list-group class="list-group-flush">
                            <b-list-group-item v-for="dir in directories" :key="dir.idx">
                            <span class="badge" :class="{'badge-success': dir.isWriteable === true, 'badge-danger': dir.isWriteable === false}">
                                <icon name="check" v-if="dir.isWriteable === true"></icon>
                                <icon name="exclamation-triangle" v-else></icon>
                            </span>
                                <span class="ml-2">{{ dir.dir }}</span>
                            </b-list-group-item>
                        </b-list-group>
                        <b-btn class="mt-3 ml-4" size="sm" v-if="directoriesStatus === 0" @click="check()">
                            <icon name="sync"></icon> {{ $t('checkAgain') }}
                        </b-btn>
                    </b-collapse>
                </b-card>
            </div>
        </div>
        <b-alert variant="info" show v-if="!checkedDirectories">
            <icon name="sync" spin></icon> {{ $t('verifyWritePermissions') }}
        </b-alert>
        <b-alert variant="danger" show v-if="networkError !== false">
            <icon name="exclamation-triangle"></icon> {{ $t('networkError') }} <div v-html="networkError"></div>
        </b-alert>
        <continue :disableBack="false" :disable="!checkedDirectories || networkError !== false || directoriesStatus === 0"></continue>
    </div>
</template>

<script>
import axios from 'axios';
export default {
    name:  'directorycheck',
    data() {
        const messages = {
            de: {
                ok:                     'Alles OK.',
                show:                   'anzeigen',
                hide:                   'ausblenden',
                checkAgain:             'Erneut prüfen',
                verifyWritePermissions: 'Prüfe Schreibrechte...',
                networkError:           'Netzwerkfehler:',
                writePermissions:       'Schreibrechte',
                headerMsg:              'Dateirechte',
                leadMsg:                'Prüft, ob alle nötigen Verzeichnisse beschreibbar sind'
            },
            en: {
                ok:                     'Everything OK.',
                show:                   'show',
                hide:                   'hide',
                checkAgain:             'check again',
                verifyWritePermissions: 'Verifying write permissions...',
                networkError:           'Network error:',
                writePermissions:       'Write permissions',
                headerMsg:              'File permissions',
                leadMsg:                'Check write permissions for all required directories and files'
            }
        };
        this.$i18n.add('en', messages.en);
        this.$i18n.add('de', messages.de);
        this.check();
        return {
            directories:        [],
            directoriesStatus:  0,
            checkedDirectories: false,
            networkError:       false,
            collapseIsVisible:  false
        };
    },
    methods: {
        collapseHide() {
            this.collapseIsVisible = false;
        },
        collapseShow() {
            this.collapseIsVisible = true;
        },
        check() {
            axios.get(this.$getApiUrl('dircheck'))
                .then(response => {
                    if (!response.data.testresults) {
                        this.networkError = response.data;
                        return;
                    }
                    this.networkError = false;
                    this.directories = [];
                    Object.keys(response.data.testresults).forEach((dir, idx) => {
                        this.directories.push({
                            idx,
                            dir,
                            isWriteable: response.data.testresults[dir]
                        });
                    });
                    /* eslint-disable no-confusing-arrow */
                    this.directoriesStatus = this.directories.reduce((acc, val) => acc && (val.isWriteable || val === 1 ? 1 : 0), 1);
                    this.checkedDirectories = true;
                })
                .catch(error => {
                    this.networkError = error.response
                        ? error.response
                        : `URL ${this.$getApiUrl('dircheck')} nicht erreichbar.`;
                });
        }
    }
};
</script>
<style scoped>
    .card-body {
        padding: 1.25rem 0;
    }
    #dir-status-msg {
        margin: 1.25rem;
    }
    .collapsed > .when-opened,
    :not(.collapsed) > .when-closed {
        display: none;
    }
</style>
