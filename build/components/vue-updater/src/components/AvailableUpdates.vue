<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-6">
                <div id="available_updates" class="container">
                    <div class="form-group align-items-center">
                        Aktuelle Shop Version '{{ appVersion }}'
                    </div>
                    <template v-if="availableUpdates !== null">
                        <div class="accordion" id="availablePatchesBuilds">
                            <template v-if="availableUpdates.patches !== undefined && Object.keys(availableUpdates.patches).length > 0">
                                <div class="card">
                                    <div class="card-header" id="availablePatches">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link" type="button" data-toggle="collapse"
                                                data-target="#collapseAvailablePatches" aria-expanded="true"
                                                aria-controls="collapseAvailablePatches">
                                                Verfügbare Patches '{{ Object.keys(availableUpdates.patches).length }}'
                                            </button>
                                        </h5>
                                    </div>

                                    <div id="collapseAvailablePatches" class="collapse show"
                                        aria-labelledby="availablePatches" data-parent="#availablePatchesBuilds">
                                        <div class="card-body">
                                            <div class="accordion" id="availablePatchesBody">
                                                <div class="card" v-for="availablePatch in availableUpdates.patches">
                                                    <div class="card-header text-left" :id="'availablePatch-'+availablePatch.id">
                                                        <h5 class="mb-0">
                                                            <button class="btn btn-link" type="button" data-toggle="collapse"
                                                                :data-target="'#collapseAvailablePatch-'+availablePatch.id"
                                                                :aria-controls="'collapseAvailablePatch-'+availablePatch.id"
                                                                aria-expanded="true">
                                                                Patch '{{ availablePatch.ref }}'
                                                            </button>
                                                            <button class="btn btn-secondary btn-sm float-right" type="button">Install</button>
                                                        </h5>
                                                    </div>
                                                    <div :id="'collapseAvailablePatch-'+availablePatch.id"
                                                        class="collapse" data-parent="#availablePatchesBody"
                                                        :aria-labelledby="'availablePatch-'+availablePatch.id">
                                                        <div class="card-body">
                                                            SOMETHING SOMETHING SOMETHING SOMETHING SOMETHING {{availablePatch.filename}}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template v-if="availableUpdates.builds !== undefined && Object.keys(availableUpdates.builds).length > 0">
                                <div class="card">
                                    <div class="card-header" id="availableBuilds">
                                        <h5 class="mb-0">
                                            <button class="btn btn-link collapsed" type="button" data-toggle="collapse"
                                                data-target="#collapseAvailableBuilds" aria-expanded="false"
                                                aria-controls="collapseAvailableBuilds">
                                                Verfügbare Builds '{{ Object.keys(availableUpdates.builds).length }}'
                                            </button>
                                        </h5>
                                    </div>
                                    <div id="collapseAvailableBuilds" class="collapse" aria-labelledby="availableBuilds"
                                        data-parent="#availablePatchesBuilds">
                                        <div class="card-body">
                                            <div class="accordion" id="availableBuildsBody">
                                                <div class="card" v-for="availableBuild in availableUpdates.builds">
                                                    <div class="card-header text-left" :id="'availableBuild-'+availableBuild.id">
                                                        <h5 class="mb-0">
                                                            <button class="btn btn-link" type="button" data-toggle="collapse"
                                                                :data-target="'#collapseAvailableBuild-'+availableBuild.id"
                                                                :aria-controls="'collapseAvailableBuild-'+availableBuild.id"
                                                                aria-expanded="true">
                                                                Build '{{ availableBuild.ref }}'
                                                            </button>
                                                            <button class="btn btn-secondary btn-sm float-right" type="button">Install</button>
                                                        </h5>
                                                    </div>
                                                    <div :id="'collapseAvailableBuild-'+availableBuild.id"
                                                         class="collapse" data-parent="#availableBuildsBody"
                                                         :aria-labelledby="'availableBuild-'+availableBuild.id">
                                                        <div class="card-body">
                                                            SOMETHING SOMETHING SOMETHING SOMETHING SOMETHING {{availableBuild.filename}}
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import axios from 'axios';
    import Form from '../helpers/class.Form';

    export default {
        name: "AvailableUpdates",
        data() {
            return {

            }
        },
        methods: {
        },
    }
</script>

<style scoped>
    .container#available_updates {
        background: #fff;
        box-shadow: 0 0 1px rgba(0,0,0,.15);
        border-radius: 0.3rem;
        padding: 15px;
    }
</style>