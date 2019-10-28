<template>
    <header>
        <div class="container">
            <img src="../assets/logo.png">
        </div>
    </header>
</template>

<script>
export default {
    name:  'pageHeader',
    props: ['header', 'lead', 'content', 'btn0', 'btn1', 'btn2']
};
</script>

<style scoped>
    header .container {
        padding: 15px;
    }
</style>
