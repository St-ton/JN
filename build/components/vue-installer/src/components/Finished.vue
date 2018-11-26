<template>
    <div>
        <jumbotron header="Weiterführende Links"
                   lead="Falls Sie noch Fragen haben..."
                   content="">
        </jumbotron>

        <div class="row">
            <div class="col">
                <h3>Aktualisieren der Rechtsdaten &uumlber einen kostenpflichtigen Zusatzservice automatisieren</h3>

                <a href="https://guide.jtl-software.de/Plugins_f%C3%BCr_JTL-Shop_verwalten" target="_blank" rel="noopener"><icon name="external-link-alt"></icon> Plugins verwalten</a><br>

                <a href="https://guide.jtl-software.de/H%C3%A4ndlerbund-Plugin_in_JTL-Shop_einbinden" target="_blank" rel="noopener"><icon name="external-link-alt"></icon> Händlerbund</a>
                <br>
                <a href="https://www.jtl-software.de/Marktplatz-Detailansicht?id=564" target="_blank" rel="noopener"><icon name="external-link-alt"></icon> IT-Rechtskanzlei</a>
                <br>
                <a href="https://guide.jtl-software.de/Janolaw-Plugin_in_JTL-Shop_einbinden" target="_blank" rel="noopener"><icon name="external-link-alt"></icon> Janolaw</a>
                <br>

                <hr>
                <h3>Versandarten konfigurieren</h3>

                <a href="https://guide.jtl-software.de/Versandarten_in_JTL-Wawi_anlegen" target="_blank" rel="noopener"><icon name="external-link-alt"></icon> Versandarten in JTL-Wawi anlegen</a>
                <br>
                <a href="https://guide.jtl-software.de/Versandarten_in_JTL-Shop_einrichten" target="_blank" rel="noopener"><icon name="external-link-alt"></icon> Versandarten in JTL-Shop einrichten</a>
                <br>
                <a href="https://guide.jtl-software.de/Versandverwaltung:Versandarten_bearbeiten" target="_blank" rel="noopener"><icon name="external-link-alt"></icon> Versandkostenseite zur Aufstellung der Versandarten editieren</a><br>

                <hr>
                <h3>Paypal Plugin installieren und einrichten</h3>

                <a href="http://downloads.jtl-software.de/jtl-shop/PayPal-Plugin-Dokumentation_104.pdf" target="_blank" rel="noopener"><icon name="external-link-alt"></icon> PayPal Plugin</a>

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
