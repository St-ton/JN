{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{if !empty($Bestellung->oDownload_arr)}
    <h2>{lang key="yourDownloads" section="global"}</h2>
    <table class="table table-striped table-bordered" id="jtl_downloads">
        <thead>
        <tr>
            <th>{lang key="downloadFile" section="global"}</th>
            <th class="text-center">{lang key="downloadLimit" section="global"}</th>
            <th class="text-center">{lang key="validUntil" section="global"}</th>
            <th class="text-center">{lang key="download" section="global"}</th>
        </tr>
        </thead>

        <tbody>
        {foreach name=downloads from=$Bestellung->oDownload_arr item=oDownload}
            <tr>
                <td class="p40 dl_name" valign="middle">{$oDownload->oDownloadSprache->getName()}</td>
                <td class="text-center dl_limit" valign="middle">{if isset($oDownload->cLimit)}{$oDownload->cLimit}{else}{lang key="unlimited" section="global"}{/if}</td>
                <td class="text-center dl_validuntil" valign="middle">{if isset($oDownload->dGueltigBis)}{$oDownload->dGueltigBis}{else}{lang key="unlimited" section="global"}{/if}</td>
                <td class="text-center dl_download" valign="middle">
                    {if $Bestellung->cStatus == $BESTELLUNG_STATUS_BEZAHLT || $Bestellung->cStatus == $BESTELLUNG_STATUS_VERSANDT}
                        <form method="post" action="jtl.php">
                            {$jtl_token}
                            <input name="a" type="hidden" value="getdl" />
                            <input name="bestellung" type="hidden" value="{$Bestellung->kBestellung}" />
                            <input name="dl" type="hidden" value="{$oDownload->getDownload()}" />
                            <button class="btn btn-default btn-xs">{lang key="download" sektion="global"}</button>
                        </form>
                    {else}
                        {lang key="downloadPending"}
                    {/if}
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
{elseif !empty($oDownload_arr)}
    <h2>{lang key="yourDownloads" section="global"}</h2>
    <table class="table table-striped table-bordered" id="jtl_downloads">
        <thead>
        <tr>
            <th>{lang key="downloadFile" section="global"}</th>
            <th class="text-center">{lang key="downloadLimit" section="global"}</th>
            <th class="text-center">{lang key="validUntil" section="global"}</th>
            <th class="text-center">{lang key="download" section="global"}</th>
        </tr>
        </thead>
    
        <tbody>
        {foreach name=downloads from=$oDownload_arr item=oDownload}
            <tr>
                <td class="p40 dl_name" valign="middle">{$oDownload->oDownloadSprache->getName()}</td>
                <td class="text-center dl_limit" valign="middle">{if isset($oDownload->cLimit)}{$oDownload->cLimit}{else}{lang key="unlimited" section="global"}{/if}</td>
                <td class="text-center dl_validuntil" valign="middle">{if isset($oDownload->dGueltigBis)}{$oDownload->dGueltigBis}{else}{lang key="unlimited" section="global"}{/if}</td>
                <td class="text-center dl_download" valign="middle">
                    <form method="post" action="jtl.php">
                        {$jtl_token}
                        <input name="kBestellung" type="hidden" value="{$oDownload->kBestellung}" />
                        <input name="kKunde" type="hidden" value="{$smarty.session.Kunde->kKunde}" />
                        {assign var=cStatus value=$BESTELLUNG_STATUS_OFFEN}
                        {foreach from=$Bestellungen item=Bestellung}
                            {if $Bestellung->kBestellung == $oDownload->kBestellung}
                                {assign var=cStatus value=$Bestellung->cStatus}
                            {/if}
                        {/foreach}
                        {if $cStatus == $BESTELLUNG_STATUS_BEZAHLT || $cStatus == $BESTELLUNG_STATUS_VERSANDT}
                            <input name="dl" type="hidden" value="{$oDownload->getDownload()}" />
                            <button class="btn btn-default"><i class="fa fa-download"></i></button>
                        {else}
                            {lang key="downloadPending"}
                        {/if}
                    </form>
                </td>
            </tr>
        {/foreach}
        </tbody>
    </table>
{/if}