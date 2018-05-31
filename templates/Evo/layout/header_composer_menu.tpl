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
                {assign var="curPageKey" value=$opc->getCurPage()->getKey()}
                <div class="list-group">
                {foreach $opc->getPageDrafts($opc->getCurPage()->getId()) as $i => $draft}
                    {assign var="isPublic" value=($curPageKey == $draft->kPage)}
                    <form action="admin/onpage-composer.php"
                          class="list-group-item {if $isPublic}list-group-item-success{/if}">
                        <input type="hidden" name="token" value="{$smarty.session.jtl_token}">
                        <input type="hidden" name="pageKey" value="{$draft->kPage}">
                        {if $curPageKey == $draft->kPage}
                            <button name="action" value="edit" class="btn btn-primary btn-sm" title="Bearbeite Entwurf">
                                <i class="fa fa-newspaper-o"></i>
                                <b>{$draft->cName}</b>
                            </button>
                        {else}
                            <button name="action" value="edit" class="btn btn-sm" title="Bearbeite Entwurf">
                                <i class="fa fa-file-o"></i>
                                {$draft->cName}
                            </button>
                        {/if}
                        <div class="pull-right">
                            <button name="action" value="discard" class="btn btn-sm btn-danger"
                                    title="Lösche Entwurf">
                                <i class="fa fa-times"></i>
                            </button>
                        </div>
                        {*<button name="action" value="discard" class="btn">*}
                            {*{if $curPageKey == $draft->kPage}*}
                                {*<b>Verwerfe {$draft->cName}</b>*}
                            {*{else}*}
                                {*Verwerfe {$draft->cName}*}
                            {*{/if}*}
                        {*</button>*}
                    </form>
                {/foreach}
                </div>
                <form action="admin/onpage-composer.php">
                    <input type="hidden" name="token" value="{$smarty.session.jtl_token}">
                    <input type="hidden" name="pageId" value="{$opc->getCurPage()->getId()}">
                    <input type="hidden" name="pageUrl" value="{$opc->getCurPage()->getUrl()}">
                    <button name="action" value="extend" class="btn">Seite erweitern</button>
                    <button name="action" value="replace" class="btn">Seite ersetzen</button>
                    <button name="action" value="restore" class="btn">Seite wiederherstellen</button>
                </form>
            </div>
        </div>
    </div>
</div>
