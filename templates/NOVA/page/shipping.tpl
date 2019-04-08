{**
 * @copyright (c) JTL-Software-GmbH
 * @license https://jtl-url.de/jtlshoplicense
 *}
{block name='page-shipping'}
    {if isset($Einstellungen.global.global_versandermittlung_anzeigen) && $Einstellungen.global.global_versandermittlung_anzeigen === 'Y'}
        {include file='snippets/opc_mount_point.tpl' id='opc_shipping_prepend'}
        {if isset($smarty.session.Warenkorb->PositionenArr) && $smarty.session.Warenkorb->PositionenArr|@count > 0}
            {block name='page-shipping-form'}
                {form method="post"
                      action="{if isset($oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND])}{$oSpezialseiten_arr[$smarty.const.LINKTYP_VERSAND]->getURL()}{else}index.php{/if}{if $bExclusive}?exclusive_content=1{/if}"
                      class="form-inline evo-validate" id="shipping-calculator-form"}
                    {input type="hidden" name="s" value=$Link->getID()}
                    {block name='page-shipping-include-shipping-calculator'}
                        {include file='snippets/shipping_calculator.tpl' checkout=false}
                    {/block}
                {/form}
            {/block}
        {else}
            {block name='page-shipping-note'}
                {lang key='estimateShippingCostsNote' section='global'}
            {/block}
        {/if}
        {include file='snippets/opc_mount_point.tpl' id='opc_shipping_append'}
    {/if}
{/block}
