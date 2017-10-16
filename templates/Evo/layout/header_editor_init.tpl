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
                            <input type="submit" name="restore_default" class="btn btn-primary" value="Standardinhalt wiederherstellen">
                        </p>
                    {/if}
                    {* <p>
                        <input type="submit" name="extend" class="btn btn-primary" value="Inhalt erweitern">
                    </p>*}
                    <p>
                        <input type="submit" name="replace" class="btn btn-success" value="Inhalt ersetzen">
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>
