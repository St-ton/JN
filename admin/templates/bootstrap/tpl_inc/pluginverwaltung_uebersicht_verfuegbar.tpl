<script>
    // transfer our licenses(-json-object) from php into js
    var vLicenses = {if isset($szLicenses)}{$szLicenses}{else}[]{/if};
//{literal}

    $(document).ready(function() {
        token = $('input[name="jtl_token"]').val();

        // for all found licenses..
        for (var key in vLicenses) {
            // ..bind a click-handler to the plugins checkbox
            $('input[id="plugin-check-'+key+'"]').on('click', function(event) {
                // grab the element, which was rising that click-event (click to the checkbox)
                var oTemp = $(event.currentTarget);
                szPluginName = oTemp.val();

                if (this.checked) { // it's checked yet, right after the click was fired
                    $('input[id="plugin-check-'+szPluginName+'"]').attr('disabled', 'disabled'); // block the checkbox!
                    $('div[id="licenseModal"]').modal({backdrop : 'static'}); // set our modal static (a click in black did not hide it!)
                    $('div[id="licenseModal"]').find('.modal-body').load('getMarkdownAsHTML.php', {'jtl_token':token, 'path':vLicenses[szPluginName]});
                    $('div[id="licenseModal"]').modal('show');
                }
            });
        }

        // handle the (befor-)hiding of the modal and what's happening during it occurs
        $('div[id="licenseModal"]').on('hide.bs.modal', function(event) {
            // IMPORTANT: release the checkbox on modal-close again too!
            $('input[id=plugin-check-'+szPluginName+']').removeAttr('disabled');

            // check, which element is 'active' before/during the modal goes hiding (to determine, which button closes it)
            // (it is faster than check a var or bind an event to an element)
            if ('ok' === document.activeElement.name) {
                $('input[id=plugin-check-'+szPluginName+']').prop('checked', true);
            } else {
                $('input[id=plugin-check-'+szPluginName+']').prop('checked', false);
            }
        });
    });
</script>
{/literal}

<div id="verfuegbar" class="tab-pane fade {if isset($cTab) && $cTab === 'verfuegbar'} active show{/if}">
    {if $pluginsAvailable->count() > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
            {$jtl_token}
            <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
            <div>
                <div class="subheading1">{__('pluginListNotInstalled')}</div>
                <hr class="mb-n3">
                <div class="table-responsive">
                    <!-- license-modal definition -->
                    <div id="licenseModal" class="modal fade" role="dialog">
                        <div class="modal-dialog modal-lg">

                            <div class="modal-content">
                                <div class="modal-header">
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                    <h4 class="modal-title">{__('licensePlugin')}</h4>
                                </div>
                                <div class="modal-body">
                                    {* license.md content goes here via js *}
                                </div>
                                <div class="modal-footer">
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-success" name="ok" data-dismiss="modal"><i class="fal fa-check text-success"></i>&nbsp;{__('ok')}</button>
                                        <button type="button" class="btn btn-danger" name="cancel" data-dismiss="modal"><i class="fa fa-close"></i>&nbsp;{__('Cancel')}</button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <table class="list table">
                        <thead>
                            <tr>
                                <th></th>
                                <th class="tleft">{__('pluginName')}</th>
                                <th>{__('pluginVersion')}</th>
                                <th>{__('pluginFolder')}</th>
                            </tr>
                        </thead>
                        <tbody>
                        {foreach $pluginsAvailable->toArray() as $listingItem}
                            <tr class="plugin">
                                <td class="check">
                                    <input type="checkbox" name="cVerzeichnis[]" id="plugin-check-{$listingItem->getDir()}" value="{$listingItem->getDir()}" />
                                    {if $listingItem->isShop5Compatible() === false}
                                        {if $listingItem->isShop4Compatible() === false}
                                            <span title="{__('dangerPluginNotCompatibleShop4')}" class="label warning label-danger"><i class="fal fa-exclamation-triangle"></i></span>
                                        {else}
                                            <span title="{__('dangerPluginNotCompatibleShop5')}" class="label warning label-warning"><i class="fal fa-exclamation-triangle"></i></span>
                                        {/if}
                                    {/if}
                                </td>
                                <td>
                                    <label for="plugin-check-{$listingItem->getDir()}">{$listingItem->getName()}</label>
                                    <p>{$listingItem->getDescription()}</p>
                                    {if $listingItem->isShop4Compatible() === false && $listingItem->isShop5Compatible() === false}
                                        <div class="alert alert-info">{__('dangerPluginNotCompatibleShop4')}</div>
                                    {/if}
                                </td>
                                <td class="tcenter">{$listingItem->getVersion()}</td>
                                <td class="tcenter">{$listingItem->getDir()}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                        <tfoot>
                            <tr>
                                <td class="check"><input name="ALLMSGS" id="ALLMSGS4" type="checkbox" onclick="AllMessagesExcept(this.form, vLicenses);" /></td>
                                <td colspan="5"><label for="ALLMSGS4">{__('selectAll')}</label></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer">
                    <button name="installieren" type="submit" class="btn btn-primary"><i class="fa fa-share"></i> {__('pluginBtnInstall')}</button>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
    {/if}
</div>
