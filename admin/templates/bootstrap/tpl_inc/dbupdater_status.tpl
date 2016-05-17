{config_load file="$lang.conf" section="dbupdater"}
{config_load file="$lang.conf" section="shopupdate"}

<form name="updateForm" method="post" id="form-update">
    {$jtl_token}
    <input type="hidden" name="update" value="1" />
    {if $updatesAvailable}
        <div class="alert alert-warning">
            <h4><i class="fa fa-warning"></i> Datenbankaktualisierung {if $currentDatabaseVersion != $currentFileVersion}von Version {formatVersion value=$currentDatabaseVersion} auf Version {formatVersion value=$currentFileVersion}{/if} erforderlich</h4>
            Klicken Sie auf <a href="dbupdater.php?action=update" data-callback="update">jetzt aktualisieren</a>, um die Datenbankaktualisierung durchzuf&uuml;hren. 
        </div>

        <div class="btn-group btn-group-md" id="btn-update-group" role="group">
            <a href="dbupdater.php?action=update" class="btn btn-success" data-callback="update"><i class="fa fa-flash"></i> Jetzt aktualisieren</a>
            <div class="btn-group btn-group-md" role="group">
                <button id="backup-button" type="button" class="btn btn-default dropdown-toggle ladda-button" data-size="l" data-style="zoom-out" data-spinner-color="#000" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="ladda-label">Sicherungskopie &nbsp; <i class="fa fa-caret-down"></i></span>
                </button>
                <ul class="dropdown-menu">
                    <li><a href="dbupdater.php?action=backup" data-callback="backup"><i class="fa fa-cloud-download"></i> &nbsp; Auf Server ablegen</a></li>
                    <li><a href="dbupdater.php?action=backup&download" data-callback="backup" data-download="true"><i class="fa fa-download"></i> &nbsp; Herunterladen</a></li>
                </ul>
            </div>
        </div>

        <br /><br />
        <h4>Ereignisprotokoll</h4>
        <pre id="debug"><div>{#currentShopVersion#}</div><div>     System: {formatVersion value=$currentFileVersion}</div><div>     Datenbank: {formatVersion value=$currentDatabaseVersion}</div>{if $currentTemplateFileVersion != $currentTemplateDatabaseVersion}<div>{#currentTemplateVersion#}</div><div>     System: {formatVersion value=$currentTemplateFileVersion}</div><div>     Datenbank: {formatVersion value=$currentTemplateDatabaseVersion}</div>{/if}</pre>
    {else}
        <div class="alert alert-success">
            <ul class="hlist">
                <li class="p50 text-x2 text-center"><i class="fa fa-check"></i> Ihr System ist auf dem neuesten Stand.</li>
                <li class="p50 text-x2 text-center">JTL-Shop {formatVersion value=$currentDatabaseVersion}</li>
            </ul>
        </div>
    {/if}
</form>

{if isset($migrations) && $migrations|@count > 0}
    <p>&nbsp;</p>

    <h4>Migrations
        {*<a href="dbupdater.php?action=migrate" data-callback="migrate" id="migrate-button" type="button" class="btn btn-default btn-sm pull-right ladda-button" data-size="l" data-style="zoom-out" data-spinner-color="#000" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="ladda-label"><i class="fa fa-flash text-primary"></i>&nbsp;Alle ausf&uuml;hren</span>
        </a>*}
    </h4>

    <table class="table table-hover">
        <thead>
        <tr>
            <th width="5%">#</th>
            <th width="15%" class="text-center">Version</th>
            <th width="50%">Migration</th>
            <th width="15%" class="text-center">Ausgef&uuml;hrt</th>
            <th width="15%" class="text-center"></th>
        </tr>
        </thead>
        <tbody>
        {$migrationIndex = 1}
        {foreach $migrations as $version => $migration}
            {$executedMigrations = $migration->getExecutedMigrations()}
            {foreach $migration->getMigrations()|@array_reverse as $m}
                {$executed = $m->getId()|in_array:$executedMigrations}
                <tr class="text-vcenter">
                    <th scope="row">{$migrationIndex++}</th>
                    <td class="text-center">{formatVersion value=$version}</td>
                    <td>{$m->getName()}<br><small class="text-muted">{$m->getDescription()}</small></td>
                    <td class="text-center"><span class="migration-created">{if $executed}<i class="fa fa-check text-success" aria-hidden="true"></i> {/if}{if $m->getCreated()}{$m->getCreated()|date_format:"d.m.Y - H:i:s"}{/if}</span></td>
                    <td class="text-center">
                        <a {if $executed}style="display:none"{/if} href="dbupdater.php?action=migration" data-callback="migration" data-dir="up" data-id="{$m->getId()}" data-version="{$version}" class="btn btn-success btn-xs" {if $executed}disabled="disabled"{/if}><i class="fa fa-arrow-up"></i></a>
                        <a {if !$executed}style="display:none"{/if} href="dbupdater.php?action=migration" data-callback="migration" data-dir="down" data-id="{$m->getId()}" data-version="{$version}" class="btn btn-warning btn-xs" {if !$executed}disabled="disabled"{/if}><i class="fa fa-arrow-down"></i></a>
                    </td>
                </tr>
            {/foreach}
        {/foreach}
        </tbody>
        <tfoot>
        <tr>
            <td colspan="4"></td>
            <td class="text-center">
                <div class="btn-group btn-group-xs" role="group">
                    <!--
                    <a href="dbupdater.php?action=migration" data-callback="migration" data-dir="up" data-version="{$version}" class="btn btn-default" title="up"><i class="fa fa-arrow-up text-success"></i></a>
                    <a href="dbupdater.php?action=migration" data-callback="migration" data-dir="down" data-version="{$version}" class="btn btn-default" title="down"><i class="fa fa-arrow-down text-danger"></i></a>
                    -->
                </div>
            </td>
        </tr>
        </tfoot>
    </table>
{/if}