<div id="probleme" class="tab-pane fade {if isset($cTab) && $cTab === 'probleme'} active show{/if}">
    {if $PluginErrorCount > 0}
    <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
        {$jtl_token}
        <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
        <div>
            <div class="subheading1">{__('pluginListProblems')}</div>
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
                        <th>&nbsp;</th>
                    </tr>
                </thead>
                <tbody>
                {foreach $pluginsByState.status_3 as $plugin}
                    <tr{if $plugin->getMeta()->isUpdateAvailable()} class="highlight"{/if}>
                        <td class="check">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="kPlugin[]" value="{$plugin->getID()}" id="plugin-problem-{$plugin->getID()}" />
                                <label class="custom-control-label" for="plugin-problem-{$plugin->getID()}"></label>
                            </div>
                        </td>
                        <td>
                            <label for="plugin-problem-{$plugin->getID()}">{$plugin->getMeta()->getName()}</label>
                            {if $plugin->getMeta()->isUpdateAvailable()}
                                <p>{__('pluginUpdateExists')}</p>
                            {/if}
                        </td>
                        <td class="tcenter">
                            <h4 class="label-wrap">
                                <span class="label {if $plugin->getState() === \JTL\Plugin\State::ACTIVATED}success label-success{elseif $plugin->getState() === \JTL\Plugin\State::DISABLED}success label-info{elseif $plugin->getState() === \JTL\Plugin\State::ERRONEOUS}success label-default{elseif $plugin->getState() === \JTL\Plugin\State::UPDATE_FAILED || $plugin->getState() === \JTL\Plugin\State::LICENSE_KEY_MISSING}info label-info{elseif $plugin->getState() === \JTL\Plugin\State::LICENSE_KEY_INVALID}danger label-danger{/if}">
                                    {$mapper->map($plugin->getState())}
                                </span>
                            </h4>
                        </td>
                        <td class="tcenter">{(string)$plugin->getMeta()->getSemVer()}{if $plugin->getMeta()->isUpdateAvailable()} <span class="error">{(string)$plugin->getCurrentVersion()}</span>{/if}</td>
                        <td class="tcenter">{$plugin->getDateInstalled()->format('d.m.Y H:i')}</td>
                        <td class="tcenter">{$plugin->getPaths()->getBaseDir()}</td>
                        <td class="tcenter">
                            {if $plugin->getLocalization()->getTranslations()|@count > 0}
                                <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$plugin->getID()}"
                                   class="btn btn-default" title="{__('modify')}">
                                    <i class="fal fa-edit"></i></a>
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getLinks()->getLinks()->count() > 0}
                                <a href="links.php?kPlugin={$plugin->getID()}"
                                   class="btn btn-default" title="{__('modify')}"><i class="fal fa-edit"></i></a>
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getLicense()->hasLicenseCheck()}
                                {if $plugin->getLicense()->hasLicense()}
                                    <strong>{__('pluginBtnLicence')}:</strong> {$plugin->getLicense()->getKey()}
                                    <button name="lizenzkey" type="submit" class="btn btn-default" value="{$plugin->getID()}">
                                        <i class="fal fa-edit"></i> {__('pluginBtnLicenceChange')}</button>
                                {else}
                                    <button name="lizenzkey" type="submit" class="btn btn-primary" value="{$plugin->getID()}">
                                        <i class="fal fa-edit"></i> {__('pluginBtnLicenceAdd')}</button>
                                {/if}
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getMeta()->isUpdateAvailable()}
                                <a onclick="ackCheck({$plugin->getID()}, '#probleme'); return false;" class="btn btn-primary">{__('pluginBtnUpdate')}</a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                {foreach $pluginsByState.status_4 as $plugin}
                    <tr{if $plugin->getMeta()->isUpdateAvailable()} class="highlight"{/if}>
                        <td class="check">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="kPlugin[]" value="{$plugin->getID()}" id="plugin-problem-{$plugin->getID()}" />
                                <label class="custom-control-label" for="plugin-problem-{$plugin->getID()}"></label>
                            </div>
                        </td>
                        <td>
                            <label for="plugin-problem-{$plugin->getID()}">{$plugin->getMeta()->getName()}</label>
                            {if $plugin->getMeta()->isUpdateAvailable()}
                                <p>{__('pluginUpdateExists')}</p>
                            {/if}
                        </td>
                        <td class="tcenter">
                            <h4 class="label-wrap">
                            <span class="label {if $plugin->getState() === \JTL\Plugin\State::ACTIVATED}success label-success{elseif $plugin->getState() === \JTL\Plugin\State::DISABLED}success label-info{elseif $plugin->getState() === \JTL\Plugin\State::ERRONEOUS}success label-default{elseif $plugin->getState() === \JTL\Plugin\State::UPDATE_FAILED || $plugin->getState() === \JTL\Plugin\State::LICENSE_KEY_MISSING}info label-info{elseif $plugin->getState() === \JTL\Plugin\State::LICENSE_KEY_INVALID}danger label-danger{/if}">
                                {$mapper->map($plugin->getState())}
                            </span>
                            </h4>
                        </td>
                        <td class="tcenter">{(string)$plugin->getMeta()->getSemVer()}{if $plugin->getMeta()->isUpdateAvailable()} <span class="error">{(string)$plugin->getCurrentVersion()}</span>{/if}</td>
                        <td class="tcenter">{$plugin->getMeta()->getDateInstalled()->format('d.m.Y H:i')}</td>
                        <td class="tcenter">{$plugin->getPaths()->getBaseDir()}</td>
                        <td class="tcenter">
                            {if $plugin->getLocalization()->getTranslations()|@count > 0}
                                <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$plugin->getID()}" class="btn btn-default">{__('modify')}</a>
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getLinks()->getLinks()->count() > 0}
                                <a href="links.php?kPlugin={$plugin->getID()}"
                                   class="btn btn-default" title="{__('modify')}">
                                    <i class="fal fa-edit"></i>
                                </a>
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getLicense()->hasLicenseCheck()}
                                {if $plugin->getLicense()->hasLicense()}
                                    {$plugin->getLicense()->getKey()|truncate:35:'...':true}
                                    <button name="lizenzkey" type="submit" class="btn btn-default" value="{$plugin->getID()}">
                                        <i class="fal fa-edit"></i> {__('pluginBtnLicenceChange')}
                                    </button>
                                {else}
                                    <button name="lizenzkey" type="submit" class="btn btn-primary" value="{$plugin->getID()}">
                                        <i class="fal fa-edit"></i> {__('pluginBtnLicenceAdd')}
                                    </button>
                                {/if}
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getMeta()->isUpdateAvailable()}
                                <a onclick="ackCheck({$plugin->getID()}, '#probleme'); return false;" class="btn btn-primary">{__('pluginBtnUpdate')}</a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                {foreach $pluginsByState.status_5 as $plugin}
                    <tr{if $plugin->getMeta()->isUpdateAvailable()} class="highlight"{/if}>
                        <td class="check">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="kPlugin[]" value="{$plugin->getID()}" id="plugin-problem-{$plugin->getID()}"/>
                                <label class="custom-control-label" for="plugin-problem-{$plugin->getID()}"></label>
                            </div>
                        </td>
                        <td>
                            <label for="plugin-problem-{$plugin->getID()}">{$plugin->getMeta()->getName()}</label>
                            {if $plugin->getMeta()->isUpdateAvailable()}
                                <p>{__('pluginUpdateExists')}</p>
                            {/if}
                        </td>
                        <td class="tcenter">
                            <h4 class="label-wrap">
                                <span class="label {if $plugin->getState() === \JTL\Plugin\State::ACTIVATED}success label-success{elseif $plugin->getState() === \JTL\Plugin\State::DISABLED}success label-info{elseif $plugin->getState() === \JTL\Plugin\State::ERRONEOUS}success label-default{elseif $plugin->getState() === \JTL\Plugin\State::UPDATE_FAILED || $plugin->getState() === \JTL\Plugin\State::LICENSE_KEY_MISSING}info label-info{elseif $plugin->getState() === \JTL\Plugin\State::LICENSE_KEY_INVALID}danger label-danger{/if}">
                                    {$mapper->map($plugin->getState())}
                                </span>
                            </h4>
                        </td>
                        <td class="tcenter">{(string)$plugin->getMeta()->getSemVer()}{if $plugin->getMeta()->isUpdateAvailable()} <span class="error">{(string)$plugin->getCurrentVersion()}</span>{/if}</td>
                        <td class="tcenter">{$plugin->getMeta()->getDateInstalled()->format('d.m.Y H:i')}</td>
                        <td class="tcenter">{$plugin->getPaths()->getBaseDir()}</td>
                        <td class="tcenter">
                            {if $plugin->getLocalization()->getTranslations()|@count > 0}
                                <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$plugin->getID()}"
                                   class="btn btn-default" title="{__('modify')}">
                                    <i class="fal fa-edit"></i>
                                </a>
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getLinks()->getLinks()->count() > 0}
                                <a href="links.php?kPlugin={$plugin->getID()}" class="btn btn-default" title="{__('modify')}">
                                    <i class="fal fa-edit"></i>
                                </a>
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getLicense()->hasLicenseCheck()}
                                {if $plugin->getLicense()->hasLicense()}
                                    <strong>{__('pluginBtnLicence')}:</strong> {$plugin->getLicense()->getKey()}
                                    <button name="lizenzkey" type="submit" class="btn btn-default"
                                            value="{$plugin->getID()}">
                                        <i class="fal fa-edit"></i> {__('pluginBtnLicenceChange')}
                                    </button>
                                {else}
                                    <button name="lizenzkey" type="submit" class="btn btn-primary"
                                            value="{$plugin->getID()}">
                                        <i class="fal fa-edit"></i> {__('pluginBtnLicenceAdd')}
                                    </button>
                                {/if}
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getMeta()->isUpdateAvailable()}
                                <a onclick="ackCheck({$plugin->getID()}, '#probleme'); return false;" class="btn btn-primary">{__('pluginBtnUpdate')}</a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                {foreach $pluginsByState.status_6 as $plugin}
                    <tr{if $plugin->getMeta()->isUpdateAvailable()} class="highlight"{/if}>
                        <td class="check">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="kPlugin[]" value="{$plugin->getID()}" id="plugin-problem-{$plugin->getID()}" />
                                <label class="custom-control-label" for="plugin-problem-{$plugin->getID()}"></label>
                            </div>
                        </td>
                        <td>
                            <label for="plugin-problem-{$plugin->getID()}">{$plugin->getMeta()->getName()}</label>
                            {if $plugin->getMeta()->isUpdateAvailable()}
                                <p>{__('pluginUpdateExists')}</p>
                            {/if}
                        </td>
                        <td class="tcenter plugin-status">
                            <h4 class="label-wrap">
                                <span class="label {if $plugin->getState() === \JTL\Plugin\State::ACTIVATED}success label-success{elseif $plugin->getState() === \JTL\Plugin\State::DISABLED}success label-info{elseif $plugin->getState() === \JTL\Plugin\State::ERRONEOUS}success label-default{elseif $plugin->getState() === \JTL\Plugin\State::UPDATE_FAILED || $plugin->getState() === \JTL\Plugin\State::LICENSE_KEY_MISSING}info label-info{elseif $plugin->getState() === \JTL\Plugin\State::LICENSE_KEY_INVALID}danger label-danger{/if}">
                                    {$mapper->map($plugin->getState())}
                                </span>
                            </h4>
                        </td>
                        <td class="tcenter plugin-version">{(string)$plugin->getMeta()->getSemVer()}{if $plugin->getMeta()->isUpdateAvailable() } <span class="label label-danger error">{(string)$plugin->getCurrentVersion()}</span>{/if}</td>
                        <td class="tcenter plugin-install-date">{$plugin->getMeta()->getDateInstalled()->format('d.m.Y H:i')}</td>
                        <td class="tcenter plugin-folder">{$plugin->getPaths()->getBaseDir()}</td>
                        <td class="tcenter plugin-lang-vars">
                            {if $plugin->getLocalization()->getTranslations()|@count > 0}
                                <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$plugin->getID()}"
                                   class="btn btn-default" title="{__('modify')}">
                                    <i class="fal fa-edit"></i>
                                </a>
                            {/if}
                        </td>
                        <td class="tcenter plugin-frontend-links">
                            {if $plugin->getLinks()->getLinks()->count() > 0}
                                <a href="links.php?kPlugin={$plugin->getID()}" class="btn btn-default" title="{__('modify')}">
                                    <i class="fal fa-edit"></i>
                                </a>
                            {/if}
                        </td>
                        <td class="tcenter plugin-license">
                            {if $plugin->getLicense()->hasLicenseCheck()}
                                {if $plugin->getLicense()->hasLicense()}
                                    <strong>{__('pluginBtnLicence')}:</strong> {$plugin->getLicense()->getKey()}
                                    <button name="lizenzkey" type="submit" class="btn btn-default"
                                            value="{$plugin->getID()}" title="{__('modify')}">
                                        <i class="fal fa-edit"></i></button>
                                {else}
                                    <button name="lizenzkey" type="submit" class="btn btn-primary"
                                            value="{$plugin->getID()}" title="{__('modify')}">
                                        <i class="fal fa-edit"></i></button>
                                {/if}
                            {/if}
                        </td>
                        <td class="tcenter">
                            {if $plugin->getMeta()->isUpdateAvailable()}
                                <a onclick="ackCheck({$plugin->getID()}, '#probleme'); return false;" class="btn btn-primary">{__('pluginBtnUpdate')}</a>
                            {/if}
                        </td>
                    </tr>
                {/foreach}
                </tbody>
                </table>
            </div>
            <div class="card-footer save-wrapper save">
                <div class="row">
                    <div class="col-sm-6 col-xl-auto text-left mb-3">
                        div class="custom-control custom-checkbox">
                        <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS3" type="checkbox" onclick="AllMessages(this.form);" />
                        <label class="custom-control-label" for="ALLMSGS3">{__('selectAll')}</label>
                    </div>
                    </div>
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <button name="deinstallieren" type="submit" class="btn btn-danger btn-block mb-3">
                            <i class="fas fa-trash-alt"></i> {__('pluginBtnDeInstall')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button name="deaktivieren" type="submit" class="btn btn-warning btn-block">
                            {__('deactivate')}
                        </button>
                    </div>
                </div>
                {*<button name="aktivieren" type="submit" class="btn btn-primary">{__('activate')}</button>*}
            </div>
        </div>
    </form>
    {else}
        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
    {/if}
</div>
