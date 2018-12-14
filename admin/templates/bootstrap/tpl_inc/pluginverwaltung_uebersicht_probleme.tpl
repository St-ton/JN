<div id="probleme" class="tab-pane fade {if isset($cTab) && $cTab === 'probleme'} active in{/if}">
    {if $PluginErrorCount > 0}
    <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
        {$jtl_token}
        <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">{#pluginListProblems#}</h3>
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
                {foreach $pluginsByState.status_3 as $plugin}
                    <tr{if $plugin->getMeta()->isUpdateAvailable()} class="highlight"{/if}>
                        <td class="check">
                            <input type="checkbox" name="kPlugin[]" value="{$plugin->getID()}" id="plugin-problem-{$plugin->getID()}" />
                        </td>
                        <td>
                            <label for="plugin-problem-{$plugin->getID()}">{$plugin->getMeta()->getName()}</label>
                            {if $plugin->getMeta()->isUpdateAvailable()}
                                <p>{#pluginUpdateExists#}</p>
                            {/if}
                        </td>
                        <td class="tcenter">
                            <h4 class="label-wrap">
                                <span class="label {if $plugin->getState() === \Plugin\State::ACTIVATED}success label-success{elseif $plugin->getState() === \Plugin\State::DISABLED}success label-info{elseif $plugin->getState() === \Plugin\State::ERRONEOUS}success label-default{elseif $plugin->getState() === \Plugin\State::UPDATE_FAILED || $plugin->getState() === \Plugin\State::LICENSE_KEY_MISSING}info label-info{elseif $plugin->getState() === \Plugin\State::LICENSE_KEY_INVALID}danger label-danger{/if}">
                                    {$mapper->map($plugin->getState())}
                                </span>
                            </h4>
                        </td>
                        <td class="tcenter">{number_format($plugin->getMeta()->getVersion() / 100, 2)}{if $plugin->getMeta()->isUpdateAvailable()} <span class="error">{number_format((float)$plugin->getCurrentVersion() / 100, 2)}</span>{/if}</td>
                        <td class="tcenter">{$plugin->getDateInstalled()->format('d.m.Y H:i')}</td>
                        <td class="tcenter">{$plugin->getPaths()->getBaseDir()}</td>
                        <td class="tcenter">
                            {if isset($plugin->oPluginSprachvariableAssoc_arr) && $plugin->oPluginSprachvariableAssoc_arr|@count > 0}
                                <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$plugin->getID()}"
                                   class="btn btn-default" title="{#modify#}">
                                    <i class="fa fa-edit"></i></a>
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getLinks()->getLinks()->count() > 0}
                                <a href="links.php?kPlugin={$plugin->getID()}"
                                   class="btn btn-default" title="{#modify#}"><i class="fa fa-edit"></i></a>
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getLicense()->hasLicenseCheck()}
                                {if $plugin->getLicense()->hasLicense()}
                                    <strong>{#pluginBtnLicence#}:</strong> {$plugin->getLicense()->getKey()}
                                    <button name="lizenzkey" type="submit" class="btn btn-default" value="{$plugin->getID()}">
                                        <i class="fa fa-edit"></i> {#pluginBtnLicenceChange#}</button>
                                {else}
                                    <button name="lizenzkey" type="submit" class="btn btn-primary" value="{$plugin->getID()}">
                                        <i class="fa fa-edit"></i> {#pluginBtnLicenceAdd#}</button>
                                {/if}
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getMeta()->isUpdateAvailable()}
                                <a onclick="ackCheck({$plugin->getID()}, '#probleme'); return false;" class="btn btn-primary">{#pluginBtnUpdate#}</a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                {foreach $pluginsByState.status_4 as $plugin}
                    <tr{if $plugin->getMeta()->isUpdateAvailable()} class="highlight"{/if}>
                        <td class="check">
                            <input type="checkbox" name="kPlugin[]" value="{$plugin->getID()}" id="plugin-problem-{$plugin->getID()}" />
                        </td>
                        <td>
                            <label for="plugin-problem-{$plugin->getID()}">{$plugin->getMeta()->getName()}</label>
                            {if $plugin->getMeta()->isUpdateAvailable()}
                                <p>{#pluginUpdateExists#}</p>
                            {/if}
                        </td>
                        <td class="tcenter">
                            <h4 class="label-wrap">
                            <span class="label {if $plugin->getState() === \Plugin\State::ACTIVATED}success label-success{elseif $plugin->getState() === \Plugin\State::DISABLED}success label-info{elseif $plugin->getState() === \Plugin\State::ERRONEOUS}success label-default{elseif $plugin->getState() === \Plugin\State::UPDATE_FAILED || $plugin->getState() === \Plugin\State::LICENSE_KEY_MISSING}info label-info{elseif $plugin->getState() === \Plugin\State::LICENSE_KEY_INVALID}danger label-danger{/if}">
                                {$mapper->map($plugin->getState())}
                            </span>
                            </h4>
                        </td>
                        <td class="tcenter">{number_format($plugin->getMeta()->getVersion() / 100, 2)}{if $plugin->getMeta()->isUpdateAvailable()} <span class="error">{number_format((float)$plugin->getCurrentVersion() / 100, 2)}</span>{/if}</td>
                        <td class="tcenter">{$plugin->getMeta()->getDateInstalled()->format('d.m.Y H:i')}</td>
                        <td class="tcenter">{$plugin->getPaths()->getBaseDir()}</td>
                        <td class="tcenter">
                            {if isset($plugin->oPluginSprachvariableAssoc_arr) && $plugin->oPluginSprachvariableAssoc_arr|@count > 0}
                                <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$plugin->getID()}" class="btn btn-default">{#modify#}</a>
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getLinks()->getLinks()->count() > 0}
                                <a href="links.php?kPlugin={$plugin->getID()}"
                                   class="btn btn-default" title="{#modify#}">
                                    <i class="fa fa-edit"></i>
                                </a>
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getLicense()->hasLicenseCheck()}
                                {if $plugin->getLicense()->hasLicense()}
                                    {$plugin->getLicense()->getKey()|truncate:35:'...':true}
                                    <button name="lizenzkey" type="submit" class="btn btn-default" value="{$plugin->getID()}">
                                        <i class="fa fa-edit"></i> {#pluginBtnLicenceChange#}
                                    </button>
                                {else}
                                    <button name="lizenzkey" type="submit" class="btn btn-primary" value="{$plugin->getID()}">
                                        <i class="fa fa-edit"></i> {#pluginBtnLicenceAdd#}
                                    </button>
                                {/if}
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getMeta()->isUpdateAvailable()}
                                <a onclick="ackCheck({$plugin->getID()}, '#probleme'); return false;" class="btn btn-primary">{#pluginBtnUpdate#}</a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                {foreach $pluginsByState.status_5 as $plugin}
                    <tr{if $plugin->getMeta()->isUpdateAvailable()} class="highlight"{/if}>
                        <td class="check">
                            <input type="checkbox" name="kPlugin[]" value="{$plugin->getID()}" id="plugin-problem-{$plugin->getID()}"/>
                        </td>
                        <td>
                            <label for="plugin-problem-{$plugin->getID()}">{$plugin->getMeta()->getName()}</label>
                            {if $plugin->getMeta()->isUpdateAvailable()}
                                <p>{#pluginUpdateExists#}</p>
                            {/if}
                        </td>
                        <td class="tcenter">
                            <h4 class="label-wrap">
                                <span class="label {if $plugin->getState() === \Plugin\State::ACTIVATED}success label-success{elseif $plugin->getState() === \Plugin\State::DISABLED}success label-info{elseif $plugin->getState() === \Plugin\State::ERRONEOUS}success label-default{elseif $plugin->getState() === \Plugin\State::UPDATE_FAILED || $plugin->getState() === \Plugin\State::LICENSE_KEY_MISSING}info label-info{elseif $plugin->getState() === \Plugin\State::LICENSE_KEY_INVALID}danger label-danger{/if}">
                                    {$mapper->map($plugin->getState())}
                                </span>
                            </h4>
                        </td>
                        <td class="tcenter">{number_format($plugin->getMeta()->getVersion() / 100, 2)}{if $plugin->getMeta()->isUpdateAvailable()} <span class="error">{number_format((float)$plugin->getCurrentVersion() / 100, 2)}</span>{/if}</td>
                        <td class="tcenter">{$plugin->getMeta()->getDateInstalled()->format('d.m.Y H:i')}</td>
                        <td class="tcenter">{$plugin->getPaths()->getBaseDir()}</td>
                        <td class="tcenter">
                            {if isset($plugin->oPluginSprachvariableAssoc_arr) && $plugin->oPluginSprachvariableAssoc_arr|@count > 0}
                                <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$plugin->getID()}"
                                   class="btn btn-default" title="{#modify#}">
                                    <i class="fa fa-edit"></i>
                                </a>
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getLinks()->getLinks()->count() > 0}
                                <a href="links.php?kPlugin={$plugin->getID()}" class="btn btn-default" title="{#modify#}">
                                    <i class="fa fa-edit"></i>
                                </a>
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getLicense()->hasLicenseCheck()}
                                {if $plugin->getLicense()->hasLicense()}
                                    <strong>{#pluginBtnLicence#}:</strong> {$plugin->getLicense()->getKey()}
                                    <button name="lizenzkey" type="submit" class="btn btn-default"
                                            value="{$plugin->getID()}">
                                        <i class="fa fa-edit"></i> {#pluginBtnLicenceChange#}
                                    </button>
                                {else}
                                    <button name="lizenzkey" type="submit" class="btn btn-primary"
                                            value="{$plugin->getID()}">
                                        <i class="fa fa-edit"></i> {#pluginBtnLicenceAdd#}
                                    </button>
                                {/if}
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getMeta()->isUpdateAvailable()}
                                <a onclick="ackCheck({$plugin->getID()}, '#probleme'); return false;" class="btn btn-primary">{#pluginBtnUpdate#}</a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                {foreach $pluginsByState.status_6 as $plugin}
                    <tr{if $plugin->getMeta()->isUpdateAvailable()} class="highlight"{/if}>
                        <td class="check">
                            <input type="checkbox" name="kPlugin[]" value="{$plugin->getID()}" id="plugin-problem-{$plugin->getID()}" />
                        </td>
                        <td>
                            <label for="plugin-problem-{$plugin->getID()}">{$plugin->getMeta()->getName()}</label>
                            {if $plugin->getMeta()->isUpdateAvailable()}
                                <p>{#pluginUpdateExists#}</p>
                            {/if}
                        </td>
                        <td class="tcenter plugin-status">
                            <h4 class="label-wrap">
                                <span class="label {if $plugin->getState() === \Plugin\State::ACTIVATED}success label-success{elseif $plugin->getState() === \Plugin\State::DISABLED}success label-info{elseif $plugin->getState() === \Plugin\State::ERRONEOUS}success label-default{elseif $plugin->getState() === \Plugin\State::UPDATE_FAILED || $plugin->getState() === \Plugin\State::LICENSE_KEY_MISSING}info label-info{elseif $plugin->getState() === \Plugin\State::LICENSE_KEY_INVALID}danger label-danger{/if}">
                                    {$mapper->map($plugin->getState())}
                                </span>
                            </h4>
                        </td>
                        <td class="tcenter plugin-version">{number_format($plugin->getMeta()->getVersion() / 100, 2)}{if $plugin->getMeta()->isUpdateAvailable() } <span class="label label-danger error">{number_format((float)$plugin->getCurrentVersion() / 100, 2)}</span>{/if}</td>
                        <td class="tcenter plugin-install-date">{$plugin->getMeta()->getDateInstalled()->format('d.m.Y H:i')}</td>
                        <td class="tcenter plugin-folder">{$plugin->getPaths()->getBaseDir()}</td>
                        <td class="tcenter plugin-lang-vars">
                            {if isset($plugin->oPluginSprachvariableAssoc_arr) && $plugin->oPluginSprachvariableAssoc_arr|@count > 0}
                                <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$plugin->getID()}"
                                   class="btn btn-default" title="{#modify#}">
                                    <i class="fa fa-edit"></i>
                                </a>
                            {/if}
                        </td>
                        <td class="tcenter plugin-frontend-links">
                            {if $plugin->getLinks()->getLinks()->count() > 0}
                                <a href="links.php?kPlugin={$plugin->getID()}" class="btn btn-default" title="{#modify#}">
                                    <i class="fa fa-edit"></i>
                                </a>
                            {/if}
                        </td>
                        <td class="tcenter plugin-license">
                            {if $plugin->getLicense()->hasLicenseCheck()}
                                {if $plugin->getLicense()->hasLicense()}
                                    <strong>{#pluginBtnLicence#}:</strong> {$plugin->getLicense()->getKey()}
                                    <button name="lizenzkey" type="submit" class="btn btn-default"
                                            value="{$plugin->getID()}" title="{#modify#}">
                                        <i class="fa fa-edit"></i></button>
                                {else}
                                    <button name="lizenzkey" type="submit" class="btn btn-primary"
                                            value="{$plugin->getID()}" title="{#modify#}">
                                        <i class="fa fa-edit"></i></button>
                                {/if}
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getMeta()->isUpdateAvailable()}
                                <a onclick="ackCheck({$plugin->getID()}, '#probleme'); return false;" class="btn btn-primary">{#pluginBtnUpdate#}</a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
                <tfoot>
                <tr>
                    <td class="check"><input name="ALLMSGS" id="ALLMSGS3" type="checkbox" onclick="AllMessages(this.form);" /></td>
                    <td colspan="10"><label for="ALLMSGS3">{#pluginSelectAll#}</label></td>
                </tr>
                </tfoot>
                </table>
            </div>
            <div class="panel-footer">
                <div class="save btn-group">
                    {*<button name="aktivieren" type="submit" class="btn btn-primary">{#pluginBtnActivate#}</button>*}
                    <button name="deaktivieren" type="submit" class="btn btn-warning">{#pluginBtnDeActivate#}</button>
                    <button name="deinstallieren" type="submit" class="btn btn-danger"><i class="fa fa-trash"></i> {#pluginBtnDeInstall#}</button>
                </div>
            </div>
        </div>
    </form>
    {else}
        <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
    {/if}
</div>