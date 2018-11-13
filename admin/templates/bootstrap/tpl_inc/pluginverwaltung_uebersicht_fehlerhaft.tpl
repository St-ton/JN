<div id="fehlerhaft" class="tab-pane fade {if isset($cTab) && $cTab === 'fehlerhaft'} active in{/if}">
    {if $pluginsErroneous->count() > 0}
        <form name="pluginverwaltung" method="post" action="pluginverwaltung.php">
            {$jtl_token}
            <input type="hidden" name="pluginverwaltung_uebersicht" value="1" />
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h3 class="panel-title">{#pluginListNotInstalledAndError#}</h3>
                </div>
                <div class="table-responsive">
                    <table class="list table">
                        <thead>
                        <tr>
                            <th class="tleft">{#pluginName#}</th>
                            <th class="tleft">{#pluginErrorCode#}</th>
                            <th>{#pluginVersion#}</th>
                            <th>{#pluginFolder#}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach $pluginsErroneous->toArray() as $plugin}
                            <tr>
                                <td>
                                    <strong>{$plugin->getName()}</strong>
                                    <p>{$plugin->getDescription()}</p>
                                </td>
                                <td>
                                    <p>
                                        <span class="badge error">{$plugin->getErrorCode()}</span>
                                        {$plugin->getErrorMessage()}
                                    </p>
                                </td>
                                <td class="tcenter">{$plugin->getVersion()}</td>
                                <td class="tcenter">{$plugin->getDir()}</td>
                            </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
            </div>
        </form>
    {else}
        <div class="alert alert-info" role="alert">{#noDataAvailable#}</div>
    {/if}
</div>