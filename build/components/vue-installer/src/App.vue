<template>
    <div id="app">
        <page-header></page-header>
        <div class="container" id="content">
            <router-view></router-view>
        </div>
        <page-footer></page-footer>
    </div>
</template>

<script>
import PageHeader from './components/Header';
import PageFooter from './components/Footer';
export default {
    name:       'app',
    components: { PageFooter, PageHeader }
};
</script>

<style>
    #app {
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    body {
        background-color: #5cbcf6!important;
        font-size: 14px;
        font-family: 'Source Sans Pro',Helvetica,Arial,sans-serif!important;
    }
    h3, .h3 {
        font-size: 1.5rem;
    }
    #content {
        background: #fff;
        box-shadow: 0 0 1px rgba(0,0,0,.15);
        border-radius: 0.3rem;
        padding: 15px;
    }
    .card-header h3 {
        margin-bottom: 0;
    }
    .input-group-addon {
        min-width: 2.5rem;
    }
</style>
