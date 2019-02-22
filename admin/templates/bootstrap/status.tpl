{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='systemcheck'}

<script>
    $(function() {
        $('.table tr[data-href]').each(function(){
            $(this).css('cursor','pointer').hover(
                function(){
                    $(this).addClass('active');
                },
                function(){
                    $(this).removeClass('active');
                })on('click', function(){
                    document.location = $(this).attr('data-href');
                }
            );
        });

        $('.grid').masonry({
            itemSelector: '.grid-item',
            columnWidth: '.grid-item',
            percentPosition: true
        });

    });
</script>

{function render_item title=null desc=null val=null more=null}
    <tr class="text-vcenter"{if $more} data-href="{$more}"{/if}>
        <td {if !$more}colspan="2"{/if}>
            {if $val}
                <i class="fa fa-check-circle text-success fa-fw" aria-hidden="true"></i>
            {else}
                <i class="fa fa-exclamation-circle text-danger fa-fw" aria-hidden="true"></i>
            {/if}
            <span>{$title}</span>
            {if $desc}<p class="text-muted"></p>{/if}
        </td>
        {if $more}
            <td class="text-right">
                <a href="{$more}" class="btn btn-default btn-xs text-uppercase">{__('details')}</a>
            </td>
        {/if}
    </tr>
{/function}

{include file='tpl_inc/systemcheck.tpl'}

