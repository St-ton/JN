<div id="fehlerhaft" class="tab-pane fade {if isset($cTab) && $cTab === 'fehlerhaft'} active show{/if}">
    {if $pluginsErroneous->count() > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
            {$jtl_token}
            <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
            <div>
                <div class="subheading1">{__('pluginListNotInstalledAndError')}</div>
                <hr class="mb-3">
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th class="text-left">{__('pluginName')}</th>
                            <th class="text-left">{__('pluginErrorCode')}</th>
                            <th>{__('pluginVersion')}</th>
                            <th>{__('pluginFolder')}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $pluginsErroneous->toArray() as $listingItem}
                            <tr>
                                <td>
                                    <strong>{$listingItem->getName()}</strong>
                                    <p>{$listingItem->getDescription()}</p>
                                </td>
                                <td>
                                    <p>
                                        <span class="badge error">{$listingItem->getErrorCode()}</span>
                                        {$listingItem->getErrorMessage()}
                                    </p>
                                </td>
                                <td class="text-center">{$listingItem->getVersion()}</td>
                                <td class="text-center">{$listingItem->getDir()}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info" role="alert">{__('noDataAvailable')}</div>
    {/if}
</div>