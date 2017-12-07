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
                    <input type="hidden" name="cCmsPageIdHash" value="{$cCmsPageIdHash}">
                    {if !empty($oCMSPage->cFinalHtml_arr)}
                        <p>
                            <button type="submit" name="cAction" class="btn btn-default" value="restore_default">
                                Standardinhalt wiederherstellen
                            </button>
                        </p>
                    {/if}
                    {if empty($oCMSPage->cFinalHtml_arr['editor_replace_all'])}
                        <p>
                            <button type="submit" name="cAction" class="btn btn-primary" value="extend">Inhalt erweitern</button>
                        </p>
                    {/if}
                    {if empty($oCMSPage->cFinalHtml_arr) || !empty($oCMSPage->cFinalHtml_arr['editor_replace_all'])}
                        <p>
                            <button type="submit" name="cAction" class="btn btn-primary" value="replace">Inhalt ersetzen</button>
                        </p>
                    {/if}
                </form>
            </div>
        </div>
    </div>
</div>
