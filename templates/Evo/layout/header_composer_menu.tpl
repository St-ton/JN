{assign var="pageDrafts" value=$opc->getPageDrafts($opc->getCurPage()->getId())}
{assign var="curPageKey" value=$opc->getCurPage()->getKey()}

<div id="opc-switcher">
    <div class="switcher" id="dashboard-config">
        <a href="#" class="parent btn-toggle" aria-expanded="false" onclick="$('.switcher').toggleClass('open')">
            <i class="fa fa-pencil"></i>
        </a>
        <div class="switcher-wrapper">
            <div class="switcher-header">
                <h2>OnPage Composer</h2>
            </div>
            <div class="switcher-content">
                {assign var="query" value="?token="|cat:$smarty.session.jtl_token}
                {if $pageDrafts|count > 0}
                    <div class="list-group">
                        {if $curPageKey > 0}
                            <div class="list-group-item list-group-item-success">
                                {assign var="queryDraft" value=$query|cat:"&pageKey="|cat:$curPageKey}
                                <a href="admin/onpage-composer.php{$queryDraft}&action=edit"
                                   class="btn btn-sm opc-draft-item-edit" title="Entwurf bearbeiten">
                                    <b><i class="fa fa-newspaper-o"></i> {$opc->getCurPage()->getName()}</b>
                                </a>
                                <a href="admin/onpage-composer.php{$queryDraft}&action=discard"
                                   class="btn btn-sm btn-danger opc-draft-item-discard pull-right"
                                   title="Entwurf verwerfen">
                                    <i class="fa fa-times"></i>
                                </a>
                            </div>
                        {/if}
                        {foreach $pageDrafts as $i => $draft}
                            {if $curPageKey != $draft->kPage}
                                <div class="list-group-item">
                                    {assign var="queryDraft" value=$query|cat:"&pageKey="|cat:$draft->kPage}
                                    <a href="admin/onpage-composer.php{$queryDraft}&action=edit"
                                       class="btn btn-sm opc-draft-item-edit" title="Entwurf bearbeiten">
                                        <i class="fa fa-file-o"></i> {$draft->cName}
                                    </a>
                                    <a href="admin/onpage-composer.php{$queryDraft}&action=discard"
                                       class="btn btn-sm btn-danger opc-draft-item-discard pull-right"
                                       title="Entwurf verwerfen">
                                        <i class="fa fa-times"></i>
                                    </a>
                                </div>
                            {/if}
                        {/foreach}
                    </div>
                {/if}
                {assign var="query" value=$query|cat:"&pageId="|cat:$opc->getCurPage()->getId()}
                {assign var="query" value=$query|cat:"&pageUrl="|cat:$opc->getCurPage()->getUrl()}
                {if $pageDrafts|count > 0}
                    <p>
                        <a href="admin/onpage-composer.php{$query}&action=restore"
                           class="btn btn-sm btn-danger">
                            <i class="fa fa-times"></i>
                            Alle Entwürfe verwerfen
                        </a>
                    </p>
                    <p><label>Neuer Entwurf:</label></p>
                {/if}
                <div class="btn-group">
                    <a href="admin/onpage-composer.php{$query}&action=extend" class="btn btn-sm btn-primary">
                        <i class="fa fa-times"></i>
                        Seite erweitern
                    </a>
                    <a href="admin/onpage-composer.php{$query}&action=replace" class="btn btn-sm btn-primary">
                        <i class="fa fa-times"></i>
                        Seite ersetzen
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
