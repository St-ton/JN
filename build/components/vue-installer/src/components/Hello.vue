<template>
    <div>
        <jumbotron :header="$t('headerMsg')"
                   :lead="$t('leadMsg')"
                   :content="$t('contentMsg')"
                   :btn1="{href: 'https://guide.jtl-software.de/JTL-Shop_neu_installieren', text: $t('installGuide'), target: '_blank'}"
                   :btn2="{href: 'https://kundencenter.jtl-software.de/', text: $t('customerCenter'), target: '_blank'}"
        >
        </jumbotron>
        <continue></continue>
    </div>
</template>

<script>
export default {
    name:  'hello',
    data() {
        const messages = {
            de: {
                installGuide:   'Installationsguide',
                customerCenter: 'Kundencenter',
                headerMsg:      'Herzlich Willkommen',
                leadMsg:        'bei der Installation Ihres neuen JTL-Shops',
                contentMsg:     '<p>Wir freuen uns, dass Sie sich für JTL-Shop entschieden haben. '
                    + 'Bei dieser Installation führen wir Sie Schritt für Schritt durch die Installation '
                    + ' Ihres neuen Shops.<br>'
                    + 'Tipps und Hilfestellungen zur Installation finden Sie in unserem Installationsguide. '
                    + 'Bei offenen Fragen können Sie eine Anfrage im  Kundencenter stellen. '
                    + 'Einer unserer Mitarbeiter hilft Ihnen gerne weiter.<br>\n'
                    + '<strong>Wir wünschen Ihnen viel Erfolg und viel Freude mit Ihrem neuen JTL-Shop!</strong></p>'
            },
            en: {
                installGuide:   'Installation guide',
                customerCenter: 'Customer center',
                headerMsg:      'Welcome',
                leadMsg:        'to the installation of your new JTL-Shop',
                contentMsg:     '<p>Thank you for deciding to give JTL-Shop a try. '
                    + 'During this wizard we will guide you step by step through the installation of your new JTL-Shop<br>'
                    + 'You can find tipps and help in our install guide.'
            }
        };

        this.$i18n.add('en', messages.en);
        this.$i18n.add('de', messages.de);
        return { };
    }
};
</script>
