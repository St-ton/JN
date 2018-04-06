<div id="opc-switcher">
    <div class="switcher" id="dashboard-config">
        <a href="#" class="dropdown-toggle parent btn-toggle" data-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-pencil"></i>
        </a>
        <div class="switcher-wrapper">
            <div class="switcher-header">
                <h2>OnPage Composer</h2>
            </div>
            <div class="switcher-content">
                <form id="opc-menu" action="admin/onpage-composer.php" class="form-group">
                    <input type="hidden" name="token" value="{$smarty.session.jtl_token}">
                    <input type="hidden" name="pageUrl" value="{$opcPage->getUrl()}">
                    <input type="hidden" name="pageId" value="{$opcPage->getId()}">
                    {if $opc->curPageExists()}
                        <p>
                            <button name="action" class="btn btn-primary"
                                    value="{if $opcPage->isReplace()}replace{else}extend{/if}">
                                Inhalt bearbeiten
                            </button>
                        </p>
                        <p>
                            <button name="action" class="btn" value="restore">
                                Seite zurücksetzen
                            </button>
                        </p>
                    {else}
                        <p>
                            <button name="action" class="btn btn-primary" value="extend">
                                Seite erweitern
                            </button>
                        </p>
                        <p>
                            <button name="action" class="btn btn-primary" value="replace">
                                Seite ersetzen
                            </button>
                        </p>
                    {/if}
                </form>
            </div>
        </div>
    </div>
</div>
