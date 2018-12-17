<template>
    <div>
        <jumbotron header="Herzlich Willkommen"
                   lead="bei der Installation Ihres neuen JTL-Shops"
                   content="<p>Wir freuen uns, dass Sie sich für JTL-Shop entschieden haben. Bei dieser Installation führen wir Sie Schritt für Schritt durch die Installation Ihres neuen Shops.<br>
                    Tipps und Hilfestellungen zur Installation finden Sie in unserem Installationsguide. Bei offenen Fragen können Sie eine Anfrage im  Kundencenter stellen. Einer unserer Mitarbeiter hilft Ihnen gerne weiter.<br>
                    <strong>Wir wünschen Ihnen viel Erfolg und viel Freude mit Ihrem neuen JTL-Shop!</strong></p>"
                   :btn1="{href: 'https://guide.jtl-software.de/JTL-Shop_neu_installieren', text: 'Installationsguide', target: '_blank'}"
                   :btn2="{href: 'https://kundencenter.jtl-software.de/', text: 'Kundencenter', target: '_blank'}"
        >
        </jumbotron>
        <continue></continue>
    </div>
</template>

<script>
    export default {
        name:  'hello',
        data() {
            return { };
        }
    };
</script>
