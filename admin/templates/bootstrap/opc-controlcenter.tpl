{include file='tpl_inc/header.tpl'}
{config_load file="$lang.conf" section='opc'}
{include file='tpl_inc/seite_header.tpl' cTitel=__('opc') cBeschreibung=__('opcDesc') cDokuURL=__('opcUrl')}

<ul class="nav nav-tabs">
    <li class="active">
        <a data-toggle="tab" href="#pages">{__('opcPages')}</a>
    </li>
    <li>
        <a data-toggle="tab" href="#portlets">{__('opcPortlets')}</a>
    </li>
    <li>
        <a data-toggle="tab" href="#blueprints">{__('opcBlueprints')}</a>
    </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade active in" id="pages">
        <div class="panel panel-default">
            {assign var=allPages value=$opcPageDB->getPages()}
            {if $allPages|@count > 0}
                {assign var=pages value=array_slice(
                    $allPages,
                    $pagesPagi->getFirstPageItem(),
                    $pagesPagi->getPageItemCount()
                )}
                {include file='tpl_inc/pagination.tpl' oPagination=$pagesPagi cParam_arr=['tab'=>'pages']}
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th>{__('url')}</th>
                            <th>{__('pageID')}</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                            {foreach $pages as $page}
                                {assign var=pageIdHash value=$page->cPageId|md5}
                                {assign var=publicPageRow value=$opcPageDB->getPublicPageRow($page->cPageId)}
                                <tr>
                                    <td>
                                        <a href="{$URL_SHOP}{$page->cPageUrl}" target="_blank">{$page->cPageUrl}</a>
                                    </td>
                                    <td>
                                        <a href="#page-{$pageIdHash}" data-toggle="collapse">{$page->cPageId}</a>
                                    </td>
                                    <td>
                                        <div class="btn-group pull-right">
                                            <button class="btn btn-default" title="{__('preview')}"
                                                    data-src="{$URL_SHOP}{$page->cPageUrl}"
                                                    data-toggle="modal"
                                                    data-target="#previewModal">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <a class="btn btn-danger" title="{__('deleteDraftAll')}"
                                               href="{strip}?token={$smarty.session.jtl_token}&
                                                     action=restore&pageId={$page->cPageId}{/strip}"
                                               onclick="return confirm('{__('sureDeleteAll')}');">
                                                <i class="fa fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="3">
                                        <div  class="collapse" id="page-{$pageIdHash}">
                                        <table class="list table ">
                                            <thead>
                                            <tr>
                                                <th>{__('draft')}</th>
                                                <th>{__('publicFrom')}</th>
                                                <th>{__('publicTill')}</th>
                                                <th>{__('replaceExtend')}</th>
                                                <th>{__('lastChange')}</th>
                                                <th>{__('changedNow')}</th>
                                                <th></th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            {foreach $opcPageDB->getDrafts($page->cPageId) as $draft}
                                                <tr>
                                                    <td>{$draft->getName()}</td>
                                                    <td>
                                                        {if empty($draft->getPublishFrom())}
                                                            <span class="text-danger">{__('unpublished')}</span>
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
                                                            {__('tillUnknown')}
                                                        {else}
                                                            {$draft->getPublishTo()|date_format:'%c'}
                                                        {/if}
                                                    </td>
                                                    <td>{if $draft->isReplace()}{__('replaced')}{else}{__('extended')}{/if}</td>
                                                    <td>{$draft->getLastModified()|date_format:'%c'}</td>
                                                    <td>
                                                        {if empty($draft->getLockedBy())}{else}{$draft->getLockedBy()}{/if}
                                                    </td>
                                                    <td>
                                                        <div class="btn-group pull-right">
                                                            <a class="btn btn-primary" title="{__('edit')}" target="_blank"
                                                               href="{strip}./onpage-composer.php?
                                                                    token={$smarty.session.jtl_token}&
                                                                    pageKey={$draft->getKey()}&
                                                                    action=edit{/strip}">
                                                                <i class="fa fa-pencil"></i>
                                                            </a>
                                                            <a class="btn btn-danger" title="{__('deleteDraft')}"
                                                               href="{strip}?token={$smarty.session.jtl_token}&
                                                                     action=discard&
                                                                     pageKey={$draft->getKey()}{/strip}"
                                                               onclick="return confirm('{__('sureDelete')}');">
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
            {else}
                <div class="alert alert-info" role="alert">
                    {__('noDataAvailable')}
                </div>
            {/if}
        </div>
    </div>
    <div class="tab-pane fade" id="portlets">
        <div class="panel panel-default">
            <div class="table-responsive">
                <table class="list table">
                    <thead>
                    <tr>
                        <th>{__('name')}</th>
                        <th>{__('group')}</th>
                        <th>{__('plugin')}</th>
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
                                    {$portlet->getPlugin()->getPluginID()}
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
            {assign var=blueprints value=$opc->getBlueprints()}
            {if $blueprints|@count > 0}
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th>{__('name')}</th>
                            <th>{__('portlet')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $blueprints as $blueprint}
                            <tr>
                                <td>{$blueprint->getName()}</td>
                                <td>{$blueprint->getInstance()->getPortlet()->getTitle()}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            {else}
                <div class="alert alert-info" role="alert">
                    {__('noDataAvailable')}
                </div>
            {/if}
        </div>
    </div>
</div>

<div id="previewModal" class="modal fade" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">×</button>
                <h3>{__('preview')}</h3>
            </div>
            <div class="modal-body">
                <iframe id="previewFrame" src="" style="zoom:0.60" width="99.6%" height="850" frameborder="0"></iframe>
            </div>
            <div class="modal-footer">
                <button class="btn" data-dismiss="modal">{__('ok')}</button>
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
