{include file='tpl_inc/header.tpl'}

<script>
{literal}
$(function() {
    
});
{/literal}
</script>

{function print2_bool key=null val=null more=null}
    <tr class="text-vcenter">
        <td><i class="fa {if $val}fa-check-square text-success{else}fa-minus-square text-danger{/if}"></i> <span>{$key}</span></td>
        <td class="text-right">{if $more}<a href="{$more}" class="btn btn-default btn-xs"><i class="fa fa-angle-double-right" aria-hidden="true"></i> Details</a></td>{/if}
    </tr>
{/function}

{include file='tpl_inc/systemcheck.tpl'}

<div id="content" class="container-fluid" style="padding-top: 10px;">
    <div class="row">

        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="heading-body"><h4 class="panel-title">Server</h4></div>
                    <div class="heading-right">
                        {*
                            <div class="btn-group btn-group-xs" role="group">
                                <a href="cache.php" class="btn btn-primary text-uppercase">System</a>
                                <a href="bilderverwaltung.php" class="btn btn-primary text-uppercase">Bilder</a>
                            </div>
                        *}
                        <div class="btn-group btn-group-xs">
                            <button class="btn btn-primary dropdown-toggle text-uppercase" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                Details <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a href="cache.php">System-Cache</a></li>
                                <li><a href="bilderverwaltung.php">Bilder-Cache</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6 border-right">
                            <div class="text-center">
                                {if $status->getObjectCache()->getResultCode() === 1}
                                    {$cacheOptions = $status->getObjectCache()->getOptions()}
                                    <i class="fa fa-check-circle text-success" style="font-size:4em"></i>
                                    <h3 style="margin-top:10px;margin-bottom:0">Aktiviert</h3>
                                    <span style="color:#c7c7c7">{$cacheOptions.method|ucfirst}</span>
                                {else}
                                    <i class="fa fa-exclamation-circle text-warning" style="font-size:4em"></i>
                                    <h3 style="margin-top:10px;margin-bottom:0">Deaktiviert</h3>
                                    <span style="color:#c7c7c7">System</span>
                                {/if}
                                
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                {$imageCache = $status->getImageCache()}
                                {if $imageCache->corrupted == 0}
                                    <i class="fa fa-check-circle text-success" style="font-size:4em"></i>
                                    <h3 style="margin-top:10px;margin-bottom:0">{$imageCache->total|number_format} Bilder</h3>
                                {else}
                                    <i class="fa fa-exclamation-circle text-warning" style="font-size:4em"></i>
                                    <h3 style="margin-top:10px;margin-bottom:0">{$imageCache->corrupted|number_format} Fehlerhaft</h3>
                                {/if}
                                <span style="color:#c7c7c7">Bilder</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {$shared = $status->getPluginSharedHooks()}
            {if count($shared) > 0}
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">Plugin</h4>
                    </div>
                    <div class="panel-body">
                        {$shared|dump}
                    </div>
                </div>
            {/if}
        </div>
        
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Allgemein</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-hover table-striped table-blank">
                        <tbody>
                            {print2_bool key='Datenbank-Struktur' val=$status->validDatabateStruct() more='dbcheck.php'}
                            {print2_bool key='Datei-Struktur' val=$status->validFileStruct() more='filecheck.php'}
                            {print2_bool key='Verzeichnisrechte' val=$status->validFolderPermissions() more='permissioncheck.php'}
                            {print2_bool key='Ausstehende Updates' val=$status->hasPendingUpdates() more='dbupdate.php'}
                            {print2_bool key='Installationsverzeichnis' val=$status->hasInstallDir()}
                            {print2_bool key='Template-Version' val=$status->hasDifferentTemplateVersion()}
                            {print2_bool key='Profiler aktiv' val=$status->hasActiveProfiler() more='profiler.php'}
                            {print2_bool key='Server' val=$status->hasValidEnvironment() more='systemcheck.php'}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">Subskription</h4>
                </div>
                <div class="panel-body">
                    {$sub = $status->getSubscription()}
                    {if (int)$sub->bUpdate === 0}
                        <dl class="dl-horizontal">
                          <dt>Typ</dt>
                          <dd>{$sub->eTyp}</dd>
                          <dt>Version</dt>
                          <dd>{formatVersion value=$sub->oShopversion->nVersion}</dd>
                          <dt>Domain</dt>
                          <dd>{$sub->cDomain}</dd>
                          <dt>G&uuml;ltig bis</dt>
                          <dd>{$sub->dDownloadBis_DE} <span class="text-muted">({$sub->nDayDiff} Tage)</span></dd>
                        </dl>
                    {/if}
                </div>
            </div>

            {$tests = $status->getEnvironmentTests()}

            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="heading-body"><h4 class="panel-title">Server</h4></div>
                    <div class="heading-right">
                        <a href="systemcheck.php" class="btn btn-primary btn-xs text-uppercase">Details</a>
                    </div>
                </div>
                <div class="panel-body">
                    {if $tests.recommendations|count > 0}
                        <table class="table table-hover table-striped table-blank">
                            <thead>
                                <tr>
                                    <th class="col-xs-7">&nbsp;</th>
                                    <th class="col-xs-3 text-center">Empfohlener Wert</th>
                                    <th class="col-xs-2 text-center">Ihr System</th>
                                </tr>
                            </thead>
                            <tbody>
                                {foreach $tests.recommendations as $test}
                                <tr class="text-vcenter">
                                    <td>
                                        <div class="test-name">
                                            {if $test->getDescription()|@count_characters > 0}
                                                <abbr title="{$test->getDescription()|utf8_decode}">{$test->getName()|utf8_decode}</abbr>
                                            {else}
                                                {$test->getName()|utf8_decode}
                                            {/if}
                                        </div>
                                    </td>
                                    <td class="text-center">{$test->getRequiredState()}</td>
                                    <td class="text-center">{call test_result test=$test}</td>
                                </tr>
                                {/foreach}
                            </tbody>
                        </table>
                    {else}
                        <div class="alert alert-success">
                            Okay!
                        </div>
                    {/if}
                </div>
            </div>

        </div>

    </div>
</div>