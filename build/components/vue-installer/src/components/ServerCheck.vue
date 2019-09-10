<template>
    <div>
        <jumbotron header="Serverkonfiguration"
                     lead="Prüft, ob die Serverkonfiguration korrekt ist"
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
                        Serveranforderungen <b-btn v-b-toggle="'collapse-programs'" size="sm">
                        <span class="when-opened">ausblenden</span>
                        <span class="when-closed">anzeigen</span>
                    </b-btn>
                    </h3>
                    <span id="server-status-msg" class="alert alert-success" v-if="serverStatus === 0 && !collapseIsVisible">Alles OK.</span>
                    <b-collapse id="collapse-programs" :visible="serverStatus !== 0" @hidden="collapseHide()" @show="collapseShow()">
                        <h4 class="ml-3 mb-3 mt-3">Installierte Software</h4>
                        <table id="programs" class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Software</th>
                                <th>Voraussetzung</th>
                                <th>Vorhanden</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr v-for="conf in programs" :key="conf.name">
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

                        <h4 class="ml-3 mb-3 mt-3">Benötigte PHP-Einstellungen</h4>
                        <table id="phpconfig" class="table table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Einstellung</th>
                                <th>Benötigter Wert</th>
                                <th>Ihr System</th>
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

                        <h4 class="ml-3 mb-3 mt-3">Benötigte PHP-Erweiterungen und -Funktionen</h4>
                        <table id="phpmodules" class="table table-striped table-hover mb-0">
                            <thead>
                            <tr>
                                <th>Bezeichnung</th>
                                <th>Status</th>
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
                        <icon name="sync"></icon> Erneut prüfen
                    </b-btn>
                </b-card>
            </div>
        </div>
        <b-alert variant="info" show v-if="!checkedServer">
            <icon name="sync" spin></icon> Prüfe Serveranforderungen...
        </b-alert>
        <b-alert variant="danger" show v-if="networkError !== false">
            <icon name="exclamation-triangle"></icon> Netzwerkfehler: {{ networkError }}
        </b-alert>
        <continue :disableBack="false" :disable="!checkedServer || serverStatus === 2 || modulesStatus === 2 || networkError !== false"></continue>
    </div>
</template>

<script>
import axios from 'axios';
export default {
    name: 'servercheck',
    data() {
        let phpConfig         = [],
            phpModules        = [],
            programs          = [],
            configStatus      = 0,
            modulesStatus     = 0,
            programsStatus    = 0,
            serverStatus      = 0,
            collapseIsVisible = false,
            checkedServer     = false,
            networkError      = false;
        this.check();
        return {
            phpConfig,
            serverStatus,
            phpModules,
            programs,
            configStatus,
            modulesStatus,
            programsStatus,
            checkedServer,
            collapseIsVisible,
            networkError
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
                        : `URL ${this.$getApiUrl('systemcheck')} nicht erreichbar.`;
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
