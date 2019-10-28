<template>
    <footer class="d-print-none mt-3 mb-3">
        <div class="container">
            JTL Shop Installer &copy; 2019, JTL-Software-GmbH
            <b-form-select :label="$t('language')" v-model="current" v-on:change="changeLocale" :options="languages"></b-form-select>
        </div>
    </footer>
</template>

<script>
export default {
    name: 'pageFooter',
    data() {
        return {
            languages: [
                { value: 'de', text: 'deutsch' },
                { value: 'en', text: 'english' }
            ],
            current:   this.$i18n.locale()
        };
    },
    methods: {
        changeLocale(locale) {
            if (this.$i18n.localeExists(locale)) {
                this.$i18n.set(locale);
            }
        }
    }
};
</script>

<style scoped>
    footer {
        color: #fff;
    }
</style>
