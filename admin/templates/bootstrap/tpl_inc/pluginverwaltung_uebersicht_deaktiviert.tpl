<div id="deaktiviert" class="tab-pane fade {if isset($cTab) && $cTab === 'deaktiviert'} active in{/if}">
    {if $pluginsByState.status_1|@count > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
            {$jtl_token}
            <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{__('pluginListNotActivated')}</h3>
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
                            <th>&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $pluginsByState.status_1 as $plugin}
                            <tr {if $plugin->getMeta()->isUpdateAvailable()}class="highlight"{/if}>
                                <td class="check">
                                    <input type="checkbox" name="kPlugin[]" id="plugin-check-{$plugin->getID()}" value="{$plugin->getID()}" />
                                </td>
                                <td>
                                    <label for="plugin-check-{$plugin->getID()}">{$plugin->getMeta()->getName()}</label
                                    {if $plugin->getMeta()->isUpdateAvailable() || (isset($plugin->cInfo) && $plugin->cInfo|strlen > 0)}
                                        <p>{__('pluginUpdateExists')}</p>
                                    {/if}
                                </td>
                                <td class="tcenter plugin-status">
                                    <h4 class="label-wrap text-nowrap">
                                        <span class="label {if $plugin->getState() === \JTL\Plugin\State::ACTIVATED}success label-success{elseif $plugin->getState() === \JTL\Plugin\State::DISABLED}success label-info{elseif $plugin->getState() === \JTL\Plugin\State::ERRONEOUS}success label-default{elseif $plugin->getState() === \JTL\Plugin\State::UPDATE_FAILED || $plugin->getState() === \JTL\Plugin\State::LICENSE_KEY_MISSING}info label-info{elseif $plugin->getState() === \JTL\Plugin\State::LICENSE_KEY_INVALID}danger label-danger{/if}">
                                            {$mapper->map($plugin->getState())}
                                        </span>
                                        {foreach $allPluginItems as $p}
                                            {if $p->getID() === $plugin->getPluginID()}
                                                {if $p->isShop5Compatible() === false}
                                                    <span title="{__('dangerPluginNotCompatible')}" class="label warning label-warning"><i class="fa fa-warning"></i></span>
                                                {elseif $p->isShop5Compatible() === false && $p->isShop4Compatible() === false}
                                                    <span title="{__('dangerPluginNotCompatible')}" class="label warning label-warning"><i class="fa fa-warning"></i></span>
                                                {/if}
                                                {break}
                                            {/if}
                                        {/foreach}
                                    </h4>
                                </td>
                                <td class="tcenter plugin-version">{number_format($plugin->getMeta()->getVersion() / 100, 2)}{if $plugin->getMeta()->isUpdateAvailable()} <span class="label label-success update-info">{number_format((float)$plugin->getCurrentVersion() / 100, 2)}</span>{/if}</td>
                                <td class="tcenter plugin-install-date">{$plugin->getMeta()->getDateInstalled()->format('d.m.Y H:i')}</td>
                                <td class="tcenter plugin-folder">{$plugin->getPaths()->getBaseDir()}</td>
                                <td class="tcenter plugin-lang-vars">
                                    {if $plugin->getLocalization()->getTranslations()|@count > 0}
                                        <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$plugin->getID()}"
                                           class="btn btn-default btn-sm" title="{__('modify')}"><i class="fa fa-edit"></i></a>
                                    {/if}
                                </td>
                                <td class="tcenter plugin-frontend-links">
                                    {if $plugin->getLinks()->getLinks()->count() > 0}
                                        <a href="links.php?kPlugin={$plugin->getID()}" class="btn btn-default btn-sm" title="{__('modify')}"><i class="fa fa-edit"></i></a>
                                    {/if}
                                </td>
                                <td class="tcenter plugin-license">
                                    {if $plugin->getLicense()->hasLicenseCheck()}
                                        <button name="lizenzkey" type="submit" title="{__('modify')}"
                                                class="btn {if $plugin->getLicense()->hasLicense()}btn-default{else}btn-primary{/if} btn-sm" value="{$plugin->getID()}">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                    {/if}
                                </td>
                                <td class="tcenter">
                                    {if $plugin->getMeta()->isUpdateAvailable()}
                                        <a onclick="ackCheck({$plugin->getID()}, 'deaktiviert'); return false;" class="btn btn-primary btn-sm" title="{__('pluginBtnUpdate')}"><i class="fa fa-refresh"></i></a>
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                        <tfoot>
                        <tr>
                            <td class="check"><input name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);" /></td>
                            <td colspan="10"><label for="ALLMSGS2">{__('selectAll')}</label></td>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="panel-footer">
                    <div class="save btn-group">
                        <button name="aktivieren" type="submit" class="btn btn-primary"><i class="fa fa-share"></i> {__('activate')}</button>
                        {*<button name="deaktivieren" type="submit" class="btn btn-warning">{__('deactivate')}</button>*}
                        <button name="deinstallieren" type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {__('pluginBtnDeInstall')}</button>
                    </div>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
    {/if}
</div>