<div id="fehlerhaft" class="tab-pane fade {if isset($cTab) && $cTab === 'fehlerhaft'} active in{/if}">
    {if isset($PluginFehlerhaft_arr) && $PluginFehlerhaft_arr|@count > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
            {$jtl_token}
            <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{__("pluginListNotInstalledAndError")}</h3>
                </div>
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th class="tleft">{__("pluginName")}</th>
                            <th class="tleft">{__("pluginErrorCode")}</th>
                            <th>{__("pluginVersion")}</th>
                            <th>{__("pluginFolder")}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach from=$PluginFehlerhaft_arr item=PluginFehlerhaft}
                            <tr>
                                <td>
                                    <strong>{if !empty($PluginFehlerhaft->cName)}{$PluginFehlerhaft->cName}{/if}</strong>
                                    <p>{if !empty($PluginFehlerhaft->cDescription)}{$PluginFehlerhaft->cDescription}{/if}</p>
                                </td>
                                <td>
                                    <p>
                                        <span class="badge error">{if !empty($PluginFehlerhaft->cFehlercode)}{$PluginFehlerhaft->cFehlercode}{/if}</span>
                                        {if !empty($PluginFehlerhaft->cFehlerBeschreibung)}{$PluginFehlerhaft->cFehlerBeschreibung}{/if}
                                    </p>
                                </td>
                                <td class="tcenter">{if !empty($PluginFehlerhaft->cVersion)}{$PluginFehlerhaft->cVersion}{/if}</td>
                                <td class="tcenter">{if !empty($PluginFehlerhaft->cVerzeichnis)}{$PluginFehlerhaft->cVerzeichnis}{/if}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info" role="alert">{__("noDataAvailable")}</div>
    {/if}
</div>