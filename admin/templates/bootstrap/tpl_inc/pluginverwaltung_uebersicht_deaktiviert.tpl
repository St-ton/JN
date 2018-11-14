<div id="deaktiviert" class="tab-pane fade {if isset($cTab) && $cTab === 'deaktiviert'} active in{/if}">
    {if $pluginsByState.status_1|@count > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
            {$jtl_token}
            <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#pluginListNotActivated#}</h3>
                </div>
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th></th>
                            <th class="tleft">{#pluginName#}</th>
                            <th>{#status#}</th>
                            <th>{#pluginVersion#}</th>
                            <th>{#pluginInstalled#}</th>
                            <th>{#pluginFolder#}</th>
                            <th>{#pluginEditLocales#}</th>
                            <th>{#pluginEditLinkgrps#}</th>
                            <th>{#pluginBtnLicence#}</th>
                            <th>&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $pluginsByState.status_1 as $plugin}
                            <tr {if $plugin->updateAvailable === true && $plugin->cFehler === ''}class="highlight"{/if}>
                                <td class="check">
                                    <input type="checkbox" name="kPlugin[]" id="plugin-check-{$plugin->kPlugin}" value="{$plugin->kPlugin}" />
                                </td>
                                <td>
                                    <label for="plugin-check-{$plugin->kPlugin}">{$plugin->cName}</label
                                    {if $plugin->updateAvailable === true || (isset($plugin->cInfo) && $plugin->cInfo|strlen > 0)}
                                        <p>
                                            {if $plugin->cFehler === ''}
                                                {if isset($plugin->cInfo) && $plugin->cInfo|strlen > 0}{$plugin->cInfo}<br />{/if}{#pluginUpdateExists#}
                                            {else}
                                                {if isset($plugin->cInfo) && $plugin->cInfo|strlen > 0}{$plugin->cInfo}<br />{/if}{#pluginUpdateExists#}. <br />{#pluginUpdateExistsError#}: <br />{$plugin->cUpdateFehler}
                                            {/if}
                                        </p>
                                    {/if}
                                </td>
                                <td class="tcenter plugin-status">
                                    <h4 class="label-wrap text-nowrap">
                                        <span class="label {if $plugin->nStatus === \Plugin\State::ACTIVATED}success label-success{elseif $plugin->nStatus == 1}success label-info{elseif $plugin->nStatus == 3}success label-default{elseif $plugin->nStatus == 4 || $plugin->nStatus == 5}info label-info{elseif $plugin->nStatus == 6}danger label-danger{/if}">
                                            {$mapper->map($plugin->nStatus)}
                                        </span>
                                        {if isset($PluginIndex_arr[$plugin->cVerzeichnis]->shop4compatible) && $PluginIndex_arr[$plugin->cVerzeichnis]->shop4compatible === false}
                                            <span title="Achtung: Plugin ist nicht vollst&auml;ndig Shop4-kompatibel! Es k&ouml;nnen daher Probleme beim Betrieb entstehen." class="label warning label-warning"><i class="fa fa-warning"></i></span>
                                        {/if}
                                    </h4>
                                </td>
                                <td class="tcenter plugin-version">{number_format($plugin->nVersion / 100, 2)}{if $plugin->updateAvailable === true} <span class="label label-success update-info">{number_format((float)$plugin->getCurrentVersion() / 100, 2)}</span>{/if}</td>
                                <td class="tcenter plugin-install-date">{$plugin->dInstalliert_DE}</td>
                                <td class="tcenter plugin-folder">{$plugin->cVerzeichnis}</td>
                                <td class="tcenter plugin-lang-vars">
                                    {if isset($plugin->oPluginSprachvariableAssoc_arr) && $plugin->oPluginSprachvariableAssoc_arr|@count > 0}
                                        <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$plugin->kPlugin}"
                                           class="btn btn-default btn-sm" title="{#modify#}"><i class="fa fa-edit"></i></a>
                                    {/if}
                                </td>
                                <td class="tcenter plugin-frontend-links">
                                    {if isset($plugin->oPluginFrontendLink_arr) && $plugin->oPluginFrontendLink_arr|@count > 0}
                                        <a href="links.php?kPlugin={$plugin->kPlugin}" class="btn btn-default btn-sm" title="{#modify#}"><i class="fa fa-edit"></i></a>
                                    {/if}
                                </td>
                                <td class="tcenter plugin-license">
                                    {if isset($plugin->cLizenzKlasse) && $plugin->cLizenzKlasse|strlen > 0}
                                        {if isset($plugin->cLizenzKlasse) && $plugin->cLizenzKlasse|strlen > 0}
                                            <button name="lizenzkey" type="submit" title="{#modify#}"
                                                    class="btn {if $plugin->cLizenz && $plugin->cLizenz|strlen > 0}btn-default{else}btn-primary{/if} btn-sm" value="{$plugin->kPlugin}">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                        {/if}
                                    {/if}
                                </td>
                                <td class="tcenter">
                                    {if $plugin->updateAvailable === true && $plugin->cFehler === ''}
                                        <a onclick="ackCheck({$plugin->kPlugin}, 'deaktiviert'); return false;" class="btn btn-primary btn-sm" title="{#pluginBtnUpdate#}"><i class="fa fa-refresh"></i></a>
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                        <tfoot>
                        <tr>
                            <td class="check"><input name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);" /></td>
                            <td colspan="10"><label for="ALLMSGS2">{#pluginSelectAll#}</label></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="panel-footer">
                    <div class="save btn-group">
                        <button name="aktivieren" type="submit" class="btn btn-primary"><i class="fa fa-share"></i> {#pluginBtnActivate#}</button>
                        {*<button name="deaktivieren" type="submit" class="btn btn-warning">{#pluginBtnDeActivate#}</button>*}
                        <button name="deinstallieren" type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {#pluginBtnDeInstall#}</button>
                    </div>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
    {/if}
</div>