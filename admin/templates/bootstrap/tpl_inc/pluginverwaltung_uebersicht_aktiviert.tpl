<div id="aktiviert" class="tab-pane fade {if !isset($cTab) || $cTab === 'aktiviert'} active show{/if}">
    {if $pluginsByState.status_2|@count > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
            {$jtl_token}
            <input type="hidden" name="{$session_name}" value="{$session_id}" />
            <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
            <div>
                <div class="subheading1">{__('pluginListInstalled')}</div>
                <hr class="mb-3">
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
                                <th>{__('actions')}</th>
                            </tr>
                        </thead>
                        <tbody>
                        {foreach $pluginsByState.status_2 as $plugin}
                            <tr{if $plugin->getMeta()->isUpdateAvailable()} class="highlight"{/if}>
                                <td class="check">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" name="kPlugin[]" id="plugin-check-{$plugin->getID()}" value="{$plugin->getID()}" />
                                        <label class="custom-control-label" for="plugin-check-{$plugin->getID()}"></label>
                                    </div>
                                </td>
                                <td>
                                    <label for="plugin-check-{$plugin->getID()}">{$plugin->getMeta()->getName()}</label>
                                    {if $plugin->getMeta()->isUpdateAvailable() || (isset($plugin->cInfo) && $plugin->cInfo|strlen > 0)}
                                        <p>{__('pluginUpdateExists')}</p>
                                    {/if}
                                </td>
                                <td class="text-center plugin-status">
                                    <h4 class="label-wrap text-nowrap">
                                        <span class="label {if $plugin->getState() === \JTL\Plugin\State::ACTIVATED}success label-success{elseif $plugin->getState() === \JTL\Plugin\State::DISABLED}success label-info{elseif $plugin->getState() === \JTL\Plugin\State::ERRONEOUS}success label-default{elseif $plugin->getState() === \JTL\Plugin\State::UPDATE_FAILED || $plugin->getState() === \JTL\Plugin\State::LICENSE_KEY_MISSING}info label-info{elseif $plugin->getState() == 6}danger label-danger{/if}">
                                            {$mapper->map($plugin->getState())}
                                        </span>
                                        {foreach $allPluginItems as $p}
                                            {if $p->getID() === $plugin->getPluginID()}
                                                {if $p->isShop5Compatible() === false}
                                                    <span title="{__('dangerPluginNotCompatibleShop5')}" class="label warning label-warning"><i class="fal fa-exclamation-triangle"></i></span>
                                                {elseif $p->isShop5Compatible() === false && $p->isShop4Compatible() === false}
                                                    <span title="{__('dangerPluginNotCompatibleShop4')}" class="label warning label-warning"><i class="fal fa-exclamation-triangle"></i></span>
                                                {/if}
                                                {break}
                                            {/if}
                                        {/foreach}
                                    </h4>
                                </td>
                                <td class="text-center plugin-version">
                                    {(string)$plugin->getMeta()->getSemVer()}{if $plugin->getMeta()->isUpdateAvailable()} <span class="badge update-available">{(string)$plugin->getCurrentVersion()}</span>{/if}
                                </td>
                                <td class="text-center plugin-install-date">{$plugin->getMeta()->getDateInstalled()->format('d.m.Y H:i')}</td>
                                <td class="text-center plugin-folder">{$plugin->getPaths()->getBaseDir()}</td>
                                <td class="text-center plugin-lang-vars">
                                    {if $plugin->getLocalization()->getLangVars()->count() > 0}
                                        <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$plugin->getID()}&token={$smarty.session.jtl_token}"
                                           class="btn btn-link" title="{__('modify')}">
                                           <span class="icon-hover">
                                                <span class="fal fa-edit"></span>
                                                <span class="fas fa-edit"></span>
                                            </span>
                                        </a>
                                    {/if}
                                </td>
                                <td class="text-center plugin-frontend-links">
                                    {if $plugin->getLinks()->getLinks()->count() > 0}
                                        <a href="links.php?kPlugin={$plugin->getID()}"
                                           class="btn btn-link" title="{__('modify')}">
                                            <span class="icon-hover">
                                                <span class="fal fa-edit"></span>
                                                <span class="fas fa-edit"></span>
                                            </span>
                                        </a>
                                    {/if}
                                </td>
                                <td class="text-center plugin-license">
                                    {if $plugin->getLicense()->hasLicenseCheck()}
                                        <button name="lizenzkey" type="submit" title="{__('modify')}"
                                                class="btn btn-link" value="{$plugin->getID()}">
                                            <span class="icon-hover">
                                                <span class="fal fa-edit"></span>
                                                <span class="fas fa-edit"></span>
                                            </span>
                                        </button>
                                    {/if}
                                </td>
                                <td class="text-center plugin-config">
                                    {assign var=btnGroup value=false}
                                    {if $plugin->getConfig()->getOptions()->count() > 0
                                        || $plugin->getAdminMenu()->getItems()->count() > 0
                                        || $plugin->getMeta()->isUpdateAvailable()}
                                        {assign var=btnGroup value=true}
                                    {/if}
                                    <div class="btn-group">
                                        {if $plugin->getConfig()->getOptions()->count() || $plugin->getAdminMenu()->getItems()->count()}
                                            <a class="btn btn-link px-1 href="plugin.php?kPlugin={$plugin->getID()}" title="Einstellungen">
                                                <span class="icon-hover">
                                                    <span class="fal fa-cogs"></span>
                                                    <span class="fas fa-cogs"></span>
                                                </span>
                                            </a>
                                        {elseif $plugin->getMeta()->getLicenseMD() || $plugin->getMeta()->getReadmeMD()}
                                            <a class="btn btn-link px-1" href="plugin.php?kPlugin={$plugin->getID()}" title="Dokumentation">
                                                <span class="icon-hover">
                                                    <span class="fal fa-copy"></span>
                                                    <span class="fas fa-copy"></span>
                                                </span>
                                            </a>
                                            {*<a class="btn btn-default btn-sm" href="plugin.php?kPlugin={$plugin->getID()}" title="Dokumentation"><i class="fa fa-file-text-o"></i></a>*}
                                        {/if}
                                        {if $plugin->getMeta()->isUpdateAvailable()}
                                            <a onclick="ackCheck({$plugin->getID()});return false;" class="btn btn-link px-1" title="{__('pluginBtnUpdate')}">
                                                <span class="icon-hover">
                                                    <span class="fal fa-refresh"></span>
                                                    <span class="fas fa-refresh"></span>
                                                </span>
                                            </a>
                                        {/if}
                                    </div>
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                <div class="card-footer save-wrapper save">
                    <div class="row">
                        <div class="col-sm-6 col-xl-auto text-left mb-3">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS1" type="checkbox" onclick="AllMessages(this.form);" />
                                <label class="custom-control-label" for="ALLMSGS1">{__('selectAll')}</label>
                            </div>
                        </div>
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button name="deinstallieren" type="submit" class="btn btn-danger btn-block mb-3">
                                <i class="fas fa-trash-alt"></i> {__('pluginBtnDeInstall')}
                            </button>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <button name="deaktivieren" type="submit" class="btn btn-warning btn-block">
                                <i class="fa fa-close"></i> {__('deactivate')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
    {/if}
</div>
