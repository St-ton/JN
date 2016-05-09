{**
 * @copyright (c) JTL-Software-GmbH
 * @license http://jtl-url.de/jtlshoplicense
 *}

{if !empty($Bestellung->oUpload_arr)}
    {* tiny left+right container optimized *}
    {assign var=nNameLength value=50}
    {assign var=nImageMaxWidth value=480}
    {assign var=nImageMaxHeight value=320}
    {assign var=nImagePreviewWidth value=35}
    <div id="uploads">
        <h3>{lang key="yourUploads" section="global"}</h3>
        <table class="tiny">
            <thead>
            <tr>
                <th class="tleft">{lang key="uploadFile" section="global"}</th>
                <th class="text-center">{lang key="uploadAdded" section="global"}</th>
                <th class="text-center">{lang key="uploadFilesize" section="global"}</th>
                <th class="text-center">{lang key="uploadState" section="global"}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$Bestellung->oUpload_arr item=oUpload}
                <tr>
                    <td class="tleft vmiddle">
                        <div class="nowrap">
                            {if $oUpload->bVorschau}
                                <span class="image_preview zoomcur" ref="{$oUpload->cBildpfad}" maxwidth="{$nImageMaxWidth}" maxheight="{$nImageMaxHeight}" title="{$oUpload->cName}">
                                   <img src="{$oUpload->cBildpfad}" alt="{$oUpload->cName}" width="{$nImagePreviewWidth}" class="vmiddle" />
                                </span>
                            {/if}
                            <span class="vmiddle {if $oUpload->cName|count_characters > $nNameLength}infocur" title="{$oUpload->cName}{/if}">
                                {$oUpload->cName|truncate:$nNameLength}
                            </span>
                        </div>
                    </td>
                    <td class="text-center" valign="middle" width="120">
                        <span class="infocur" title="{$oUpload->dErstellt|date_format:"%d.%m.%Y - %H:%M:%S"}">
                            {$oUpload->dErstellt|date_format:"%d.%m.%Y"}
                        </span>
                    </td>
                    <td class="text-center" valign="middle" width="100">{$oUpload->cGroesse}</td>
                    <td class="text-center" valign="middle" width="60"><span class="{if $oUpload->bVorhanden}success{else}notice{/if}"></span></td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
{/if}