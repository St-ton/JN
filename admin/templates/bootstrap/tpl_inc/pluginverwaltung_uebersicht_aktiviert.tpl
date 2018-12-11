<div id="aktiviert" class="tab-pane fade {if !isset($cTab) || $cTab === 'aktiviert'} active in{/if}">
    {if $PluginInstalliertByStatus_arr.status_2|@count > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
            {$jtl_token}
            <input type="hidden" name="{$session_name}" value="{$session_id}" />
            <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{__('pluginListInstalled')}</h3>
                </div>
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th></th>
                            <th class="tleft">{__('pluginName')}</th>
                            <th>{__('status')}</th>
                            <th>{__('pluginVersion')}</th>
                            <th>{__('pluginInstalled')}</th>
                            <th>{__('pluginFolder')}</th>
                            <th>{__('pluginEditLocales')}</th>
                            <th>{__('pluginEditLinkgrps')}</th>
                            <th>{__('pluginBtnLicence')}</th>
                            <th>Aktionen</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach from=$PluginInstalliertByStatus_arr.status_2 item=PluginInstalliert}
                            <tr {if $PluginInstalliert->updateAvailable === true && $PluginInstalliert->cFehler === ''}class="highlight"{/if}>
                                <td class="check">
                                    <input type="checkbox" name="kPlugin[]" id="plugin-check-{$PluginInstalliert->kPlugin}" value="{$PluginInstalliert->kPlugin}" />
                                </td>
                                <td>
                                    <label for="plugin-check-{$PluginInstalliert->kPlugin}">{$PluginInstalliert->cName}</label>
                                    {if $PluginInstalliert->updateAvailable === true || (isset($PluginInstalliert->cInfo) && $PluginInstalliert->cInfo|strlen > 0)}
                                        <p>
                                            {if $PluginInstalliert->cFehler === ''}
                                                {if isset($PluginInstalliert->cInfo) && $PluginInstalliert->cInfo|strlen > 0}{$PluginInstalliert->cInfo}<br />{/if}{__('pluginUpdateExists')}
                                            {else}
                                                {if isset($PluginInstalliert->cInfo) && $PluginInstalliert->cInfo|strlen > 0}{$PluginInstalliert->cInfo}<br />{/if}{__('pluginUpdateExists')}. <br />{__('pluginUpdateExistsError')}: <br />{$PluginInstalliert->cFehler}
                                            {/if}
                                        </p>
                                    {/if}
                                </td>
                                <td class="tcenter plugin-status">
                                    <h4 class="label-wrap text-nowrap">
                                        <span class="label {if $PluginInstalliert->nStatus === \Plugin\Plugin::PLUGIN_ACTIVATED}success label-success{elseif $PluginInstalliert->nStatus == 1}success label-info{elseif $PluginInstalliert->nStatus == 3}success label-default{elseif $PluginInstalliert->nStatus == 4 || $PluginInstalliert->nStatus == 5}info label-info{elseif $PluginInstalliert->nStatus == 6}danger label-danger{/if}">
                                            {$mapper->map($PluginInstalliert->nStatus)}
                                        </span>
                                        {if isset($PluginIndex_arr[$PluginInstalliert->cVerzeichnis]->shop4compatible) && $PluginIndex_arr[$PluginInstalliert->cVerzeichnis]->shop4compatible === false}
                                            <span title="Achtung: Plugin ist nicht vollständig Shop4-kompatibel! Es können daher Probleme beim Betrieb entstehen." class="label warning label-warning"><i class="fa fa-warning"></i></span>
                                        {/if}
                                    </h4>
                                </td>
                                <td class="tcenter plugin-version">{number_format($PluginInstalliert->nVersion / 100, 2)}{if $PluginInstalliert->updateAvailable === true} <span class="badge update-available">{number_format((float)$PluginInstalliert->getCurrentVersion() / 100, 2)}</span>{/if}</td>
                                <td class="tcenter plugin-install-date">{$PluginInstalliert->dInstalliert_DE}</td>
                                <td class="tcenter plugin-folder">{$PluginInstalliert->cVerzeichnis}</td>
                                <td class="tcenter plugin-lang-vars">
                                    {if isset($PluginInstalliert->oPluginSprachvariableAssoc_arr) && $PluginInstalliert->oPluginSprachvariableAssoc_arr|@count > 0}
                                        <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$PluginInstalliert->kPlugin}&token={$smarty.session.jtl_token}"
                                           class="btn btn-default btn-sm" title="{__('modify')}"><i class="fa fa-edit"></i></a>
                                    {/if}
                                </td>
                                <td class="tcenter plugin-frontend-links">
                                    {if isset($PluginInstalliert->oPluginFrontendLink_arr) && $PluginInstalliert->oPluginFrontendLink_arr|@count > 0}
                                        <a href="links.php?kPlugin={$PluginInstalliert->kPlugin}"
                                           class="btn btn-default btn-sm" title="{__('modify')}"><i class="fa fa-edit"></i></a>
                                    {/if}
                                </td>
                                <td class="tcenter plugin-license">
                                    {if isset($PluginInstalliert->cLizenzKlasse) && $PluginInstalliert->cLizenzKlasse|strlen > 0}
                                        <button name="lizenzkey" type="submit" title="{__('modify')}"
                                                class="btn {if $PluginInstalliert->cLizenz && $PluginInstalliert->cLizenz|strlen > 0}btn-default{else}btn-primary{/if} btn-sm" value="{$PluginInstalliert->kPlugin}">
                                        <i class="fa fa-edit"></i></button>
                                    {/if}
                                </td>
                                <td class="tcenter plugin-config">
                                    {assign var=btnGroup value=false}
                                    {if (isset($PluginInstalliert->oPluginEinstellung_arr) && $PluginInstalliert->oPluginEinstellung_arr|@count > 0) || (isset($PluginInstalliert->oPluginAdminMenu_arr) && $PluginInstalliert->oPluginAdminMenu_arr|@count > 0) &&
                                    ($PluginInstalliert->updateAvailable === true && $PluginInstalliert->cFehler === '')}
                                        {assign var=btnGroup value=true}
                                    {/if}
                                    {if $btnGroup}
                                    <div class="btn-group" style="min-width:75px;">
                                        {/if}
                                        {if (isset($PluginInstalliert->oPluginEinstellung_arr) && $PluginInstalliert->oPluginEinstellung_arr|@count > 0) || (isset($PluginInstalliert->oPluginAdminMenu_arr) && $PluginInstalliert->oPluginAdminMenu_arr|@count > 0)}
                                            <a class="btn btn-default btn-sm" href="plugin.php?kPlugin={$PluginInstalliert->kPlugin}" title="Einstellungen"><i class="fa fa-cogs"></i></a>
                                        {else}
                                            {if (isset($PluginInstalliert->cTextReadmePath) && $PluginInstalliert->cTextReadmePath|count_characters > 0) || (isset($PluginInstalliert->cTextLicensePath) && $PluginInstalliert->cTextLicensePath|count_characters > 0)}
                                                <a class="btn btn-default btn-sm" href="plugin.php?kPlugin={$PluginInstalliert->kPlugin}" title="Dokumentation"><i class="fa fa-copy"></i></a>
                                                {*<a class="btn btn-default btn-sm" href="plugin.php?kPlugin={$PluginInstalliert->kPlugin}" title="Dokumentation"><i class="fa fa-file-text-o"></i></a>*}
                                            {/if}
                                        {/if}
                                        {if $PluginInstalliert->updateAvailable === true && $PluginInstalliert->cFehler === ''}
                                            <a onclick="ackCheck({$PluginInstalliert->kPlugin});return false;" class="btn btn-success btn-sm" title="{__('pluginBtnUpdate')}"><i class="fa fa-refresh"></i></a>
                                        {/if}
                                        {if $btnGroup}
                                    </div>
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                        <tfoot>
                        <tr>
                            <td class="check"><input name="ALLMSGS" id="ALLMSGS1" type="checkbox" onclick="AllMessages(this.form);" /></td>
                            <td colspan="10"><label for="ALLMSGS1">{__('pluginSelectAll')}</label></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="panel-footer">
                    <div class="save btn-group">
                        <button name="deaktivieren" type="submit" class="btn btn-warning"><i class="fa fa-close"></i> {__('pluginBtnDeActivate')}</button>
                        <button name="deinstallieren" type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {__('pluginBtnDeInstall')}</button>
                    </div>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
    {/if}
</div>