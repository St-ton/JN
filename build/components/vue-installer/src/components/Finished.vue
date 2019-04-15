<template>
    <div>
        <jumbotron header="Weiterführende Links"
                   lead="Falls Sie noch Fragen haben..."
                   content="">
        </jumbotron>

        <div class="row">
            <div class="col">
                <h3>Aktualisieren der Rechtsdaten über einen kostenpflichtigen Zusatzservice automatisieren</h3>

                <a href="https://guide.jtl-software.de/jtl-shop/shop-erweitern/plugins-fuer-jtl-shop-verwalten/" target="_blank" rel="noopener"><icon name="external-link"></icon> Plugins verwalten</a><br>

                <a href="https://guide.jtl-software.de/jtl-shop/shop-erweitern/haendlerbund-plugin-einbinden/" target="_blank" rel="noopener"><icon name="external-link"></icon> Händlerbund</a><br>
                <a href="https://www.jtl-software.de/jtl-store/erweiterungen/it-recht-kanzlei" target="_blank" rel="noopener"><icon name="external-link"></icon> IT-Rechtskanzlei</a><br>
                <a href="https://guide.jtl-software.de/jtl-shop/shop-erweitern/janolaw-plugin-einbinden/" target="_blank" rel="noopener"><icon name="external-link"></icon> Janolaw</a><br>

                <hr>
                <h3>Versandarten konfigurieren</h3>

                <a href="https://guide.jtl-software.de/jtl-wawi/versand/versandarten-anlegen/" target="_blank" rel="noopener"><icon name="external-link"></icon> Versandarten in JTL-Wawi anlegen</a>
                <br>
                <a href="https://guide.jtl-software.de/jtl-wawi/versand/versandarten-aus-jtl-shop-mit-jtl-wawi-verknuepfen/" target="_blank" rel="noopener"><icon name="external-link"></icon> Versandarten aus JTL-Shop mit JTL-Wawi verknüpfen</a>
                <br>
                <a href="https://guide.jtl-software.de/jtl-wawi/versand/versandarten-anlegen-bearbeiten/" target="_blank" rel="noopener"><icon name="external-link"></icon> Versandkostenseite zur Aufstellung der Versandarten editieren</a><br>

                <hr>
                <h3>Paypal Plugin installieren und einrichten</h3>

                <a href="http://jtl-url.de/paypaldocs" target="_blank" rel="noopener"><icon name="external-link"></icon> PayPal Plugin</a>

                <hr>

                <b-alert variant="success" show>Die Installation ist abgeschlossen. Sie können dieses Fenster jetzt schließen.</b-alert>

                <continue :disableBack="false" :disable="true"></continue>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    name: 'finished',
    data() {
        return { };
    }
};
</script>
