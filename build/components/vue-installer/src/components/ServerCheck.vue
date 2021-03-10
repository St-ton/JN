<template>
    <div>
        <jumbotron :header="$t('headerMsg')"
                   :lead="$t('leadMsg')"
                   content="">
        </jumbotron>
        <div class="row">
            <div class="col" v-if="checkedServer">
                <b-card show-header no-block>
                    <h3 slot="header">
                        <span class="badge" :class="{'badge-danger': serverStatus === 2, 'badge-warning': serverStatus === 1, 'badge-success': serverStatus === 0}">
                            <icon name="check" v-if="serverStatus === 0"></icon>
                            <icon name="exclamation-triangle" v-else></icon>
                        </span>
                        {{ $t('systemRequirements') }} <b-btn v-b-toggle="'collapse-programs'" size="sm">
                        <span class="when-opened">{{ $t('hide') }}</span>
                        <span class="when-closed">{{ $t('show') }}</span>
                    </b-btn>
                    </h3>
                    <span id="server-status-msg" class="alert alert-success" v-if="serverStatus === 0 && !collapseIsVisible">{{ $t('ok') }}</span>
                    <b-collapse id="collapse-programs" :visible="serverStatus !== 0" @hidden="collapseHide()" @show="collapseShow()">
                        <h4 class="ml-3 mb-3 mt-3">{{ $t('installedSoftware') }}</h4>
                        <table id="programs" class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>{{ $t('installedSoftware') }}</th>
                                <th>{{ $t('requirement') }}</th>
                                <th>{{ $t('existing') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="conf in programs" :key="conf.name">
                                <td>{{ $t(conf.name) }}</td>
                                <td>{{ conf.requiredState }}</td>
                                <td>
                                <span class="hidden-xs">
                                    <h4 class="badge-wrap">
                                        <span class="badge" :class="conf.className">
                                            <span v-if="conf.currentState">{{ conf.currentState }}</span>
                                            <icon :name="conf.icon" v-else></icon>
                                        </span>
                                    </h4>
                                </span>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <h4 class="ml-3 mb-3 mt-3">{{ $t('requiredConfig') }}</h4>
                        <table id="phpconfig" class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>{{ $t('config') }}</th>
                                <th>{{ $t('requiredValue') }}</th>
                                <th>{{ $t('actualValue') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="conf in phpConfig" :key="conf.name">
                                <td>{{ conf.name }}</td>
                                <td>{{ conf.requiredState }}</td>
                                <td>
                                    <span class="hidden-xs">
                                        <h4 class="badge-wrap">
                                            <span class="badge" :class="conf.className">
                                                <span v-if="conf.currentState">{{ conf.currentState }}</span>
                                                <icon :name="conf.icon" v-else></icon>
                                            </span>
                                        </h4>
                                    </span>
                                </td>
                            </tr>
                            </tbody>
                        </table>

                        <h4 class="ml-3 mb-3 mt-3">{{ $t('requiredExtensions') }}</h4>
                        <table id="phpmodules" class="table table-striped table-hover mb-0">
                            <thead>
                            <tr>
                                <th>{{ $t('name') }}</th>
                                <th>{{ $t('state') }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="conf in phpModules" :key="conf.name">
                                <td>{{ conf.name }}</td>
                                <td>
                                    <span class="hidden-xs">
                                        <h4 class="badge-wrap">
                                            <span v-b-tooltip.hover :title="conf.description.replace(/(<([^>]+)>)/ig, '')" class="badge" :class="conf.className">
                                                <span v-if="conf.currentState">{{ conf.currentState }}</span>
                                                <icon :name="conf.icon" v-else></icon>
                                            </span>
                                        </h4>
                                    </span>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </b-collapse>
                    <b-btn class="mt-3" size="sm" v-if="serverStatus !== 0" @click="check()" style="margin-left: 15px">
                        <icon name="sync"></icon> {{ $t('checkAgain') }}
                    </b-btn>
                </b-card>
            </div>
        </div>
        <b-alert variant="info" show v-if="!checkedServer">
            <icon name="sync" spin></icon> {{ $t('validateServerRequirements') }}
        </b-alert>
        <b-alert variant="danger" show v-if="networkError !== false">
            <icon name="exclamation-triangle"></icon> {{ $t('networkError') }} <div v-html="networkError"></div>
        </b-alert>
        <continue :disableBack="false" :disable="!checkedServer || serverStatus === 2 || modulesStatus === 2 || networkError !== false"></continue>
    </div>
</template>

<script>
import axios from 'axios';
export default {
    name: 'servercheck',
    data() {
        const messages = {
            de: {
                systemRequirements:         'Serveranforderungen',
                unreachable:                'URL {url} nicht erreichbar.',
                hide:                       'ausblenden',
                show:                       'anzeigen',
                installedSoftware:          'Installierte Software',
                software:                   'Software',
                requirement:                'Voraussetzung',
                existing:                   'Vorhanden',
                requiredConfig:             'Benötigte PHP-Einstellungen',
                config:                     'Einstellung',
                requiredValue:              'Benötigter Wert',
                actualValue:                'Ihr System',
                requiredExtensions:         'Benötigte PHP-Erweiterungen und -Funktionen',
                name:                       'Bezeichnung',
                state:                      'Status',
                checkAgain:                 'Erneut prüfen',
                validateServerRequirements: 'Prüfe Serveranforderungen...',
                networkError:               'Netzwerkfehler:',
                ok:                         'Alles OK.',
                headerMsg:                  'Serverkonfiguration',
                leadMsg:                    'Prüft, ob die Serverkonfiguration korrekt ist',
                Betriebssystem:             'Betriebssystem',
                'PHP-Version':              'PHP-Version',
                'PHP-SAPI':                 'PHP-SAPI'
            },
            en: {
                systemRequirements:         'System requirements',
                unreachable:                'URL {url} unreachable.',
                hide:                       'hide',
                show:                       'show',
                installedSoftware:          'Installed software',
                software:                   'Software',
                requirement:                'Requirement',
                existing:                   'existing',
                requiredConfig:             'Required php config',
                config:                     'Config',
                requiredValue:              'Required value',
                actualValue:                'Your system',
                requiredExtensions:         'Required php extensions',
                name:                       'Name',
                state:                      'State',
                checkAgain:                 'check again',
                validateServerRequirements: 'Validating server requirements...',
                networkError:               'Network error:',
                ok:                         'Everything OK.',
                headerMsg:                  'Server configuration',
                leadMsg:                    'Checks your server configuration',
                Betriebssystem:             'Operating system',
                'PHP-Version':              'PHP version',
                'PHP-SAPI':                 'PHP SAPI'
            }
        };
        this.$i18n.add('en', messages.en);
        this.$i18n.add('de', messages.de);
        this.check();
        return {
            phpConfig:         [],
            phpModules:        [],
            programs:          [],
            serverStatus:      0,
            configStatus:      0,
            modulesStatus:     0,
            programsStatus:    0,
            checkedServer:     false,
            collapseIsVisible: false,
            networkError:      false
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
            axios.get(this.$getApiUrl('systemcheck'))
                .then(response => {
                    if (!response.data.testresults) {
                        this.networkError = response.data;
                        return;
                    }
                    this.phpModules = response.data.testresults.php_modules.map(this.$addClasses);
                    this.programs = response.data.testresults.programs.map(this.$addClasses);
                    this.phpConfig = response.data.testresults.php_config.map(this.$addClasses);
                    this.modulesStatus = this.phpModules.reduce(this.$getTotalResultCode, 0);
                    this.configStatus = this.phpConfig.reduce(this.$getTotalResultCode, 0);
                    this.programsStatus = this.programs.reduce(this.$getTotalResultCode, 0);
                    this.checkedServer = true;
                    this.serverStatus = 2;
                    if (this.modulesStatus === 0 && this.configStatus === 0 && this.programsStatus === 0) {
                        this.serverStatus = 0;
                    } else if (this.modulesStatus === 1 || this.configStatus === 1 || this.programsStatus === 1) {
                        this.serverStatus = 1;
                    }
                })
                .catch(error => {
                    this.networkError = error.response
                        ? error.response
                        : this.$i18n.translate('unreachable', { url: this.$getApiUrl('systemcheck') });
                });
        }
    }
};
</script>
<style scoped>
    .card-body {
        padding: 1.25rem 0;
    }
    #server-status-msg {
        margin: 1.25rem;
    }
    .collapsed > .when-opened,
    :not(.collapsed) > .when-closed {
        display: none;
    }
</style>
