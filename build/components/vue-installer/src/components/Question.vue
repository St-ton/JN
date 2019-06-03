<template>
    <div class="row">
        <div class="col">
            <h3>{{ text }}</h3>
            <b-form-checkbox :id="'question' + qid"
                             v-model="selected"
                             :value="qid"
                             v-if="type === 0"
                             @click.native="test()"
            >
                {{ text }}
            </b-form-checkbox>
            <div>State: <strong>{{selected}}</strong></div>
        </div>
    </div>
</template>

<script>
export default {
    name:    'question',
    props:   ['text', 'qval', 'type', 'qid'],
    methods: {
        test() {
            console.log(this.selected);
        }
    },
    data() {
        return {
            selected: []
        };
    }
};
</script>
