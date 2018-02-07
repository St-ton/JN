<div id="switcher">
    <div class="switcher" id="dashboard-config">
        <a href="#" class="dropdown-toggle parent btn-toggle" data-toggle="dropdown" aria-expanded="false">
            <i class="fa fa-pencil"></i>
        </a>
        <div class="switcher-wrapper">
            <div class="switcher-header">
                <h2>Live Editor</h2>
            </div>
            <div class="switcher-content">
                <form id="start-editor" action="admin/cms-live-editor.php" method="get" class="form-group">
                    <input type="hidden" name="token" value="{$smarty.session.jtl_token}">
                    <input type="hidden" name="cCmsPageIdHash" value="{$cCmsPageIdHash}">
                    <input type="hidden" name="cPageUrl" value="{$smarty.server.REQUEST_URI}">
                    {if !empty($oCMSPage->cFinalHtml_arr)}
                        <p>
                            <button type="submit" name="cAction" class="btn btn-default" value="restore_default">
                                Standardinhalt wiederherstellen
                            </button>
                        </p>
                        <p>
                            <button type="submit" name="cAction" class="btn btn-primary"
                                    value="{if empty($oCMSPage->cFinalHtml_arr['editor_replace_all'])}extend{else}replace{/if}">Inhalt
                                bearbeiten
                            </button>
                        </p>
                    {else}
                        <p>
                            <button type="submit" name="cAction" class="btn btn-primary" value="extend">Inhalt erweitern</button>
                        </p>
                        <p>
                            <button type="submit" name="cAction" class="btn btn-primary" value="replace">Inhalt ersetzen</button>
                        </p>
                    {/if}
                </form>
            </div>
        </div>
    </div>
</div>
