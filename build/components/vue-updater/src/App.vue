<template>
    <div id="app" class="container h-100">
        <div class="row align-items-center h-100">
            <div class="col-12 mx-auto">
                <div class="text-center">
                    <page-header></page-header>
                    <div class="container" id="content">
                        <router-view></router-view>
                    </div>
                    <page-footer></page-footer>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    import PageHeader from './components/Header';
    import PageFooter from './components/Footer';
    export default {
        name:       'app',
        components: {PageFooter, PageHeader}
    };
</script>

<style>
    html {
        height: 100%;
        background-color: #f0f0f0;
    }
    #app {
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    body {
        height: 100%;
        background-color: transparent;
        font-size: 14px;
        font-family: 'Source Sans Pro',Helvetica,Arial,sans-serif!important;
    }
    h3, .h3 {
        font-size: 1.5rem;
    }
    .card-header h3 {
        margin-bottom: 0;
    }
    .input-group-addon {
        min-width: 2.5rem;
    }
    .vertical-center > .container {
        max-width: 100%;
        display: inline-block;
        vertical-align: middle;
    }
</style>
