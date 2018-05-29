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
                {foreach $opc->getPageDrafts($opc->getCurPage()->getId()) as $draft}
                    <form action="admin/onpage-composer.php">
                        <input type="hidden" name="token" value="{$smarty.session.jtl_token}">
                        <input type="hidden" name="pageKey" value="{$draft->kPage}">
                        <button name="action" value="edit" class="btn">Bearbeite {$draft->cName}</button>
                        <button name="action" value="discard" class="btn">Verwerfe {$draft->cName}</button>
                    </form>
                {/foreach}
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
