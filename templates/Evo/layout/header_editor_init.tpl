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
                {*<div id="editForm">
                    <button id="btn-start-cms" class="btn btn-primary">Go Edit</button>
                </div>*}
                <form id="start-editor" action="admin/editpage.php" method="post" class="form-group" data-ed-sprache="{$smarty.session.kSprache}"
                      data-ed-cKey="{$oLiveEditParams->cKey}" data-ed-kKey="{$oLiveEditParams->kKey}">
                    <input type="hidden" name="cKey" value="{$oLiveEditParams->cKey}">
                    <input type="hidden" name="kKey" value="{$oLiveEditParams->kKey}">
                    <input type="hidden" name="kSprache" value="{$smarty.session.kSprache}">
                    {if !empty($oLiveEditParams->oContent)}
                        <p>
                            {*todo editor: standard herstellen prüfen*}
                            <button type="submit" name="action" class="btn btn-default" value="restore_default">Standardinhalt wiederherstellen</button>
                        </p>
                    {/if}
                    <p>
                        <button type="submit" name="action" class="btn btn-primary" value="extend">Inhalt erweitern</button>
                    </p>
                    <p>
                        <button type="submit" name="action" class="btn btn-primary" value="replace">Inhalt ersetzen</button>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
