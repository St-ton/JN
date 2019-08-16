<div id="deaktiviert" class="tab-pane fade {if isset($cTab) && $cTab === 'deaktiviert'} active show{/if}">
    {if $pluginsByState.status_1|@count > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
            {$jtl_token}
            <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
            <div>
                <div class="subheading1">{__('pluginListNotActivated')}</div>
                <hr class="mb-3">
                <div class="table-responsive">
                    <table class="table table-striped table-align-top">
                        <thead>
                        <tr>
                            <th></th>
                            <th class="text-left">{__('pluginName')}</th>
                            <th class="text-center">{__('status')}</th>
                            <th class="text-center">{__('pluginVersion')}</th>
                            <th class="text-center">{__('pluginInstalled')}</th>
                            <th>{__('pluginFolder')}</th>
                            <th class="text-center">{__('pluginEditLocales')}</th>
                            <th class="text-center">{__('pluginEditLinkgrps')}</th>
                            <th class="text-center">{__('pluginBtnLicence')}</th>
                            <th class="text-center">&nbsp;</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $pluginsByState.status_1 as $plugin}
                            <tr {if $plugin->getMeta()->isUpdateAvailable()}class="highlight"{/if}>
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
                                    <span class="text-nowrap">
                                        <span class="label {if $plugin->getState() === \JTL\Plugin\State::ACTIVATED} text-success
                                                {elseif $plugin->getState() === \JTL\Plugin\State::DISABLED} text-warning
                                                {elseif $plugin->getState() === \JTL\Plugin\State::ERRONEOUS || $plugin->getState() === \JTL\Plugin\State::LICENSE_KEY_INVALID}} text-danger
                                                {elseif $plugin->getState() === \JTL\Plugin\State::UPDATE_FAILED || $plugin->getState() === \JTL\Plugin\State::LICENSE_KEY_MISSING} text-warning{/if}">
                                            {$mapper->map($plugin->getState())}
                                        </span>
                                        {foreach $allPluginItems as $p}
                                            {if $p->getID() === $plugin->getPluginID()}
                                                {if $p->isShop5Compatible() === false}
                                                    <span title="{__('dangerPluginNotCompatibleShop5')}" class="label text-warning"><i class="fal fa-exclamation-triangle"></i></span>
                                                {elseif $p->isShop5Compatible() === false && $p->isShop4Compatible() === false}
                                                    <span title="{__('dangerPluginNotCompatibleShop4')}" class="label text-warning"><i class="fal fa-exclamation-triangle"></i></span>
                                                {/if}
                                                {break}
                                            {/if}
                                        {/foreach}
                                    </span>
                                </td>
                                <td class="text-center plugin-version">{(string)$plugin->getMeta()->getSemVer()}{if $plugin->getMeta()->isUpdateAvailable()} <span class="label text-success update-info">{(string)$plugin->getCurrentVersion()}</span>{/if}</td>
                                <td class="text-center plugin-install-date">{$plugin->getMeta()->getDateInstalled()->format('d.m.Y H:i')}</td>
                                <td class="plugin-folder">{$plugin->getPaths()->getBaseDir()}</td>
                                <td class="text-center plugin-lang-vars">
                                    {if $plugin->getLocalization()->getTranslations()|@count > 0}
                                        <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$plugin->getID()}"
                                           class="btn btn-link"
                                           title="{__('modify')}"
                                           data-toggle="tooltip">
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
                                           class="btn btn-link"
                                           title="{__('modify')}"
                                           data-toggle="tooltip">
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
                                                class="btn btn-link" value="{$plugin->getID()}" data-toggle="tooltip">
                                            <span class="icon-hover">
                                                <span class="fal fa-edit"></span>
                                                <span class="fas fa-edit"></span>
                                            </span>
                                        </button>
                                    {/if}
                                </td>
                                <td class="text-center">
                                    {if $plugin->getMeta()->isUpdateAvailable()}
                                        <a onclick="ackCheck({$plugin->getID()}, 'deaktiviert'); return false;"
                                           class="btn btn-link"
                                           title="{__('pluginBtnUpdate')}"
                                           data-toggle="tooltip">
                                            <span class="icon-hover">
                                                <span class="fal fa-refresh"></span>
                                                <span class="fas fa-refresh"></span>
                                            </span>
                                        </a>
                                    {/if}
                                </td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                <div class="card-footer save-wrapper save">
                    <div class="row">
                        <div class="col-sm-6 col-xl-auto text-left">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS2" type="checkbox" onclick="AllMessages(this.form);" />
                                <label class="custom-control-label" for="ALLMSGS2">{__('selectAll')}</label>
                            </div>
                        </div>
                        <div class="ml-auto col-sm-6 col-xl-auto">
                            <button name="deinstallieren" type="submit" class="btn btn-danger btn-block">
                                <i class="fas fa-trash-alt"></i> {__('pluginBtnDeInstall')
                                }</button>
                        </div>
                        <div class="col-sm-6 col-xl-auto">
                            <button name="aktivieren" type="submit" class="btn btn-primary btn-block">
                                <i class="fa fa-share"></i> {__('activate')}
                            </button>
                        </div>
                    </div>
                    {*<button name="deaktivieren" type="submit" class="btn btn-warning">{__('deactivate')}</button>*}
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
    {/if}
</div>
