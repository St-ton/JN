{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{if $cHinweis}
    <div class="alert alert-danger error_log" xmlns="http://www.w3.org/1999/html" xmlns="http://www.w3.org/1999/html">
        <p>Bei der Datenbankeinrichtung sind Fehler aufgetreten.</p>
        {$cHinweis}
    </div>
    <div class="alert alert-info">
        <p>Hilfe zur Installation erhalten Sie <a href="https://www.jtl-software.de" target="_blank"><i class="fa fa-external-link"></i> hier</a></p>
    </div>
{else}
    <script src="{$URL_SHOP}install/template/js/confetti.js" type="text/javascript"></script>
    <h2 class="welcome no-print">Herzlichen Gl&uuml;ckwunsch!</h2>

    <div class="alert alert-success no-print">
        <i class="fa fa-thumbs-up"></i> JTL-Shop-Installation erfolgreich abgeschlossen!
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Webshop-Einstellungen in JTL-Wawi eintragen</h3>
        </div>
        <div class="panel-body">
            <p>Tragen Sie nun die nachfolgenden Daten im Men&uuml; <code>Webshop -&gt; Webshop-Einstellungen</code> der JTL-Wawi ein.</p>

            <table class="table table-bordered">
                <tr>
                    <td><strong>Lizenzschl&uuml;ssel</strong></td>
                    <td>Den Lizenzschl&uuml;ssel f&uuml;r den JTL-Shop finden Sie im
                    <a target="_blank" href="https://kundencenter.jtl-software.de/"><i class="fa fa-external-link"></i>
                        JTL Kundencenter</a>
                    </td>
                </tr>
                <tr>
                    <td><strong>Webshop-URL</strong></td>
                    <td>{$URL_SHOP}</td>
                <tr>
                    <td><strong>Sync-Benutzer</strong></td>
                    <td>{$cPostVar_arr.syncuser}</td>
                </tr>
                <tr>
                    <td><strong>Sync-Passwort</strong></td>
                    <td>{$cPostVar_arr.syncpass}</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Admin-Backend Zugangsdaten</h3>
        </div>
        <div class="panel-body">
            <table class="table table-bordered">
                <tr>
                    <td><strong>Admin-Backend URL</strong></td>
                    <td>{$URL_SHOP}admin</td>
                </tr>
                <tr>
                    <td><strong>Benutzername</strong></td>
                    <td>{$cPostVar_arr.adminuser}</td>
                </tr>
                <tr>
                    <td><strong>Passwort</strong></td>
                    <td>{$cPostVar_arr.adminpass}</td>
                </tr>
                <tr>
                    <td><strong>Geheimer Schl&uuml;ssel</strong><br><small>Sensible Kundendaten werden in der Datenbank verschl&uuml;sselt gespeichert.<br> Ohne diesen Schl&uuml;ssel sind
                        die Daten nicht mehr rekonstruierbar</small>
                    </td>
                    <td>{$BLOWFISH_KEY}</td>
                </tr>
            </table>
        </div>
    </div>

    <div class="alert alert-info no-print">
        <p>Drucken Sie diese Seite aus und verwahren Sie diese gut.</p>

        <p>Bitte l&ouml;schen Sie nun das Installationsverzeichnis des Shops (/install) und entziehen Sie die Schreibrechte
            von der Datei <code>includes/config.JTL-Shop.ini.php</code>.</p>

        <p><strong>Wir w&uuml;nschen Ihnen viel Erfolg und Spa&szlig; mit Ihrem neuen JTL-Shop!</strong></p>
    </div>

    <div class="row hidden-print no-print">
        <div class="col-xs-12 col-md-6">
            <button type="button" class="btn btn-default pull-left" onClick="javascript:window.print(); return false;"><i class="fa fa-print"></i> Diese Seite drucken</button>
        </div>
        <div class="btn-group col-xs-12 col-md-6 pull-right" style="height:80px;" role="group">
            <a href="{$URL_SHOP}" class="btn btn-default" style="text-align:left;width:330px;position:absolute;right:15px"><i class="fa fa-share"></i> Hier gelangen Sie zu Ihrem Shop</a><br>
            <a href="{$URL_SHOP}admin/" class="btn btn-primary" style="text-align:left;width:330px;position:absolute;top:40px;right:15px"><i class="fa fa-share"></i> Hier gelangen Sie zu Ihrem Shop-Backend</a>
        </div>
    </div>
{/if}
