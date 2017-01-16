{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}
{if !empty($cHinweis)}
    <div class="alert alert-danger">
        {$cHinweis}
    </div>
{/if}
<form name="install" method="post" action="index.php" class="form-horizontal">
    <input type="hidden" name="installiereSchritt1" value="1" />
    <input type="hidden" name="DBname" value="{$cPostVar_arr.DBname}" />
    <input type="hidden" name="DBhost" value="{$cPostVar_arr.DBhost}" />
    <input type="hidden" name="DBuser" value="{$cPostVar_arr.DBuser}" />
    <input type="hidden" name="DBpass" value="{$cPostVar_arr.DBpass}" />
    <input type="hidden" name="DBsocket" value="{$cPostVar_arr.DBsocket}" />

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Shop-Administrator Benutzerdaten</h3>
        </div>
        <div class="panel-body">
            <div class="well">
                <p>Für das Backend des Shops (<code>http://www.mein-shop.de/admin</code>), wo Sie Shop-Einstellungen durchführen und Statistiken einsehen können, wird jetzt ein Administrator-Benutzer angelegt.</p>
            </div>
            <div class="col-xs-12">
                <div class="form-group">
                    <div class="col-sm-6 input-group">
                        <span class="input-group-addon fixed-addon"><strong>Benutzername</strong></span>
                        <input class="form-control" id="adminuser" type="text" name="adminuser" required size="35" value="admin" placeholder="Benutzername" />
                        <span class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </span>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <div class="col-sm-6 input-group">
                        <span class="input-group-addon fixed-addon"><strong>Passwort</strong></span>
                        <input class="form-control" id="adminpass" type="text" name="adminpass" required size="35" value="{$cAdminPass}" placeholder="Passwort" />
                        <span class="input-group-addon">
                            <i class="fa fa-lock"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="panel panel-default">
        <div class="panel-heading">
            <h3 class="panel-title">Wawi-Synchronisation</h3>
        </div>
        <div class="panel-body">
            <p class="well">
                Für die Synchronisation zwischen JTL-Wawi und JTL-Shop wird ein Synchronisations-Benutzer benötigt.<br>
                Diese Daten werden in den Webshop-Einstellungen der JTL-Wawi eingetragen (Näheres dazu im nächsten Schritt).
            </p>

            <div class="col-xs-12">
                <div class="form-group">
                    <div class="col-sm-6 input-group">
                        <span class="input-group-addon fixed-addon"><strong>Sync-Benutzer</strong></span>
                        <input class="form-control" id="syncuser" type="text" name="syncuser" required size="35" value="sync" placeholder="Benutzername" />
                        <span class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </span>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom: 0;">
                    <div class="col-sm-6 input-group">
                        <span class="input-group-addon fixed-addon"><strong>Sync-Passwort</strong></span>
                        <input class="form-control" id="syncpass" type="text" name="syncpass" required size="35" value="{$cSyncPass}" placeholder="Passwort" />
                        <span class="input-group-addon">
                            <i class="fa fa-lock"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <p class="tcenter">
        <button type="submit" class="submit btn btn-primary pull-right"><i class="fa fa-share"></i> Installation abschließen</button>
    </p>
</form>