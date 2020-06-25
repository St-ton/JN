<div id="probleme" class="tab-pane fade {if isset($cTab) && $cTab === 'probleme'} active show{/if}">
    {if $pluginsProblematic->count() > 0}
    <form name="pluginverwaltung" method="post" action="pluginverwaltung.php" id="problematic-plugins">
        {$jtl_token}
        <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
        <div>
            <div class="subheading1">{__('pluginListProblems')}</div>
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
                {foreach $pluginsProblematic as $plugin}
                    <tr{if $plugin->isUpdateAvailable()} class="highlight"{/if}>
                        <td class="check">
                            <div class="custom-control custom-checkbox">
                                <input class="custom-control-input" type="checkbox" name="kPlugin[]" value="{$plugin->getID()}" id="plugin-problem-{$plugin->getID()}" />
                                <label class="custom-control-label" for="plugin-problem-{$plugin->getID()}"></label>
                            </div>
                        </td>
                        <td>
                            <label for="plugin-problem-{$plugin->getID()}">{$plugin->getName()}</label>
                            {if $plugin->isUpdateAvailable()}
                                <p>{__('pluginUpdateExists')}</p>
                            {/if}
                        </td>
                        <td class="text-center">
                            <span class="label {if $plugin->getState() === \JTL\Plugin\State::ACTIVATED} text-success
                                    {elseif $plugin->getState() === \JTL\Plugin\State::DISABLED} text-warning
                                    {elseif $plugin->getState() === \JTL\Plugin\State::ERRONEOUS || $plugin->getState() === \JTL\Plugin\State::LICENSE_KEY_INVALID}} text-danger
                                    {elseif $plugin->getState() === \JTL\Plugin\State::UPDATE_FAILED || $plugin->getState() === \JTL\Plugin\State::LICENSE_KEY_MISSING} text-warning{/if}">
                                {$mapper->map($plugin->getState())}
                            </span>
                        </td>
                        <td class="text-center">{(string)$plugin->getVersion()}{if $plugin->isUpdateAvailable()} <span class="error">{(string)$plugin->isUpdateAvailable()}</span>{/if}</td>
                        <td class="text-center">{$plugin->getDateInstalled()->format('d.m.Y H:i')}</td>
                        <td>{$plugin->getDir()}</td>
                        <td class="text-center">
                            {if $plugin->getLangVarCount() > 0}
                                <a href="pluginverwaltung.php?pluginverwaltung_uebersicht=1&sprachvariablen=1&kPlugin={$plugin->getID()}"
                                   class="btn btn-link" title="{__('modify')}" data-toggle="tooltip">
                                    <span class="icon-hover">
                                        <span class="fal fa-edit"></span>
                                        <span class="fas fa-edit"></span>
                                    </span>
                                </a>
                            {/if}
                        </td>
                        <td class="text-center">
                            {if $plugin->getLinkCount() > 0}
                                <a href="links.php?kPlugin={$plugin->getID()}"
                                   class="btn btn-link" title="{__('modify')}" data-toggle="tooltip">
                                    <span class="icon-hover">
                                        <span class="fal fa-edit"></span>
                                        <span class="fas fa-edit"></span>
                                    </span>
                                </a>
                            {/if}
                        </td>
                        <td class="text-center">
                            {if $plugin->hasLicenseCheck()}
                                {if $plugin->getLicenseKey()}
                                    <strong>{__('pluginBtnLicence')}:</strong> {$plugin->getLicenseKey()}
                                    <button name="lizenzkey"
                                            type="submit"
                                            class="btn btn-link"
                                            value="{$plugin->getID()}"
                                            title="{__('edit')}"
                                            data-toggle="tooltip">
                                        <span class="icon-hover">
                                            <span class="fal fa-edit"></span>
                                            <span class="fas fa-edit"></span>
                                        </span> {__('pluginBtnLicenceChange')}
                                    </button>
                                {else}
                                    <button name="lizenzkey" type="submit" class="btn btn-link"
                                            value="{$plugin->getID()}" title="{__('edit')}" data-toggle="tooltip">
                                        <span class="icon-hover">
                                            <span class="fal fa-edit"></span>
                                            <span class="fas fa-edit"></span>
                                        </span> {__('pluginBtnLicenceAdd')}
                                    </button>
                                {/if}
                            {/if}
                        </td>
                        <td class="text-center">
                            {if $plugin->isUpdateAvailable()}
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
                    <div class="col-sm-6 col-xl-auto text-left">
                        <div class="custom-control custom-checkbox">
                            <input class="custom-control-input" name="ALLMSGS" id="ALLMSGS3" type="checkbox" onclick="AllMessages(this.form);" />
                            <label class="custom-control-label" for="ALLMSGS3">{__('selectAll')}</label>
                        </div>
                    </div>
                    <div class="ml-auto col-sm-6 col-xl-auto">
                        <button name="deinstallieren" id="uninstall-problematic-plugin" type="submit" class="btn btn-danger btn-block">
                            <i class="fas fa-trash-alt"></i> {__('pluginBtnDeInstall')}
                        </button>
                    </div>
                    <div class="col-sm-6 col-xl-auto">
                        <button name="deaktivieren" type="submit" class="btn btn-warning btn-block">
                            {__('deactivate')}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <div id="uninstall-problematic-modal" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">{__('deletePluginData')}</h2>
                    <button type="button" class="close" data-dismiss="modal">
                        <i class="fal fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">

                </div>
                <div class="modal-footer">
                    <div class="row">
                        <div class="ml-auto col-sm-6 col-xl-auto submit">
                            <button type="button" class="btn btn-danger btn-bock" name="yes" data-dismiss="modal">
                                <i class="fa fa-close"></i>&nbsp;{__('deletePluginDataYes')}
                            </button>
                        </div> <div class="col-sm-6 col-xl-auto submit">
                            <button type="button" class="btn btn-outline-primary" name="no" data-dismiss="modal">
                                <i class="fa fa-close"></i>&nbsp;{__('deletePluginDataNo')}
                            </button>
                        </div>
                        <div class="col-sm-6 col-xl-auto submit">
                            <button type="button" class="btn btn-primary" name="cancel" data-dismiss="modal">
                                <i class="fal fa-check text-success"></i>&nbsp;{__('cancel')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        {literal}
        $(document).ready(function() {
            var probModal = $('#uninstall-problematic-modal');
            $('#uninstall-problematic-plugin').on('click', function(event) {
                probModal.modal('show');
                return false;
            });
            probModal.on('hide.bs.modal', function(event) {
                if (document.activeElement.name === 'yes' || document.activeElement.name === 'no') {
                    var data = $('#problematic-plugins').serialize();
                    data += '&deinstallieren=1&delete-data=';
                    if (document.activeElement.name === 'yes') {
                        data += '1';
                    } else {
                        data += '0';
                    }
                    simpleAjaxCall('pluginverwaltung.php', data, function () {
                        location.reload();
                    });
                }
            });
        });
        {/literal}
    </script>
    {else}
        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
    {/if}
</div>
