{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
<script type="text/javascript">
{literal}
    function hosterChanged(id) {
        if (id == 1 || id == 2) {
            $('#hoster1').slideDown();
        } else {
            $('#hoster1').slideUp();
        }
        if (id == 1) {
            $('#strato-help').hide();
            $('#einsundeins-help').show();
        }
        if (id == 2) {
            $('#einsundeins-help').hide();
            $('#strato-help').show();
        }
    }
{/literal}
</script>
<h2 class="welcome">Herzlich Willkommen bei der Installation Ihres neuen JTL Shops</h2>
<div class="well">
    <p>Wir freuen uns, dass Sie sich für JTL Shop entschieden haben. Bei dieser Installation führen wir Sie Schritt für Schritt durch die Installation Ihres neuen Shops.</p>
    <p>Tipps und Hilfestellungen zu der Installation finden Sie in unserem <a href="http://jtl-url.de/shop3inst" target="_blank"><i class="fa fa-external-link"></i> Installationsguide</a>. Bei offenen Fragen können Sie eine Anfrage im <a href="http://kundencenter.jtl-software.de/" target="_blank"><i class="fa fa-external-link"></i> Kundencenter</a> stellen. Einer unserer Mitarbeiter hilft Ihnen gerne weiter.</p>
    <p><strong>Wir wünschen Ihnen viel Erfolg und viel Freude mit Ihrem neuen JTL Shop!</strong></p>
</div>

{if isset($cHinweis) && $cHinweis|@count_characters > 0}
    <div class="alert alert-danger">
        <i class="fa fa-warning"></i> {$cHinweis}
    </div>
{/if}

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Erfüllt der Server alle Anforderungen?</h3>
    </div>
    <ul class="req list-group">
        <li class="list-group-item first{if !$oSafeMode->bOk} alert-danger{/if}">
            {if $oSafeMode->bOk}<i class="fa fa-check-circle-o ok"></i>{else}<i class="fa fa-exclamation-circle error"></i>{/if}
            {$oSafeMode->cText}
        </li>
        <li class="list-group-item second{if !$oSafeMode->bOk} alert-danger{/if}">
            {if $oPHPVersion->bOk}<i class="fa fa-check-circle-o ok"></i>{else}<i class="fa fa-exclamation-circle error"></i>{/if}
            {$oPHPVersion->cText}
        </li>
        <li class="list-group-item first{if !$oGDText->bOk} alert-danger{/if}">
            {if $oGDText->bOk}<i class="fa fa-check-circle-o ok"></i>{else}<i class="fa fa-exclamation-circle error"></i>{/if}
            {$oGDText->cText}
        </li>
        <li class="list-group-item second{if !$oMySQLText->bOk} alert-danger{/if}">
            {if $oMySQLText->bOk}<i class="fa fa-check-circle-o ok"></i>{else}<i class="fa fa-exclamation-circle error"></i>{/if}
            {$oMySQLText->cText}
        </li>
    </ul>
</div>

<div class="panel panel-default">
    <div class="panel-heading">
        <h3 class="panel-title">Überprüfe Schreibrechte</h3>
    </div>
    <ul class="list-group req">
        {foreach name=beschreibbareverzeichnisse from=$cVerzeichnis_arr key=cVerzeichnis item=bBeschreibbar}
            <li class="list-group-item {if $smarty.foreach.beschreibbareverzeichnisse.index % 2 == 0}first{else}second{/if}{if !$bBeschreibbar} alert-danger{/if}">
                {if $bBeschreibbar}<i class="fa fa-check-circle-o ok"></i>{else}<i class="fa fa-exclamation-circle error"></i>{/if}
                {$cVerzeichnis}
            </li>
        {/foreach}
    </ul>
</div>

{if $bOk}
    <form name="install" method="post" action="index.php" class="form-horizontal">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Datenbank einrichten</h3>
            </div>
            <div class="panel-body">
                <div class="well">
                    <p>Für die Installation des JTL Shops benötigen wir eine Datenbank.</p>

                    <p>Meistens müssen der Benutzer und die Datenbank erst manuell erstellt werden. Bei Problemen wenden Sie sich
                        bitte an Ihren Administrator bzw. Webhoster, da dieser Vorgang von Hoster zu Hoster unterschiedlich ist und von der eingesetzten Software abhängt.</p>

                    <p>Der Benutzer benötigt Lese-, Schreib- und Löschrechte (<i>Create, Insert, Update, Delete</i>) für diese Datenbank.</p>

                    <p>Als <strong>Host</strong> ist "localhost" zumeist die richtige Einstellung. Diese Information bekommen Sie ebenfalls von Ihrem Webhoster.</p>
                    <p>Das Feld <strong>Socket</strong> füllen Sie bitte nur aus, wenn Sie ganz sicher sind, dass Ihre Datenbank über einen Sockets erreichbar ist. In diesem Fall tragen Sie bitte den absoluten Pfad zum Socket ein.</p>
                </div>
                <div class="col-xs-12">
                    <div class="form-group">
                        <div class="col-sm-6 input-group">
                            <span class="input-group-addon fixed-addon"><strong>Host</strong></span>
                            <input class="form-control" id="dbhost" type="text" name="DBhost" required size="35" value="localhost" placeholder="Host" />
                            <span class="input-group-addon">
                                <i class="fa fa-home"></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-6 input-group">
                            <span class="input-group-addon fixed-addon"><strong>Socket (optional)</strong></span>
                            <input class="form-control" id="dbsocket" type="text" name="DBsocket" size="35" value="" placeholder="Socket (z.B. /tmp/mysql5.sock)" />
                            <span class="input-group-addon">
                                <i class="fa fa-exchange"></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-6 input-group">
                            <span class="input-group-addon fixed-addon"><strong>Benutzername</strong></span>
                            <input class="form-control" id="dbuser" type="text" name="DBuser" required size="35" placeholder="Datenbank-Benutzername" />
                            <span class="input-group-addon">
                                <i class="fa fa-user"></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-6 input-group">
                            <span class="input-group-addon fixed-addon"><strong>Passwort</strong></span>
                            <input class="form-control" id="dbpass" type="text" name="DBpass" required size="35" placeholder="Datenbank-Passwort" />
                            <span class="input-group-addon">
                                <i class="fa fa-lock"></i>
                            </span>
                        </div>
                    </div>
                    <div class="form-group" style="margin-bottom: 0;">
                        <div class="col-sm-6 input-group">
                            <span class="input-group-addon fixed-addon"><strong>Datenbank-Name</strong></span>
                            <input class="form-control" id="dbname" type="text" name="DBname" required size="35" placeholder="Datenbank-Name" />
                            <span class="input-group-addon">
                                <i class="fa fa-database"></i>
                            </span>
                        </div>
                    </div>
                    <input type="hidden" name="installiere" value="1" />
                </div>
            </div>
        </div>
        <button type="submit" class="btn btn-primary pull-right">Installation starten</button>
    </form>
{/if}