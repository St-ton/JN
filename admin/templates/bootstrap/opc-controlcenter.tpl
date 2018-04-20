{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='opc'}
{include file='tpl_inc/seite_header.tpl' cTitel=#opc# cBeschreibung=#opcDesc# cDokuURL=#opcUrl#}

<ul class="nav nav-tabs">
    <li class="active">
        <a data-toggle="tab" href="#pages">{#opcPages#}</a>
    </li>
    <li>
        <a data-toggle="tab" href="#portlets">{#opcPortlets#}</a>
    </li>
    <li>
        <a data-toggle="tab" href="#blueprints">{#opcBlueprints#}</a>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade active in" id="pages">
        <div class="panel panel-default">
            {include file='tpl_inc/pagination.tpl' oPagination=$pagesPagi cParam_arr=['tab'=>'pages']}
            <div class="table-responsive">
                <table class="list table">
                    <thead>
                    <tr>
                        <th>URL</th>
                        <th>Ersetzt/Erweitert</th>
                        <th>Letzte Änderung</th>
                        <th>Gerade bearbeitet</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                        {foreach array_slice($opc->getPages(), $pagesPagi->getFirstPageItem(), $pagesPagi->getPageItemCount()) as $page}
                            <tr>
                                <td>
                                    <a href="{$URL_SHOP}{$page->getUrl()}" target="_blank">{$page->getUrl()}</a>
                                </td>
                                <td>{if $page->isReplace()}Ersetzt{else}Erweitert{/if}</td>
                                <td>{$page->getLastModified()|date_format:'%c'}</td>
                                <td>{if empty($page->getLockedBy())}{else}{$page->getLockedBy()}{/if}</td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-default" title="Vorschau"
                                                data-src="{$URL_SHOP}{$page->getUrl()}" data-toggle="modal"
                                                data-target="#previewModal"><i class="fa fa-eye"></i>
                                        </button>
                                        <a class="btn btn-danger" title="Seite zurücksetzen"
                                           href="?token={$smarty.session.jtl_token}&action=restore&pageId={$page->getId()}">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                        <a class="btn btn-primary" title="Bearbeiten" target="_blank"
                                           href="onpage-composer.php?token={$smarty.session.jtl_token}&pageUrl={$page->getUrl()}&pageId={$page->getId()}&action=edit">
                                            <i class="fa fa-pencil"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="portlets">
        <div class="panel panel-default">
            <div class="table-responsive">
                <table class="list table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Gruppe</th>
                        <th>Plugin</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $opc->getPortletGroups() as $group}
                        {foreach $group->getPortlets() as $portlet}
                        <tr>
                            <td>{$portlet->getButtonHtml()}</td>
                            <td>{$portlet->getGroup()}</td>
                            <td>
                                {if $portlet->getPluginId() > 0}
                                    {$portlet->getPlugin()->cName}
                                {/if}
                            </td>
                        </tr>
                        {/foreach}
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="tab-pane fade" id="blueprints">
        <div class="panel panel-default">
            <div class="table-responsive">
                <table class="list table">
                    <thead>
                    <tr>
                        <th>Name</th>
                        <th>Portlet</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach $opc->getBlueprints() as $blueprint}
                        <tr>
                            <td>{$blueprint->getName()}</td>
                            <td>{$blueprint->getInstance()->getPortlet()->getTitle()}</td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div id="previewModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3>Preview</h3>
            </div>
            <div class="modal-body">
                <iframe id="previewFrame" src="" style="zoom:0.60" width="99.6%" height="850" frameborder="0"></iframe>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    $('#previewModal').on('show.bs.modal', function (event) {
        var button = $(event.relatedTarget);
        var frameSrc = button.data('src');

        var modal = $(this);
        modal.find('#previewFrame').attr('src', frameSrc);
    });
</script>

{*

{*
{if $links|@count > 0 && $links}
    {include file='tpl_inc/pagination.tpl' oPagination=$oPagiCmsLinks cAnchor='cmsLinks'}
    <div class="panel panel-default">
        {$jtl_token}
        <input type="hidden" name="cmsLinks" value="1" />
        <div class="table-responsive">
            <table class="list table table-hover">
                <thead>
                <tr>
                    <th class="tleft">interne ID</th>
                    <th class="tleft">URL</th>
                    <th class="tleft">last modified</th>
                    <th class="tleft">actions</th>
                </tr>
                </thead>
                <tbody>
                {foreach name=cmsLinks from=$links item=oLink}
                    <tr>
                        <td>{$oLink->kPage}</td>
                        <td>{$oLink->cPageURL}</td>
                        <td>{$oLink->dLastModified}</td>
                        <td>
                            <a href="{$oLink->cPageURL}" target="_blank" class="btn btn-default"
                               title="öffnet die ausgewählte Seite in einem neuen Fenster">
                                <i class="fa fa-external-link"></i> Shopseite öffnen
                            </a>

                            <button class="btn btn-default preview-btn" data-src="{$URL_SHOP}{$oLink->cPageURL}" data-toggle="modal"
                                    data-target="#previewModal">Vorschau
                            </button>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>

    <div id="previewModal" class="modal fade" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">×</button>
                    <h3>Preview</h3>
                </div>
                <div class="modal-body">
                    <iframe id="previewFrame" src="" style="zoom:0.60" width="99.6%" height="850" frameborder="0"></iframe>
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal">OK</button>
                </div>
            </div>
        </div>
    </div>
{else}
    <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
{/if}

<script>
    {literal}
        $('#previewModal').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var frameSrc = button.data('src');

            var modal = $(this);
            modal.find('#previewFrame').attr('src', frameSrc);
        });
    {/literal}
</script>
*}

{include file='tpl_inc/footer.tpl'}