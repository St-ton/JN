<div id="verfuegbar" class="tab-pane fade {if isset($cTab) && $cTab === 'verfuegbar'} active in{/if}">
    {if isset($PluginVerfuebar_arr) && $PluginVerfuebar_arr|@count > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
            {$jtl_token}
            <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#pluginListNotInstalled#}</h3>
                </div>
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th></th>
                            <th class="tleft">{#pluginName#}</th>
                            <th>{#pluginVersion#}</th>
                            <th>{#pluginFolder#}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach name="verfuergbareplugins" from=$PluginVerfuebar_arr item=PluginVerfuebar}
                            <tr>
                                <td class="check"><input type="checkbox" name="cVerzeichnis[]" id="plugin-check-{$PluginVerfuebar->cVerzeichnis}" value="{$PluginVerfuebar->cVerzeichnis}" /></td>
                                <td>
                                    <label for="plugin-check-{$PluginVerfuebar->cVerzeichnis}">{$PluginVerfuebar->cName}</label>
                                    <p>{$PluginVerfuebar->cDescription}</p>
                                    {if isset($PluginVerfuebar->shop4compatible) && $PluginVerfuebar->shop4compatible === false}
                                        <div class="alert alert-info"><strong>Achtung:</strong> Plugin ist nicht vollst&auml;ndig Shop4-kompatibel! Es k&ouml;nnen daher Probleme beim Betrieb entstehen.</div>
                                    {/if}
                                </td>
                                <td class="tcenter">{$PluginVerfuebar->cVersion}</td>
                                <td class="tcenter">{$PluginVerfuebar->cVerzeichnis}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                        <tfoot>
                        <tr>
                            <td class="check"><input name="ALLMSGS" id="ALLMSGS4" type="checkbox" onclick="AllMessages(this.form);" /></td>
                            <td colspan="5"><label for="ALLMSGS4">{#pluginSelectAll#}</label></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="panel-footer">
                    <button name="installieren" type="submit" class="btn btn-primary"><i class="fa fa-share"></i> {#pluginBtnInstall#}</button>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
    {/if}
</div>