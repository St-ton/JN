{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='productdetails-config-summary'}
{strip}
    {block name='productdetails-config-summary-name-net'}
        <tr>
            <td colspan="2">{$Artikel->cName}</td>
            <td class="text-right text-nowrap">{$Artikel->Preise->cVKLocalized[$NettoPreise]}</td>
        </tr>
    {/block}
    {if $oKonfig->oKonfig_arr|@count > 0}
        {$isIgnoreMultiplier = false}
        {block name='productdetails-config-summary-conf-groups'}
            {foreach $oKonfig->oKonfig_arr as $oKonfiggruppe}
                {if $oKonfiggruppe->bAktiv}
                    {foreach $oKonfiggruppe->oItem_arr as $oKonfigitem}
                        {if $oKonfigitem->bAktiv && !$oKonfigitem->ignoreMultiplier()}
                            <tr>
                                <td class="text-nowrap">{$oKonfigitem->fAnzahl} &times;</td>
                                <td class="word-break">{$oKonfigitem->getName()}</td>
                                <td class="text-right text-nowrap">{$oKonfigitem->getFullPriceLocalized(true, false, $oKonfig->fAnzahl)}</td>
                            </tr>
                        {elseif $oKonfigitem->bAktiv && $oKonfigitem->ignoreMultiplier()}
                            {$isIgnoreMultiplier = true}
                        {/if}
                    {/foreach}
                {/if}
            {/foreach}
        {/block}
        {if $isIgnoreMultiplier}
            {block name='productdetails-config-summary-conf-groups-ignore-multiplier'}
                <tr>
                    <td colspan="3" class="highlighted">{lang key='one-off' section='checkout'}</td>
                </tr>
                {foreach $oKonfig->oKonfig_arr as $oKonfiggruppe}
                    {if $oKonfiggruppe->bAktiv}
                        {foreach $oKonfiggruppe->oItem_arr as $oKonfigitem}
                            {if $oKonfigitem->bAktiv && $oKonfigitem->ignoreMultiplier()}
                                <tr>
                                    <td class="text-nowrap">{$oKonfigitem->fAnzahl} &times;</td>
                                    <td class="word-break">{$oKonfigitem->getName()}</td>
                                    <td class="text-right">{$oKonfigitem->getFullPriceLocalized()}</td>
                                </tr>
                            {/if}
                        {/foreach}
                    {/if}
                {/foreach}
            {/block}
        {/if}
    {/if}
{/strip}
{/block}
