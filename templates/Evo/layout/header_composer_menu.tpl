<div id="switcher">
    <div class="switcher" id="dashboard-config">
        <a href="#" class="dropdown-toggle parent btn-toggle" data-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-pencil"></i>
        </a>
        <div class="switcher-wrapper">
            <div class="switcher-header">
                <h2>OnPage Composer</h2>
            </div>
            <div class="switcher-content">
                <form id="start-editor" action="admin/onpage-composer.php" method="get" class="form-group">
                    <input type="hidden" name="token" value="{$smarty.session.jtl_token}">
                    <input type="hidden" name="cCmsPageIdHash" value="{$oCMSPage->cIdHash}">
                    <input type="hidden" name="cPageUrl" value="{$smarty.server.REQUEST_URI}">
                    {if !empty($oCMSPage->cFinalHtml_arr)}
                        <p>
                            <button type="submit" name="cAction" class="btn btn-primary"
                                    value="{if empty($oCMSPage->cFinalHtml_arr['editor_replace_all'])}extend{else}replace{/if}">
                                Inhalt bearbeiten
                            </button>
                        </p>
                        <p>
                            <button type="submit" name="cAction" class="btn" value="restore_default">
                                Seite zurücksetzen
                            </button>
                        </p>
                    {else}
                        <p>
                            <button type="submit" name="cAction" class="btn btn-primary" value="extend">
                                Seite erweitern
                            </button>
                        </p>
                        <p>
                            <button type="submit" name="cAction" class="btn btn-primary" value="replace">
                                Seite ersetzen
                            </button>
                        </p>
                    {/if}
                </form>
            </div>
        </div>
    </div>
</div>