<div id="content" class="container-fluid" style="padding-top: 10px;">
    <div class="grid">

        <div class="grid-item">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="heading-body"><h4 class="panel-title">{__('cache')}</h4></div>
                    <div class="heading-right">
                        <div class="btn-group btn-group-xs">
                            <button class="btn btn-primary dropdown-toggle text-uppercase" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                {__('details')} <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu">
                                <li><a href="cache.php">{__('systemCache')}</a></li>
                                <li><a href="bilderverwaltung.php">{__('imageCache')}</a></li>
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
                                    <i class="fa fa-check-circle text-four-times text-success"></i>
                                    <h3 style="margin-top:10px;margin-bottom:0">{__('activated')}</h3>
                                    <span style="color:#c7c7c7">{$cacheOptions.method|ucfirst}</span>
                                {else}
                                    <i class="fa fa-exclamation-circle text-four-times text-info"></i>
                                    <h3 style="margin-top:10px;margin-bottom:0">{__('deactivated')}</h3>
                                    <span style="color:#c7c7c7">{__('requirementsMet')}</span>
                                {/if}

                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-center">
                                {$imageCache = $status->getImageCache()}
                                <i class="fa fa-file-image-o text-four-times text-success"></i>
                                <h3 style="margin-top:10px;margin-bottom:0">
                                    {(($imageCache->generated[JTL\Media\Image::SIZE_XS] + $imageCache->generated[JTL\Media\Image::SIZE_SM] + $imageCache->generated[JTL\Media\Image::SIZE_MD] + $imageCache->generated[JTL\Media\Image::SIZE_LG]) / 4)|round:0}
                                </h3>
                                <span style="color:#c7c7c7">{__('imagesInCache')}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid-item">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{__('general')}</h4>
                </div>
                <div class="panel-body">
                    <table class="table table-hover table-striped table-blank text-x1 last-child">
                        <tbody>
                            {render_item title=__('databaseStructure') val=$status->validDatabaseStruct() more='dbcheck.php'}
                            {render_item title=__('fileStructure') val=($status->validModifiedFileStruct()&&$status->validOrphanedFilesStruct()) more='filecheck.php'}
                            {render_item title=__('directoryPermissions') val=$status->validFolderPermissions() more='permissioncheck.php'}
                            {render_item title=__('openUpdates') val=!$status->hasPendingUpdates() more='dbupdater.php'}
                            {render_item title=__('installDirectory') val=!$status->hasInstallDir()}
                            {render_item title=__('profilerActive') val=!$status->hasActiveProfiler() more='profiler.php'}
                            {render_item title=__('server') val=$status->hasValidEnvironment() more='systemcheck.php'}
                            {render_item title=__('orphanedCategories') val=$status->getOrphanedCategories() more='categorycheck.php'}
                            {render_item title=__('newPluginVersions') val=!$status->hasNewPluginVersions() more='pluginverwaltung.php'}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="grid-item">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h4 class="panel-title">{__('subscription')}</h4>
                </div>
                <div class="panel-body">
                    {if $sub === null}
                        <div class="alert alert-danger alert-sm">
                            <p><i class="fa fa-exclamation-circle"></i> {__('atmNoInfo')}</p>
                        </div>
                    {else}
                        <div class="row vertical-align">
                            <div class="col-md-3">
                                <div class="text-center">
                                    {if intval($sub->bUpdate) === 0}
                                        <i class="fa fa-check-circle text-four-times text-success"></i>
                                        <h3 style="margin-top:10px;margin-bottom:0">{__('valid')}</h3>
                                    {else}
                                        {if $sub->nDayDiff <= 0}
                                            <i class="fa fa-exclamation-circle text-four-times text-danger"></i>
                                            <h3 style="margin-top:10px;margin-bottom:0">{__('expired')}</h3>
                                        {else}
                                            <i class="fa fa-exclamation-circle text-four-times text-info"></i>
                                            <h3 style="margin-top:10px;margin-bottom:0">{{__('expiresInXDays')}|sprintf:{$sub->nDayDiff}}</h3>
                                        {/if}
                                    {/if}
                                </div>
                            </div>
                            <div class="col-md-9">
                                {if intval($sub->bUpdate) === 0}
                                    <table class="table table-hover table-striped table-blank text-x1 last-child">
                                        <tbody>
                                            <tr>
                                                <td class="text-muted text-right"><strong>{__('version')}</strong></td>
                                                <td>{formatVersion value=$sub->oShopversion->nVersion} <span class="label label-default">{$sub->eTyp}</span></td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted text-right"><strong>{__('domain')}</strong></td>
                                                <td>{$sub->cDomain}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted text-right"><strong>{__('valid')} {__('to')}</strong></td>
                                                <td>{$sub->dDownloadBis_DE} <span class="text-muted">({$sub->nDayDiff} {__('days')})</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                {/if}
                            </div>
                        </div>
                    {/if}
                </div>
            </div>
        </div>

        {$incorrectPaymentMethods = $status->getPaymentMethodsWithError()}
        {if count($incorrectPaymentMethods) > 0}
            <div class="grid-item">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{__('paymentTypes')}</h4>
                    </div>
                    <div class="panel-body">
                        <div class="alert alert-info">
                            {__('paymentTypesWithError')}
                        </div>

                        <table class="table table-condensed table-hover table-striped table-blank last-child">
                            <tbody>
                            {foreach $incorrectPaymentMethods as $s}
                                <tr class="text-vcenter">
                                    <td class="text-left" width="55">
                                        <h4 class="label-wrap"><span class="label label-danger" style="display:inline-block;width:3em">{$s->logCount}</span></h4>
                                    </td>
                                    <td class="text-muted"><strong>{$s->cName}</strong></td>
                                    <td class="text-right">
                                        <a class="btn btn-default btn-xs text-uppercase" href="zahlungsarten.php?a=log&kZahlungsart={$s->kZahlungsart}">{__('details')}</a>
                                    </td>
                                </tr>
                            {/foreach}
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        {/if}

        {$shared = $status->getPluginSharedHooks()}
        {if count($shared) > 0}
            <div class="grid-item">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4 class="panel-title">{__('plugin')}</h4>
                    </div>
                    <div class="panel-body">
                        <div class="alert alert-info">
                            {__('pluginsWithSameHook')}
                        </div>

                        <table class="table table-condensed table-hover table-striped table-blank last-child">
                            <tbody>
                            {foreach $shared as $s}
                                {if count($s) > 1}
                                    <tr>
                                        <td class="text-muted text-right" width="33%"><strong>{$s@key}</strong></td>
                                        <td width="66%">
                                            <ul class="list-unstyled">
                                                {foreach $s as $p}
                                                    <li>{$p->cName}</li>
                                                {/foreach}
                                            </ul>
                                        </td>
                                    </tr>
                                {/if}
                            {/foreach}
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        {/if}

        <div class="grid-item">
            {$tests = $status->getEnvironmentTests()}

            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="heading-body"><h4 class="panel-title">{__('server')}</h4></div>
                    <div class="heading-right">
                        <a href="systemcheck.php" class="btn btn-primary btn-xs text-uppercase">{__('details')}</a>
                    </div>
                </div>
                <div class="panel-body">
                    {if $tests.recommendations|count > 0}
                        <table class="table table-condensed table-hover table-striped table-blank">
                            <thead>
                            <tr>
                                <th class="col-xs-7">&nbsp;</th>
                                <th class="col-xs-3 text-center">{__('recommendedValue')}</th>
                                <th class="col-xs-2 text-center">{__('yourSystem')}</th>
                            </tr>
                            </thead>
                            <tbody>
                            {foreach $tests.recommendations as $test}
                                <tr class="text-vcenter">
                                    <td>
                                        <div class="test-name">
                                            {if $test->getDescription()|@count_characters > 0}
                                                <abbr title="{$test->getDescription()|escape:'html'}">{$test->getName()}</abbr>
                                            {else}
                                                {$test->getName()}
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
                            <p>{__('requirementsMet')}</p>
                        </div>
                    {/if}
                </div>
            </div>
        </div>

    </div>
</div>
{include file='tpl_inc/footer.tpl'}
