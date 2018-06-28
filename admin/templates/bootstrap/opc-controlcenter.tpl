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
                        <th>Seiten-ID</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                        {foreach array_slice(
                            $opcPageDB->getPages(), $pagesPagi->getFirstPageItem(), $pagesPagi->getPageItemCount()
                        ) as $page}
                            {assign var="publicPageRow" value=$opcPageDB->getPublicPageRow($page->cPageId)}
                            <tr>
                                <td>
                                    <a href="{$URL_SHOP}{$page->cPageUrl}" target="_blank">{$page->cPageUrl}</a>
                                </td>
                                <td>
                                    <a href="#page-{$page->cPageId}" data-toggle="collapse">{$page->cPageId}</a>
                                </td>
                                <td>
                                    <div class="btn-group pull-right">
                                        <button class="btn btn-default" title="Vorschau"
                                                data-src="{$URL_SHOP}{$page->cPageUrl}"
                                                data-toggle="modal"
                                                data-target="#previewModal">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                        <a class="btn btn-danger" title="Alle Entwürfe löschen"
                                           href="{strip}?token={$smarty.session.jtl_token}&
                                                 action=restore&pageId={$page->cPageId}{/strip}"
                                           onclick="return confirm('Wollen Sie wirklich alle Entwürfe für die Seite löschen?');">
                                            <i class="fa fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <div  class="collapse" id="page-{$page->cPageId}">
                                    <table class="list table ">
                                        <thead>
                                        <tr>
                                            <th>Entwurf</th>
                                            <th>Veröffentlichen Ab</th>
                                            <th>Veröffentlichen Bis</th>
                                            <th>Ersetzt/Erweitert</th>
                                            <th>Letzte Änderung</th>
                                            <th>Gerade bearbeitet</th>
                                            <th></th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        {foreach $opcPageDB->getDrafts($page->cPageId) as $draft}
                                            <tr>
                                                <td>{$draft->getName()}</td>
                                                <td>
                                                    {if empty($draft->getPublishFrom())}
                                                        <span class="text-danger">Unveröffentlicht</span>
                                                    {elseif $publicPageRow->kPage == $draft->getKey()}
                                                        <span class="text-success">
                                                            {$draft->getPublishFrom()|date_format:'%c'}
                                                        </span>
                                                    {else}
                                                        {$draft->getPublishFrom()|date_format:'%c'}
                                                    {/if}
                                                </td>
                                                <td>
                                                    {if empty($draft->getPublishTo())}
                                                        Auf unbestimmte Zeit
                                                    {else}
                                                        {$draft->getPublishTo()|date_format:'%c'}
                                                    {/if}
                                                </td>
                                                <td>{if $draft->isReplace()}Ersetzt{else}Erweitert{/if}</td>
                                                <td>{$draft->getLastModified()|date_format:'%c'}</td>
                                                <td>
                                                    {if empty($draft->getLockedBy())}{else}{$draft->getLockedBy()}{/if}
                                                </td>
                                                <td>
                                                    <div class="btn-group pull-right">
                                                        <a class="btn btn-primary" title="Bearbeiten" target="_blank"
                                                           href="{strip}./onpage-composer.php?
                                                                token={$smarty.session.jtl_token}&
                                                                pageKey={$draft->getKey()}&
                                                                action=edit{/strip}">
                                                            <i class="fa fa-pencil"></i>
                                                        </a>
                                                        <a class="btn btn-danger" title="Entwurf löschen"
                                                           href="{strip}?token={$smarty.session.jtl_token}&
                                                                 action=discard&
                                                                 pageKey={$draft->getKey()}{/strip}"
                                                           onclick="return confirm('Wollen Sie diesen Entwurf wirklich löschen?');">
                                                            <i class="fa fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        {/foreach}
                                        </tbody>
                                    </table>
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
                            <td>{$portlet->getTitle()}</td>
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
    $('.collapse').on('show.bs.collapse', function () {
        $('.collapse.in').collapse('hide');
    });
</script>

{include file='tpl_inc/footer.tpl'}