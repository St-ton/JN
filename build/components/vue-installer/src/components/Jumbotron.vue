<template>
    <div>
    <b-jumbotron :header="header"
                 :lead="lead" class="d-print-none">
        <div v-html="content"></div>
        <span class="btn-group">
            <b-btn v-if="typeof btn1 !== 'undefined'" variant="primary" :href="btn1.href" :target="btn1.target" :rel="btn1.target == '_blank' ? 'noopener' : ''">
                <icon name="external-link-alt" v-if="btn1.target === '_blank'"></icon> {{ btn1.text}}
            </b-btn>
            <b-btn v-if="typeof btn2 !== 'undefined'" variant="secondary" :href="btn2.href" :target="btn1.target" :rel="btn2.target == '_blank' ? 'noopener' : ''">
                <icon name="external-link-alt" v-if="btn2.target === '_blank'"></icon> {{ btn2.text}}
            </b-btn>
            </span>
    </b-jumbotron>
    </div>
</template>

<script>
export default {
    name:  'jumbotron',
    props: ['header', 'lead', 'content', 'btn0', 'btn1', 'btn2']
};
</script>

<style>
    .jumbotron {
        padding: 2rem 1rem;
    }
    .jumbotron h1, .jumbotron h1.display-3 {
        font-size: 2rem;
    }
    .jumbotron .lead {
        font-size: 1rem;
    }
</style>
