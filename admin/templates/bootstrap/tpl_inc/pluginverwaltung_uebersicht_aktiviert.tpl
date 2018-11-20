<div id="aktiviert" class="tab-pane fade {if !isset($cTab) || $cTab === 'aktiviert'} active in{/if}">
    {if $pluginsByState.status_2|@count > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
            {$jtl_token}
            <input type="hidden" name="{$session_name}" value="{$session_id}" />
            <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#pluginListInstalled#}</h3>
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
                            <th>Aktionen</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $pluginsByState.status_2 as $plugin}
                            <tr {if $plugin->getMeta()->isUpdateAvailable() && $plugin->cFehler === ''}class="highlight"{/if}>
                                <td class="check">
                                    <input type="checkbox" name="kPlugin[]" id="plugin-check-{$plugin->getID()}" value="{$plugin->getID()}" />
                                </td>
                                <td>
                                    <label for="plugin-check-{$plugin->getID()}">{$plugin->getMeta()->getName()}</label>
                                    {if $plugin->getMeta()->isUpdateAvailable() || (isset($plugin->cInfo) && $plugin->cInfo|strlen > 0)}
                                        <p>
                                            {if $PluginInstalliert_arr->cFehler === ''}
                                                {if isset($plugin->cInfo) && $plugin->cInfo|strlen > 0}{$plugin->cInfo}<br />{/if}{#pluginUpdateExists#}
                                            {else}
                                                {if isset($plugin->cInfo) && $plugin->cInfo|strlen > 0}{$plugin->cInfo}<br />{/if}{#pluginUpdateExists#}. <br />{#pluginUpdateExistsError#}: <br />{$plugin->cFehler}
                                            {/if}
                                        </p>
                                    {/if}
                                </td>
                                <td class="tcenter plugin-status">
                                    <h4 class="label-wrap text-nowrap">
                                        <span class="label {if $plugin->getState() === \Plugin\State::ACTIVATED}success label-success{elseif $plugin->getState() == 1}success label-info{elseif $plugin->getState() == 3}success label-default{elseif $plugin->getState() == 4 || $plugin->getState() == 5}info label-info{elseif $plugin->getState() == 6}danger label-danger{/if}">
                                            {$mapper->map($plugin->getState())}
                                        </span>
                                        {foreach $allPluginItems as $p}
                                            {if $p->getID() === $plugin->getPluginID()}
                                                {if $p->isShop5Compatible() === false}
                                                    <span title="Achtung: Plugin ist nicht vollständig Shop5-kompatibel! Es können daher Probleme beim Betrieb entstehen." class="label warning label-warning"><i class="fa fa-warning"></i></span>
                                                {elseif $p->isShop5Compatible() === false && $p->isShop4Compatible() === false}
                                                    <span title="Achtung: Plugin ist nicht vollständig Shop4-kompatibel! Es können daher Probleme beim Betrieb entstehen." class="label warning label-warning"><i class="fa fa-warning"></i></span>
                                                {/if}
                                                {break}
                                            {/if}
                                        {/foreach}
                                    </h4>
                                </td>
                                <td class="tcenter plugin-version">{number_format($plugin->getMeta()->getVersion() / 100, 2)}{if $plugin->getMeta()->isUpdateAvailable()} <span class="badge update-available">{number_format((float)$plugin->getCurrentVersion() / 100, 2)}</span>{/if}</td>
                                <td class="tcenter plugin-install-date">{$plugin->getMeta()->getDateInstalled()->format('d.m.Y H:i')}</td>
                                <td class="tcenter plugin-folder">{$plugin->getPaths()->getBaseDir()}</td>
                                <td class="tcenter plugin-lang-vars">
                                    {if isset($plugin->oPluginSprachvariableAssoc_arr) && $plugin->oPluginSprachvariableAssoc_arr|@count > 0}
                                        <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$plugin->getID()}&token={$smarty.session.jtl_token}"
                                           class="btn btn-default btn-sm" title="{#modify#}"><i class="fa fa-edit"></i></a>
                                    {/if}
                                </td>
                                <td class="tcenter plugin-frontend-links">
                                    {if isset($plugin->oPluginFrontendLink_arr) && $plugin->oPluginFrontendLink_arr|@count > 0}
                                        <a href="links.php?kPlugin={$plugin->getID()}"
                                           class="btn btn-default btn-sm" title="{#modify#}"><i class="fa fa-edit"></i></a>
                                    {/if}
                                </td>
                                <td class="tcenter plugin-license">
                                    {if isset($plugin->cLizenzKlasse) && $plugin->cLizenzKlasse|strlen > 0}
                                        <button name="lizenzkey" type="submit" title="{#modify#}"
                                                class="btn {if $plugin->cLizenz && $plugin->cLizenz|strlen > 0}btn-default{else}btn-primary{/if} btn-sm" value="{$plugin->getID()}">
                                        <i class="fa fa-edit"></i></button>
                                    {/if}
                                </td>
                                <td class="tcenter plugin-config">
                                    {assign var=btnGroup value=false}
                                    {if (isset($plugin->oPluginEinstellung_arr) && $plugin->oPluginEinstellung_arr|@count > 0) || (isset($plugin->oPluginAdminMenu_arr) && $plugin->oPluginAdminMenu_arr|@count > 0) &&
                                    ($plugin->getMeta()->isUpdateAvailable() && $plugin->cFehler === '')}
                                        {assign var=btnGroup value=true}
                                    {/if}
                                    {if $btnGroup}
                                    <div class="btn-group" style="min-width:75px;">
                                        {/if}
                                        {if (isset($plugin->oPluginEinstellung_arr) && $plugin->oPluginEinstellung_arr|@count > 0) || (isset($plugin->oPluginAdminMenu_arr) && $plugin->oPluginAdminMenu_arr|@count > 0)}
                                            <a class="btn btn-default btn-sm" href="plugin.php?kPlugin={$plugin->getID()}" title="Einstellungen"><i class="fa fa-cogs"></i></a>
                                        {else}
                                            {if (isset($plugin->cTextReadmePath) && $plugin->cTextReadmePath|count_characters > 0) || (isset($plugin->cTextLicensePath) && $plugin->cTextLicensePath|count_characters > 0)}
                                                <a class="btn btn-default btn-sm" href="plugin.php?kPlugin={$plugin->getID()}" title="Dokumentation"><i class="fa fa-copy"></i></a>
                                                {*<a class="btn btn-default btn-sm" href="plugin.php?kPlugin={$plugin->getID()}" title="Dokumentation"><i class="fa fa-file-text-o"></i></a>*}
                                            {/if}
                                        {/if}
                                        {if $plugin->getMeta()->isUpdateAvailable() && $plugin->cFehler === ''}
                                            <a onclick="ackCheck({$plugin->getID()});return false;" class="btn btn-success btn-sm" title="{#pluginBtnUpdate#}"><i class="fa fa-refresh"></i></a>
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
                            <td colspan="10"><label for="ALLMSGS1">{#pluginSelectAll#}</label></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="panel-footer">
                    <div class="save btn-group">
                        <button name="deaktivieren" type="submit" class="btn btn-warning"><i class="fa fa-close"></i> {#pluginBtnDeActivate#}</button>
                        <button name="deinstallieren" type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {#pluginBtnDeInstall#}</button>
                    </div>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
    {/if}
</div>