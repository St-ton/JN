<div id="fehlerhaft" class="tab-pane fade {if isset($cTab) && $cTab === 'fehlerhaft'} active in{/if}">
    {if isset($PluginFehlerhaft_arr) && $PluginFehlerhaft_arr|@count > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
            {$jtl_token}
            <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#pluginListNotInstalledAndError#}</h3>
                </div>
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th class="tleft">{#pluginName#}</th>
                            <th class="tleft">{#pluginErrorCode#}</th>
                            <th>{#pluginVersion#}</th>
                            <th>{#pluginFolder#}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach from=$PluginFehlerhaft_arr item=PluginFehlerhaft}
                            <tr>
                                <td>
                                    <strong>{$PluginFehlerhaft->cName}</strong>
                                    <p>{$PluginFehlerhaft->cDescription}</p>
                                </td>
                                <td>
                                    <span class="badge error">{$PluginFehlerhaft->cFehlercode}</span>
                                    {$PluginFehlerhaft->cFehlerBeschreibung}
                                </td>
                                <td class="tcenter">{$PluginFehlerhaft->cVersion}</td>
                                <td class="tcenter">{$PluginFehlerhaft->cVerzeichnis}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
    {/if}
</div>