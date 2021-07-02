<template>
    <div>
        <b-progress v-model="installProgress" striped :animated="installProgress < 100" class="mb-3 mt-3 d-print-none"></b-progress>
        <div id="install-wrapper">
            <installation-summary class="steps" id="step-7" v-if="istep === 7"></installation-summary>

            <schema class="steps" id="step-6" v-if="istep === 6"></schema>

            <admin-user class="steps" id="step-5" v-if="istep === 5"></admin-user>

            <database-parameters class="steps" id="step-4" v-if="istep === 4"></database-parameters>

            <server-check class="steps" id="step-3" v-if="istep === 3"></server-check>

            <directory-check class="steps" id="step-2" v-if="istep === 2"></directory-check>

            <installed-check class="steps" id="step-1" v-if="istep === 1"></installed-check>

            <hello class="steps" id="step-0" v-if="istep === 0"></hello>
        </div>
    </div>
</template>

<script>
import InstalledCheck from './InstalledCheck';
import Hello from './Hello';
import ServerCheck from './ServerCheck';
import DirectoryCheck from './DirectoryCheck';
import DatabaseParameters from './DatabaseParameters';
import AdminUser from './AdminUser';
import Schema from './Schema';
import InstallationSummary from './InstallationSummary';
export default {
    name:       'installer',
    components: {
        InstalledCheck,
        Hello,
        DirectoryCheck,
        ServerCheck,
        DatabaseParameters,
        AdminUser,
        Schema,
        InstallationSummary
    }
};
</script>

<style scoped>
    .list-group {
        width: 100%;
    }
</style>
