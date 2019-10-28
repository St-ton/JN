<template>
    <div>
        <jumbotron header="Grundeinrichtung"
                   lead=""
                   content="">
        </jumbotron>
        <div class="row">
            <div class="col" v-if="questions !== null">
                <div v-for="question in questions" :key="question.id">
                    <b-form-checkbox :id="'question' + question.id"
                                     v-model="selected[question.id]"
                                     :value="true"
                                     v-if="question.type === 0"
                                     @change="submitData()"
                    >
                        {{ question.text }}
                    </b-form-checkbox>
                    <div v-else-if="question.type === 1">
                        <b-form-input v-model="selected[question.id]"
                                      type="text"
                                      :placeholder="question.text"></b-form-input>
                    </div>
                    <div v-else-if="question.type === 2">
                        <b-form-input v-model="selected[question.id]"
                                      type="email"
                                      :placeholder="question.text"></b-form-input>
                    </div>
                </div>
                <hr>
                <b-btn :class="{'pulse-button': true}" size="sm" variant="primary" @click="submitData()">
                    <icon name="save"></icon> speichern
                </b-btn>
                <continue :disableBack="false" :disable="error !== false"></continue>
            </div>
        </div>
    </div>
</template>

<script>
/* eslint-disable */
import Vue from 'vue';
import {mapGetters} from 'vuex';
import axios from 'axios';
import qs from 'qs';
export default {
    name:     'wizard',
    props:    ['wizardStepID'],
    data() {
        return {
            selected:  [],
            error:     false,
            questions: null
        };
    },
    computed: mapGetters({
        wawi:      'getWawiUser',
        admin:     'getAdminUser',
        shopURL:   'getShopURL',
        secretKey: 'getSecretKey'
    }),
    mounted() {
        this.getQuestions();
    },
    watch: {
        wizardStepID: function (value) {
            if (value < 3) {
                this.getQuestions();
            }
        }
    },
    methods: {
        submitData() {
            Vue.nextTick(() => {
                const postData = qs.stringify({
                    db:     this.$store.state.database,
                    action: 'setData',
                    data:   this.selected,
                    stepId: this.wizardStepID
                });
                axios.post(this.$getApiUrl('wizard'), postData)
                    .then(response => {
                        this.questions = response.data.payload.questions;
                    })
                    .catch(error => {
                        console.error('caught: ', error);
                    });
            });
        },
        getQuestions() {
            const postData = qs.stringify({
                db:     this.$store.state.database,
                stepId: this.wizardStepID
            });
            axios.post(this.$getApiUrl('wizard'), postData)
                .then(response => {
                    this.questions = response.data.payload.questions;
                })
                .catch(error => {
                    console.error('caught: ', error);
                });
        }
    }
};
</script>
