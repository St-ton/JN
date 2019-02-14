{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='vat-info'}
    {strip}
    {if !empty($taxdata.text)}
        {$taxdata.text}
    {else}
        {if $Einstellungen.global.global_ust_auszeichnung === 'auto'}
            {if $taxdata.net}
                {lang key='excl' section='productDetails'}
            {else}
                {lang key='incl' section='productDetails'}
            {/if}
            &nbsp;{$taxdata.tax}% {lang key='vat' section='productDetails'}
        {elseif $Einstellungen.global.global_ust_auszeichnung === 'endpreis'}
            {lang key='finalprice' section='productDetails'}
        {/if}
    {/if}
    {/strip}
    {if $Einstellungen.global.global_versandhinweis === 'zzgl'}
    ,
        {if $Einstellungen.global.global_versandfrei_anzeigen === 'Y' && $taxdata.shippingFreeCountries}
            {if $Einstellungen.global.global_versandkostenfrei_darstellung === 'D'}
                {lang key='noShippingcostsTo'} {lang key='noShippingCostsAtExtended' section='basket' printf=' ::: '}
                {foreach item=country key=cISO from=$taxdata.countries}
                    <abbr title="{$country}">{$cISO}</abbr>
                {/foreach}, {link href="{if isset($oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND])}{$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL()}{/if}" rel="nofollow" class="shipment popup"}
                    {lang key='shipping' section='basket'}{/link}
            {else}
                {link
                    href="{$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL()}"
                    rel="nofollow"
                    class="shipment popup"
                    data-toggle="tooltip"
                    data-placement="left"
                    title="{$taxdata.shippingFreeCountries}, {lang key='else'} {lang key='plus' section='basket'} {lang key='shipping' section='basket'}"
                }
                    {lang key='noShippingcostsTo'}
                {/link}
            {/if}
        {elseif isset($oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND])}
            {lang key='plus' section='basket'} {link href="{$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL()}" rel="nofollow" class="shipment popup"}
                {lang key='shipping' section='basket'}
            {/link}
        {/if}
    {elseif $Einstellungen.global.global_versandhinweis === 'inkl'}
        , {lang key='incl' section='productDetails'} {link href="{if isset($oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND])}{$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL()}{/if}" rel="nofollow" class="shipment"}{lang key='shipping' section='basket'}{/link}
    {/if}
{/block}

{block name='shipping-class'}
    {if !empty($taxdata.shippingClass) && $taxdata.shippingClass !== 'standard' && $Einstellungen.global.global_versandklasse_anzeigen === 'Y'}
        ({$taxdata.shippingClass})
    {/if}
{/block}
